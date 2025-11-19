<?php

class TLMS_User_Registration {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // استخدام الهوكات الصحيحة من Tutor LMS
        add_action('tutor_student_reg_form_after', array($this, 'add_student_registration_fields'));
        add_action('tutor_instructor_reg_form_after', array($this, 'add_instructor_registration_fields'));
        
        // الهوكات الاحتياطية
        add_action('tutor_student_reg_form_end', array($this, 'add_student_registration_fields_fallback'));
        add_action('tutor_instructor_reg_form_end', array($this, 'add_instructor_registration_fields_fallback'));
        
        // التحقق من الصحة
        add_filter('tutor_student_registration_errors', array($this, 'validate_student_form'), 10, 1);
        add_filter('tutor_instructor_registration_errors', array($this, 'validate_instructor_form'), 10, 1);
        
        // حفظ البيانات
        add_action('user_register', array($this, 'save_custom_fields'));
        add_action('profile_update', array($this, 'save_custom_fields'));
        
        // AJAX handlers
        add_action('wp_ajax_tlms_get_academic_categories', array($this, 'ajax_get_academic_categories'));
        add_action('wp_ajax_nopriv_tlms_get_academic_categories', array($this, 'ajax_get_academic_categories'));
    }
    
    public function add_student_registration_fields() {
        $this->display_registration_fields('student');
    }
    
    public function add_instructor_registration_fields() {
        $this->display_registration_fields('instructor');
    }
    
    public function add_student_registration_fields_fallback() {
        if (!did_action('tutor_student_reg_form_after')) {
            $this->display_registration_fields('student');
        }
    }
    
    public function add_instructor_registration_fields_fallback() {
        if (!did_action('tutor_instructor_reg_form_after')) {
            $this->display_registration_fields('instructor');
        }
    }
    
    public function validate_student_form($errors) {
        return $this->validate_registration_form($errors, 'student');
    }
    
    public function validate_instructor_form($errors) {
        return $this->validate_registration_form($errors, 'instructor');
    }
    
    private function validate_registration_form($errors, $user_type) {
        if (empty($_POST['tlms_education_type'])) {
            $errors['tlms_education_type'] = __('Education type is required', 'tutor-lms-academic-pro');
        }
        
        if (isset($_POST['tlms_education_type']) && $_POST['tlms_education_type'] !== 'general') {
            if (empty($_POST['tlms_academic_categories']) || !is_array($_POST['tlms_academic_categories'])) {
                $errors['tlms_academic_categories'] = __('Please complete all academic category selections', 'tutor-lms-academic-pro');
            } else {
                // التحقق من أن جميع المستويات مكتملة
                $categories = $_POST['tlms_academic_categories'];
                $has_empty = false;
                foreach ($categories as $level => $category_id) {
                    if (empty($category_id)) {
                        $has_empty = true;
                        break;
                    }
                }
                if ($has_empty) {
                    $errors['tlms_academic_categories'] = __('Please complete all academic category selections', 'tutor-lms-academic-pro');
                }
            }
        }
        
        return $errors;
    }
    
    public function display_registration_fields($user_type) {
        $options = get_option('tlms_academic_pro_settings');
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $education_types = isset($options['education_types']) ? $options['education_types'] : array();
        
        if (empty($education_types)) {
            return;
        }
        ?>
        <div class="tutor-form-group">
            <label for="tlms_education_type">
                <?php _e('Education Type *', 'tutor-lms-academic-pro'); ?>
            </label>
            <select name="tlms_education_type" id="tlms_education_type" class="tutor-form-control" required>
                <option value=""><?php _e('Select Education Type', 'tutor-lms-academic-pro'); ?></option>
                <?php foreach ($education_types as $type): ?>
                    <option value="<?php echo esc_attr($type); ?>" <?php selected(isset($_POST['tlms_education_type']) ? $_POST['tlms_education_type'] : '', $type); ?>>
                        <?php echo $this->get_education_type_label($type); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div id="tlms_academic_categories_container" style="display: none; margin-top: 15px;">
            <!-- المحتوى الديناميكي للتصنيفات سيظهر هنا -->
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#tlms_education_type').change(function() {
                var educationType = $(this).val();
                var $container = $('#tlms_academic_categories_container');
                
                if (educationType) {
                    $container.show();
                    $container.html('<div class="tlms-loading"></div>');
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'tlms_get_academic_categories',
                            education_type: educationType,
                            level: 0,
                            nonce: '<?php echo wp_create_nonce('tlms_ajax_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $container.html(response.data);
                            } else {
                                $container.html('<p><?php _e('Error loading categories.', 'tutor-lms-academic-pro'); ?></p>');
                            }
                        },
                        error: function() {
                            $container.html('<p><?php _e('Error loading categories. Please try again.', 'tutor-lms-academic-pro'); ?></p>');
                        }
                    });
                } else {
                    $container.hide().empty();
                }
            });
            
            // إذا كانت هناك قيمة مسبقة، قم بتحميل التصنيفات تلقائياً
            var initialEducationType = $('#tlms_education_type').val();
            if (initialEducationType) {
                $('#tlms_education_type').trigger('change');
            }
        });
        </script>
        <?php
    }
    
    public function save_custom_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        if (isset($_POST['tlms_education_type'])) {
            update_user_meta($user_id, 'tlms_education_type', sanitize_text_field($_POST['tlms_education_type']));
        }
        
        if (isset($_POST['tlms_academic_categories']) && is_array($_POST['tlms_academic_categories'])) {
            $academic_categories = array();
            foreach ($_POST['tlms_academic_categories'] as $level => $category_id) {
                if (!empty($category_id)) {
                    $academic_categories[$level] = intval($category_id);
                }
            }
            update_user_meta($user_id, 'tlms_academic_categories', $academic_categories);
        }
    }
    
    // باقي الدوال تبقى كما هي (ajax_get_academic_categories, render_category_fields, etc.)
    public function ajax_get_academic_categories() {
        check_ajax_referer('tlms_ajax_nonce', 'nonce');
        
        $education_type = sanitize_text_field($_POST['education_type']);
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $level = isset($_POST['level']) ? intval($_POST['level']) : 0;
        
        $categories = get_terms(array(
            'taxonomy' => 'tlms_academic_category',
            'hide_empty' => false,
            'parent' => $parent_id,
            'meta_query' => array(
                array(
                    'key' => 'education_type',
                    'value' => $education_type
                )
            )
        ));
        
        if (empty($categories) || is_wp_error($categories)) {
            wp_send_json_success(false);
            return;
        }
        
        ob_start();
        ?>
        <div class="tutor-form-group">
            <label for="tlms_category_<?php echo $level; ?>"><?php echo $this->get_level_label($education_type, $level); ?></label>
            <select name="tlms_academic_categories[<?php echo $level; ?>]" id="tlms_category_<?php echo $level; ?>" class="tutor-form-control tlms-category-select" data-level="<?php echo $level; ?>">
                <option value=""><?php _e('Select', 'tutor-lms-academic-pro'); ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->term_id; ?>">
                        <?php echo $category->name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
        $output = ob_get_clean();
        
        wp_send_json_success($output);
    }
    
    private function get_education_type_label($type) {
        $labels = array(
            'university' => __('🏛️ University', 'tutor-lms-academic-pro'),
            'school' => __('🎓 School', 'tutor-lms-academic-pro'),
            'general' => __('📚 General Courses', 'tutor-lms-academic-pro')
        );
        
        return isset($labels[$type]) ? $labels[$type] : ucfirst($type);
    }
    
    private function get_level_label($education_type, $level) {
        $labels = array(
            'university' => array(
                __('Select University', 'tutor-lms-academic-pro'),
                __('Select College', 'tutor-lms-academic-pro'),
                __('Select Department', 'tutor-lms-academic-pro'),
                __('Select Program', 'tutor-lms-academic-pro'),
                __('Select Specialization', 'tutor-lms-academic-pro')
            ),
            'school' => array(
                __('Select School Type', 'tutor-lms-academic-pro'),
                __('Select Education Level', 'tutor-lms-academic-pro'),
                __('Select Grade/Year', 'tutor-lms-academic-pro'),
                __('Select Section', 'tutor-lms-academic-pro'),
                __('Select Track', 'tutor-lms-academic-pro')
            )
        );
        
        return isset($labels[$education_type][$level]) ? $labels[$education_type][$level] : sprintf(__('Level %d', 'tutor-lms-academic-pro'), $level + 1);
    }
}

?>