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
        
        // إضافة الحقول لواجهة إنشاء الكورس
        add_action('add_meta_boxes', array($this, 'add_course_meta_boxes'));
        add_action('save_post_courses', array($this, 'save_course_meta'));
        
        // AJAX للحصول على تصنيفات الكورس
        add_action('wp_ajax_tlms_get_course_categories', array($this, 'ajax_get_course_categories'));
        
        // التأكد من تحميل التصنيفات في واجهة التحرير
        add_action('admin_init', array($this, 'ensure_taxonomy_registered'));
    }
    
    public function ensure_taxonomy_registered() {
        // التأكد من أن التصنيف مسجل قبل استخدامه
        if (!taxonomy_exists($this->taxonomy_name)) {
            $this->register_taxonomies();
        }
    }
    
    public function register_taxonomies() {
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
            'show_in_menu' => false,
            'show_in_nav_menus' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_tutor',
                'edit_terms' => 'manage_tutor',
                'delete_terms' => 'manage_tutor',
                'assign_terms' => 'manage_tutor'
            )
        );
        
        register_taxonomy($this->taxonomy_name, array('courses'), $args);
        register_taxonomy_for_object_type($this->taxonomy_name, 'courses');
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'tutor',
            __('Academic Structure', 'tutor-lms-academic-pro'),
            __('Academic Structure', 'tutor-lms-academic-pro'),
            'manage_tutor',
            'tlms-academic-structure',
            array($this, 'academic_structure_page')
        );
    }
    
    public function academic_structure_page() {
        // التأكد من وجود التصنيفات
        $this->ensure_taxonomy_registered();
        ?>
        <div class="wrap">
            <h1><?php _e('Academic Structure Management', 'tutor-lms-academic-pro'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="#universities" class="nav-tab nav-tab-active"><?php _e('🏛️ Universities', 'tutor-lms-academic-pro'); ?></a>
                <a href="#schools" class="nav-tab"><?php _e('🎓 Schools', 'tutor-lms-academic-pro'); ?></a>
                <a href="#general" class="nav-tab"><?php _e('📚 General Courses', 'tutor-lms-academic-pro'); ?></a>
            </h2>
            
            <div id="universities" class="tlms-tab-content active">
                <h3><?php _e('University Structure', 'tutor-lms-academic-pro'); ?></h3>
                <p><?php _e('Manage university categories hierarchy', 'tutor-lms-academic-pro'); ?></p>
                <?php $this->display_categories_table('university'); ?>
            </div>
            
            <div id="schools" class="tlms-tab-content">
                <h3><?php _e('School Structure', 'tutor-lms-academic-pro'); ?></h3>
                <p><?php _e('Manage school categories hierarchy', 'tutor-lms-academic-pro'); ?></p>
                <?php $this->display_categories_table('school'); ?>
            </div>
            
            <div id="general" class="tlms-tab-content">
                <h3><?php _e('General Courses Categories', 'tutor-lms-academic-pro'); ?></h3>
                <p><?php _e('Manage general course categories', 'tutor-lms-academic-pro'); ?></p>
                <?php $this->display_categories_table('general'); ?>
            </div>
        </div>

        <style>
        .tlms-tab-content { display: none; }
        .tlms-tab-content.active { display: block; }
        .tlms-categories-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tlms-categories-table th, .tlms-categories-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .tlms-categories-table th { background: #f5f5f5; }
        .tlms-category-level-0 { font-weight: bold; background: #e7f3ff; }
        .tlms-category-level-1 { padding-left: 20px; background: #f0f8ff; }
        .tlms-category-level-2 { padding-left: 40px; background: #f9fcff; }
        .tlms-category-level-3 { padding-left: 60px; }
        .tlms-category-level-4 { padding-left: 80px; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab-wrapper a').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.tlms-tab-content').removeClass('active');
                $(target).addClass('active');
            });
        });
        </script>
        <?php
    }
    
    private function display_categories_table($education_type) {
        $categories = get_terms(array(
            'taxonomy' => $this->taxonomy_name,
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'education_type',
                    'value' => $education_type
                )
            ),
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        if (empty($categories) || is_wp_error($categories)) {
            echo '<div class="notice notice-warning"><p>';
            echo sprintf(__('No %s categories found. The sample categories will be created automatically when you first activate the plugin.', 'tutor-lms-academic-pro'), $education_type);
            echo '</p></div>';
            
            // محاولة إنشاء التصنيفات تلقائياً
            $this->create_sample_categories();
            
            // إعادة المحاولة
            $categories = get_terms(array(
                'taxonomy' => $this->taxonomy_name,
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'education_type',
                        'value' => $education_type
                    )
                ),
                'orderby' => 'name',
                'order' => 'ASC'
            ));
            
            if (empty($categories) || is_wp_error($categories)) {
                echo '<p>' . __('Please try reactivating the plugin to create sample categories.', 'tutor-lms-academic-pro') . '</p>';
                return;
            }
        }
        
        echo '<table class="tlms-categories-table">';
        echo '<thead><tr><th>' . __('Category Name', 'tutor-lms-academic-pro') . '</th><th>' . __('Slug', 'tutor-lms-academic-pro') . '</th><th>' . __('Education Type', 'tutor-lms-academic-pro') . '</th><th>' . __('Actions', 'tutor-lms-academic-pro') . '</th></tr></thead>';
        echo '<tbody>';
        
        $this->display_categories_hierarchy($categories, $education_type);
        
        echo '</tbody></table>';
    }
    
    private function display_categories_hierarchy($categories, $education_type, $parent = 0, $level = 0) {
        foreach ($categories as $category) {
            if ($category->parent == $parent) {
                $category_education_type = get_term_meta($category->term_id, 'education_type', true);
                
                echo '<tr class="tlms-category-level-' . $level . '">';
                echo '<td>' . str_repeat('— ', $level) . $category->name . '</td>';
                echo '<td>' . $category->slug . '</td>';
                echo '<td>' . ($category_education_type ? $category_education_type : $education_type) . '</td>';
                echo '<td>';
                echo '<a href="' . admin_url('term.php?taxonomy=' . $this->taxonomy_name . '&tag_ID=' . $category->term_id) . '" class="button">' . __('Edit', 'tutor-lms-academic-pro') . '</a> ';
                echo '<a href="' . admin_url('edit-tags.php?action=delete&taxonomy=' . $this->taxonomy_name . '&tag_ID=' . $category->term_id) . '" class="button button-link-delete">' . __('Delete', 'tutor-lms-academic-pro') . '</a>';
                echo '</td>';
                echo '</tr>';
                
                $this->display_categories_hierarchy($categories, $education_type, $category->term_id, $level + 1);
            }
        }
    }
    
    private function create_sample_categories() {
        // استدعاء دالة إنشاء التصنيفات من class-activation
        TLMS_Activation::create_sample_categories();
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
        // التأكد من وجود التصنيفات
        $this->ensure_taxonomy_registered();
        
        wp_nonce_field('tlms_course_categories_nonce', 'tlms_course_categories_nonce');
        
        $education_type = get_post_meta($post->ID, '_tlms_education_type', true);
        $selected_categories = wp_get_post_terms($post->ID, $this->taxonomy_name, array('fields' => 'ids'));
        ?>
        <div class="tlms-course-categories">
            <p>
                <label for="tlms_education_type_meta"><strong><?php _e('Education Type:', 'tutor-lms-academic-pro'); ?></strong></label>
                <select name="tlms_education_type" id="tlms_education_type_meta" style="width: 100%;">
                    <option value=""><?php _e('Select Education Type', 'tutor-lms-academic-pro'); ?></option>
                    <option value="university" <?php selected($education_type, 'university'); ?>><?php _e('University', 'tutor-lms-academic-pro'); ?></option>
                    <option value="school" <?php selected($education_type, 'school'); ?>><?php _e('School', 'tutor-lms-academic-pro'); ?></option>
                    <option value="general" <?php selected($education_type, 'general'); ?>><?php _e('General Courses', 'tutor-lms-academic-pro'); ?></option>
                </select>
            </p>
            
            <div id="tlms_meta_categories_container" style="<?php echo $education_type ? '' : 'display: none;'; ?>">
                <label><strong><?php _e('Academic Categories:', 'tutor-lms-academic-pro'); ?></strong></label>
                <div id="tlms_meta_categories_checkboxes" style="max-height: 200px; overflow-y: auto;">
                    <?php if ($education_type): ?>
                        <?php echo $this->get_categories_checkboxes($education_type, $selected_categories); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#tlms_education_type_meta').change(function() {
                var educationType = $(this).val();
                var $container = $('#tlms_meta_categories_container');
                var $checkboxes = $('#tlms_meta_categories_checkboxes');
                
                if (educationType) {
                    $container.show();
                    $checkboxes.html('<div class="tlms-loading"><?php _e("Loading categories...", "tutor-lms-academic-pro"); ?></div>');
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'tlms_get_course_categories',
                            education_type: educationType,
                            course_id: <?php echo $post->ID; ?>,
                            nonce: '<?php echo wp_create_nonce('tlms_course_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $checkboxes.html(response.data);
                            } else {
                                $checkboxes.html('<p><?php _e("Error loading categories.", "tutor-lms-academic-pro"); ?></p>');
                            }
                        },
                        error: function() {
                            $checkboxes.html('<p><?php _e("Error loading categories. Please try again.", "tutor-lms-academic-pro"); ?></p>');
                        }
                    });
                } else {
                    $container.hide();
                    $checkboxes.empty();
                }
            });
        });
        </script>
        <?php
    }
    
    public function ajax_get_course_categories() {
        check_ajax_referer('tlms_course_nonce', 'nonce');
        
        $education_type = sanitize_text_field($_POST['education_type']);
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        
        $selected_categories = array();
        if ($course_id) {
            $selected_categories = wp_get_post_terms($course_id, 'tlms_academic_category', array('fields' => 'ids'));
        }
        
        $categories = get_terms(array(
            'taxonomy' => 'tlms_academic_category',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'education_type',
                    'value' => $education_type
                )
            )
        ));
        
        if (empty($categories) || is_wp_error($categories)) {
            wp_send_json_success('<p>' . __('No categories found for this education type.', 'tutor-lms-academic-pro') . '</p>');
            return;
        }
        
        $output = '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
        foreach ($categories as $category) {
            $checked = in_array($category->term_id, $selected_categories) ? 'checked' : '';
            $output .= '<label style="display: block; margin-bottom: 8px;">';
            $output .= '<input type="checkbox" name="tlms_academic_categories[]" value="' . $category->term_id . '" ' . $checked . '> ';
            $output .= $category->name;
            $output .= '</label>';
        }
        $output .= '</div>';
        
        wp_send_json_success($output);
    }
    
    private function get_categories_checkboxes($education_type, $selected_categories = array()) {
        $categories = get_terms(array(
            'taxonomy' => 'tlms_academic_category',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'education_type',
                    'value' => $education_type
                )
            )
        ));
        
        if (empty($categories) || is_wp_error($categories)) {
            return '<p>' . __('No categories found for this education type.', 'tutor-lms-academic-pro') . '</p>';
        }
        
        $output = '<div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
        foreach ($categories as $category) {
            $checked = in_array($category->term_id, $selected_categories) ? 'checked' : '';
            $output .= '<label style="display: block; margin-bottom: 5px;">';
            $output .= '<input type="checkbox" name="tlms_academic_categories[]" value="' . $category->term_id . '" ' . $checked . '> ';
            $output .= $category->name;
            $output .= '</label>';
        }
        $output .= '</div>';
        
        return $output;
    }
    
    public function save_course_meta($post_id) {
        if (!isset($_POST['tlms_course_categories_nonce']) || 
            !wp_verify_nonce($_POST['tlms_course_categories_nonce'], 'tlms_course_categories_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) !== 'courses') {
            return;
        }
        
        if (isset($_POST['tlms_education_type'])) {
            update_post_meta($post_id, '_tlms_education_type', sanitize_text_field($_POST['tlms_education_type']));
        }
        
        if (isset($_POST['tlms_academic_categories'])) {
            $categories = array_map('intval', $_POST['tlms_academic_categories']);
            wp_set_post_terms($post_id, $categories, $this->taxonomy_name);
        } else {
            wp_set_post_terms($post_id, array(), $this->taxonomy_name);
        }
    }
}

?>