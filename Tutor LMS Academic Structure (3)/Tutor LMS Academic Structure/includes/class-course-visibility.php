<?php

class TLMS_Course_Visibility {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('pre_get_posts', array($this, 'filter_courses_query'));
        add_filter('tutor_course/single/enrolled/nav_items', array($this, 'filter_course_navigation'));
        add_action('add_meta_boxes', array($this, 'add_course_meta_boxes'));
        add_action('save_post_courses', array($this, 'save_course_meta'));
        add_filter('tutor_course_archive_arg', array($this, 'filter_course_archive'));
        
        // Frontend course access control
        add_action('template_redirect', array($this, 'check_course_access'));
    }
    
    public function filter_courses_query($query) {
        if (is_admin() || !$query->is_main_query() || !$this->is_course_query($query)) {
            return;
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }
        
        $user_education_type = get_user_meta($user_id, 'tlms_education_type', true);
        $user_categories = get_user_meta($user_id, 'tlms_academic_categories', true);
        
        if ($user_education_type === 'general') {
            // General users can only see general courses and their own courses
            $this->filter_general_user_courses($query, $user_id);
        } else {
            // University/School users see courses from their categories + general courses
            $this->filter_academic_user_courses($query, $user_categories, $user_id);
        }
    }
    
    private function is_course_query($query) {
        return (is_post_type_archive('courses') || 
                is_tax('course-category') || 
                (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'courses'));
    }
    
    private function filter_general_user_courses($query, $user_id) {
        $meta_query = $query->get('meta_query') ?: array();
        
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key' => '_tlms_education_type',
                'value' => 'general',
                'compare' => '='
            ),
            array(
                'key' => '_tlms_course_author',
                'value' => $user_id,
                'compare' => '='
            )
        );
        
        $query->set('meta_query', $meta_query);
    }
    
    private function filter_academic_user_courses($query, $user_categories, $user_id) {
        if (empty($user_categories)) {
            return;
        }
        
        $tax_query = $query->get('tax_query') ?: array();
        
        // Get the leaf category (deepest level)
        $leaf_category = $this->get_leaf_category($user_categories);
        
        if ($leaf_category) {
            $tax_query[] = array(
                'taxonomy' => 'tlms_academic_category',
                'field' => 'term_id',
                'terms' => array($leaf_category),
                'operator' => 'IN'
            );
        }
        
        // Also include general courses
        $tax_query['relation'] = 'OR';
        $tax_query[] = array(
            'taxonomy' => 'tlms_academic_category',
            'field' => 'term_id',
            'terms' => $this->get_general_categories(),
            'operator' => 'IN'
        );
        
        $query->set('tax_query', $tax_query);
    }
    
    private function get_leaf_category($categories) {
        foreach (array_reverse($categories, true) as $category) {
            if ($category) {
                return $category;
            }
        }
        return false;
    }
    
    private function get_general_categories() {
        $general_categories = get_terms(array(
            'taxonomy' => 'tlms_academic_category',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'education_type',
                    'value' => 'general'
                )
            ),
            'fields' => 'ids'
        ));
        
        return is_array($general_categories) ? $general_categories : array();
    }
    
    public function check_course_access() {
        if (!is_singular('courses')) {
            return;
        }
        
        global $post;
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return; // Let Tutor LMS handle guest access
        }
        
        $course_id = $post->ID;
        $user_education_type = get_user_meta($user_id, 'tlms_education_type', true);
        $user_categories = get_user_meta($user_id, 'tlms_academic_categories', true);
        
        // Course author can always access their own courses
        if ($post->post_author == $user_id) {
            return;
        }
        
        // Admin and editors can access all courses
        if (current_user_can('administrator') || current_user_can('editor')) {
            return;
        }
        
        $course_education_type = get_post_meta($course_id, '_tlms_education_type', true);
        $course_categories = wp_get_post_terms($course_id, 'tlms_academic_category', array('fields' => 'ids'));
        
        if ($user_education_type === 'general') {
            if ($course_education_type !== 'general') {
                $this->restrict_access();
            }
        } else {
            $leaf_category = $this->get_leaf_category($user_categories);
            if ($course_education_type !== 'general' && !in_array($leaf_category, $course_categories)) {
                $this->restrict_access();
            }
        }
    }
    
    private function restrict_access() {
        wp_die(
            __('You do not have permission to access this course.', 'tutor-lms-academic-pro'),
            __('Access Denied', 'tutor-lms-academic-pro'),
            array('response' => 403)
        );
    }
    
    public function add_course_meta_boxes() {
        add_meta_box(
            'tlms_course_visibility',
            __('Academic Visibility', 'tutor-lms-academic-pro'),
            array($this, 'course_visibility_meta_box'),
            'courses',
            'side',
            'high'
        );
    }
    
    public function course_visibility_meta_box($post) {
        wp_nonce_field('tlms_course_visibility_nonce', 'tlms_course_visibility_nonce');
        
        $education_type = get_post_meta($post->ID, '_tlms_education_type', true);
        $selected_categories = wp_get_post_terms($post->ID, 'tlms_academic_category', array('fields' => 'ids'));
        ?>
        <div class="tlms-course-visibility">
            <p>
                <label for="tlms_education_type"><strong><?php _e('Education Type:', 'tutor-lms-academic-pro'); ?></strong></label>
                <select name="tlms_education_type" id="tlms_education_type" style="width: 100%;">
                    <option value=""><?php _e('Select Education Type', 'tutor-lms-academic-pro'); ?></option>
                    <option value="university" <?php selected($education_type, 'university'); ?>><?php _e('University', 'tutor-lms-academic-pro'); ?></option>
                    <option value="school" <?php selected($education_type, 'school'); ?>><?php _e('School', 'tutor-lms-academic-pro'); ?></option>
                    <option value="general" <?php selected($education_type, 'general'); ?>><?php _e('General Courses', 'tutor-lms-academic-pro'); ?></option>
                </select>
            </p>
            
            <div id="tlms_course_categories_container" style="<?php echo $education_type ? '' : 'display: none;'; ?>">
                <label><strong><?php _e('Academic Categories:', 'tutor-lms-academic-pro'); ?></strong></label>
                <?php
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
                
                if ($categories && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $checked = in_array($category->term_id, $selected_categories) ? 'checked' : '';
                        echo '<p>';
                        echo '<label>';
                        echo '<input type="checkbox" name="tlms_academic_categories[]" value="' . $category->term_id . '" ' . $checked . '> ';
                        echo $category->name;
                        echo '</label>';
                        echo '</p>';
                    }
                } else {
                    echo '<p>' . __('No categories found for this education type.', 'tutor-lms-academic-pro') . '</p>';
                }
                ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#tlms_education_type').change(function() {
                var educationType = $(this).val();
                if (educationType) {
                    $('#tlms_course_categories_container').show();
                    // In a real implementation, you might want to load categories via AJAX
                } else {
                    $('#tlms_course_categories_container').hide();
                }
            });
        });
        </script>
        <?php
    }
    
    public function save_course_meta($post_id) {
        if (!isset($_POST['tlms_course_visibility_nonce']) || 
            !wp_verify_nonce($_POST['tlms_course_visibility_nonce'], 'tlms_course_visibility_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save education type
        if (isset($_POST['tlms_education_type'])) {
            update_post_meta($post_id, '_tlms_education_type', sanitize_text_field($_POST['tlms_education_type']));
        }
        
        // Save academic categories
        if (isset($_POST['tlms_academic_categories'])) {
            $categories = array_map('intval', $_POST['tlms_academic_categories']);
            wp_set_post_terms($post_id, $categories, 'tlms_academic_category');
        } else {
            wp_set_post_terms($post_id, array(), 'tlms_academic_category');
        }
        
        // Save course author for reference
        $post = get_post($post_id);
        update_post_meta($post_id, '_tlms_course_author', $post->post_author);
    }
    
    public function filter_course_navigation($nav_items) {
        // Ensure course navigation respects visibility rules
        return $nav_items;
    }
    
    public function filter_course_archive($args) {
        // Modify course archive arguments if needed
        return $args;
    }
}

?>