<?php

class TLMS_Custom_Taxonomies {
    
    private static $instance = null;
    private $taxonomy_name = 'tlms_academic_category';
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('init', array($this, 'register_taxonomies'), 10);
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_tlms_get_child_categories', array($this, 'ajax_get_child_categories'));
        
        // إضافة التصنيفات إلى محرر الكورسات - استخدام hooks الخاصة بـ Tutor LMS
        add_action('add_meta_boxes', array($this, 'add_course_meta_boxes'));
        add_action('save_post', array($this, 'save_course_categories'));
        
        // إضافة التصنيفات إلى واجهة Tutor LMS
        add_action('tutor_course_builder_after_btn_group', array($this, 'add_to_course_builder'));
        add_action('tutor_course_builder_settings_tab_after_price', array($this, 'add_to_course_settings'));
        
        // تصفية الكورسات بناءً على التصنيفات
        add_filter('pre_get_posts', array($this, 'filter_courses_by_category'));
        
        // إضافة التصنيفات إلى قائمة الكورسات في الادمن
        add_filter('manage_courses_posts_columns', array($this, 'add_course_columns'));
        add_action('manage_courses_posts_custom_column', array($this, 'manage_course_columns'), 10, 2);
    }
    
    public function register_taxonomies() {
        // التحقق إذا كانت taxonomy مسجلة مسبقاً
        if (taxonomy_exists($this->taxonomy_name)) {
            return;
        }
        
        $labels = array(
            'name' => __('Academic Categories', 'tutor-lms-academic-pro'),
            'singular_name' => __('Academic Category', 'tutor-lms-academic-pro'),
            'search_items' => __('Search Academic Categories', 'tutor-lms-academic-pro'),
            'all_items' => __('All Academic Categories', 'tutor-lms-academic-pro'),
            'parent_item' => __('Parent Category', 'tutor-lms-academic-pro'),
            'parent_item_colon' => __('Parent Category:', 'tutor-lms-academic-pro'),
            'edit_item' => __('Edit Category', 'tutor-lms-academic-pro'),
            'update_item' => __('Update Category', 'tutor-lms-academic-pro'),
            'add_new_item' => __('Add New Category', 'tutor-lms-academic-pro'),
            'new_item_name' => __('New Category Name', 'tutor-lms-academic-pro'),
            'menu_name' => __('Academic Structure', 'tutor-lms-academic-pro'),
        );
        
        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'academic-category'),
            'show_in_rest' => true,
            'public' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_tutor',
                'edit_terms' => 'manage_tutor',
                'delete_terms' => 'manage_tutor',
                'assign_terms' => 'manage_tutor'
            )
        );
        
        register_taxonomy($this->taxonomy_name, array('courses'), $args);
        
        // تسجيل taxonomy بشكل صحيح مع post type
        register_taxonomy_for_object_type($this->taxonomy_name, 'courses');
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'tutor',
            __('Academic Structure', 'tutor-lms-academic-pro'),
            __('Academic Structure', 'tutor-lms-academic-pro'),
            'manage_tutor',
            'edit-tags.php?taxonomy=' . $this->taxonomy_name
        );
    }
    
    public function add_course_meta_boxes() {
        add_meta_box(
            'tlms_course_categories',
            __('Academic Categories', 'tutor-lms-academic-pro'),
            array($this, 'course_categories_meta_box'),
            'courses',
            'side',
            'default'
        );
    }
    
    public function course_categories_meta_box($post) {
        // استخدام nonce للتحقق
        wp_nonce_field('tlms_course_categories_nonce', 'tlms_course_categories_nonce');
        
        // الحصول على التصنيفات المحددة للكورس
        $selected_categories = wp_get_post_terms($post->ID, $this->taxonomy_name, array('fields' => 'ids'));
        
        // الحصول على جميع التصنيفات
        $categories = get_terms(array(
            'taxonomy' => $this->taxonomy_name,
            'hide_empty' => false,
            'parent' => 0
        ));
        
        echo '<div class="tlms-course-categories">';
        echo '<p>' . __('Select academic categories for this course:', 'tutor-lms-academic-pro') . '</p>';
        
        if (!empty($categories) && !is_wp_error($categories)) {
            echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
            $this->display_category_checkboxes($categories, $selected_categories);
            echo '</div>';
        } else {
            echo '<p>' . __('No academic categories found. Please create categories first.', 'tutor-lms-academic-pro') . '</p>';
            echo '<a href="' . admin_url('edit-tags.php?taxonomy=' . $this->taxonomy_name) . '" class="button">';
            echo __('Manage Academic Categories', 'tutor-lms-academic-pro');
            echo '</a>';
        }
        
        echo '</div>';
    }
    
    public function add_to_course_builder($course_id) {
        echo '<div class="tlms-course-builder-categories" style="margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px;">';
        echo '<h3>' . __('Academic Categories', 'tutor-lms-academic-pro') . '</h3>';
        $this->course_categories_meta_box(get_post($course_id));
        echo '</div>';
    }
    
    public function add_to_course_settings() {
        global $post;
        if ($post && $post->post_type === 'courses') {
            echo '<div class="tutor-option-field-row" style="margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px;">';
            echo '<div class="tutor-option-field-label">';
            echo '<label>' . __('Academic Categories', 'tutor-lms-academic-pro') . '</label>';
            echo '</div>';
            echo '<div class="tutor-option-field">';
            $this->course_categories_meta_box($post);
            echo '</div>';
            echo '</div>';
        }
    }
    
    private function display_category_checkboxes($categories, $selected_categories, $level = 0) {
        foreach ($categories as $category) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
            $checked = in_array($category->term_id, $selected_categories) ? 'checked' : '';
            
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo $indent . '<input type="checkbox" name="tlms_course_categories[]" value="' . $category->term_id . '" ' . $checked . '> ';
            echo $category->name;
            echo ' <small style="color: #666;">(' . get_term_meta($category->term_id, 'education_type', true) . ')</small>';
            echo '</label>';
            
            // عرض التصنيفات الفرعية
            $child_categories = get_terms(array(
                'taxonomy' => $this->taxonomy_name,
                'hide_empty' => false,
                'parent' => $category->term_id
            ));
            
            if (!empty($child_categories) && !is_wp_error($child_categories)) {
                $this->display_category_checkboxes($child_categories, $selected_categories, $level + 1);
            }
        }
    }
    
    public function save_course_categories($post_id) {
        // التحقق من nonce
        if (!isset($_POST['tlms_course_categories_nonce']) || 
            !wp_verify_nonce($_POST['tlms_course_categories_nonce'], 'tlms_course_categories_nonce')) {
            return;
        }
        
        // التحقق من صلاحيات المستخدم
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // التحقق من نوع المحتوى
        if (get_post_type($post_id) !== 'courses') {
            return;
        }
        
        // حفظ التصنيفات المحددة
        if (isset($_POST['tlms_course_categories'])) {
            $categories = array_map('intval', $_POST['tlms_course_categories']);
            wp_set_post_terms($post_id, $categories, $this->taxonomy_name);
        } else {
            // إذا لم يتم تحديد أي تصنيفات، قم بإزالة جميع التصنيفات
            wp_set_post_terms($post_id, array(), $this->taxonomy_name);
        }
    }
    
    public function add_course_columns($columns) {
        $columns['academic_categories'] = __('Academic Categories', 'tutor-lms-academic-pro');
        return $columns;
    }
    
    public function manage_course_columns($column, $post_id) {
        if ($column === 'academic_categories') {
            $terms = wp_get_post_terms($post_id, $this->taxonomy_name, array('fields' => 'names'));
            if (!empty($terms)) {
                echo implode(', ', $terms);
            } else {
                echo '—';
            }
        }
    }
    
    public function ajax_get_child_categories() {
        // التحقق من الأمان
        check_ajax_referer('tlms_ajax_nonce', 'nonce');
        
        $parent_id = intval($_POST['parent_id']);
        $level = intval($_POST['level']);
        
        $categories = get_terms(array(
            'taxonomy' => $this->taxonomy_name,
            'hide_empty' => false,
            'parent' => $parent_id
        ));
        
        if (empty($categories) || is_wp_error($categories)) {
            wp_send_json_success(false);
            return;
        }
        
        $output = '';
        foreach ($categories as $category) {
            $output .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
        }
        
        wp_send_json_success(array(
            'categories' => $output,
            'level' => $level
        ));
    }
    
    public function filter_courses_by_category($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $screen = get_current_screen();
        if ($screen && $screen->id === 'edit-courses' && isset($_GET[$this->taxonomy_name])) {
            $term_id = intval($_GET[$this->taxonomy_name]);
            if ($term_id) {
                $query->set('tax_query', array(
                    array(
                        'taxonomy' => $this->taxonomy_name,
                        'field' => 'term_id',
                        'terms' => array($term_id)
                    )
                ));
            }
        }
    }
    
    public function get_taxonomy_name() {
        return $this->taxonomy_name;
    }
}

?>