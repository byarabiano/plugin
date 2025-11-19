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
        // إضافة الحقول لصفحات تسجيل Tutor LMS
        add_action('tutor_student_registration_after_terms', array($this, 'add_student_registration_fields'));
        add_action('tutor_instructor_registration_after_terms', array($this, 'add_instructor_registration_fields'));
        
        add_action('user_register', array($this, 'save_user_academic_data'));
        add_action('show_user_profile', array($this, 'add_user_profile_fields'));
        add_action('edit_user_profile', array($this, 'add_user_profile_fields'));
        add_action('personal_options_update', array($this, 'save_user_academic_data'));
        add_action('edit_user_profile_update', array($this, 'save_user_academic_data'));
        
        // Tutor LMS specific hooks
        add_action('tutor_after_student_signup', array($this, 'save_tutor_student_data'));
        add_action('tutor_after_instructor_signup', array($this, 'save_tutor_instructor_data'));
        
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
    
    public function display_registration_fields($user_type) {
        $options = get_option('tlms_academic_pro_settings');
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $education_types = isset($options['education_types']) ? $options['education_types'] : array();
        
        // إذا لم يكن هناك أنواع تعليم متاحة، لا تظهر الحقول
        if (empty($education_types)) {
            return;
        }
        ?>
        <div class="tlms-registration-fields">
            <h3><?php _e('Academic Information', 'tutor-lms-academic-pro'); ?></h3>
            
            <div class="tlms-education-type-buttons">
                <p><strong><?php _e('Select Your Education Type:', 'tutor-lms-academic-pro'); ?></strong></p>
                <div class="tlms-button-group">
                    <?php foreach ($education_types as $type): ?>
                        <button type="button" class="tlms-edu-btn" data-education-type="<?php echo esc_attr($type); ?>">
                            <?php echo $this->get_education_type_label($type); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="tlms_education_type" id="tlms_education_type" required>
            </div>
            
            <div id="tlms_academic_categories_container" style="display: none; margin-top: 20px;">
                <div id="tlms_categories_dynamic_content">
                    <!-- المحتوى الديناميكي للتصنيفات سيظهر هنا -->
                </div>
            </div>
        </div>

        <style>
        .tlms-education-type-buttons {
            margin: 15px 0;
        }
        .tlms-button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 10px 0;
        }
        .tlms-edu-btn {
            padding: 12px 20px;
            border: 2px solid #3498db;
            background: white;
            color: #3498db;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .tlms-edu-btn:hover {
            background: #3498db;
            color: white;
        }
        .tlms-edu-btn.active {
            background: #3498db;
            color: white;
        }
        .tlms-category-level {
            margin-bottom: 15px;
        }
        .tlms-category-level label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        .tlms-category-level select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            background: white;
        }
        .tlms-loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: tlms-spin 1s linear infinite;
            margin-left: 10px;
        }
        @keyframes tlms-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var currentEducationType = '';
            
            // التعامل مع النقر على أزرار نوع التعليم
            $('.tlms-edu-btn').on('click', function() {
                var educationType = $(this).data('education-type');
                
                // إزالة النشط من جميع الأزرار
                $('.tlms-edu-btn').removeClass('active');
                // إضافة النشط للزر المختار
                $(this).addClass('active');
                
                // تعيين القيمة في الحقل المخفي
                $('#tlms_education_type').val(educationType);
                currentEducationType = educationType;
                
                // إظهار حاوية التصنيفات
                $('#tlms_academic_categories_container').show();
                
                // تحميل التصنيفات عبر AJAX
                loadEducationCategories(educationType, 0);
            });
            
            // التعامل مع تغيير التصنيفات
            $(document).on('change', '.tlms-category-select', function() {
                var level = $(this).data('level');
                var categoryId = $(this).val();
                var container = $(this).closest('.tlms-category-level');
                
                // إزالة المستويات الأعلى
                container.nextAll('.tlms-category-level').remove();
                
                if (categoryId && currentEducationType) {
                    loadChildCategories(categoryId, level + 1, container);
                }
            });
            
            function loadEducationCategories(educationType, level) {
                var $container = $('#tlms_categories_dynamic_content');
                $container.html('<div class="tlms-loading"></div>');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'tlms_get_academic_categories',
                        education_type: educationType,
                        level: level,
                        nonce: '<?php echo wp_create_nonce('tlms_ajax_nonce'); ?>'
                    },
                    success: function(response) {
                        $container.html('');
                        if (response.success && response.data) {
                            $container.html(response.data);
                        } else {
                            $container.html('<p><?php _e('No categories available for this education type.', 'tutor-lms-academic-pro'); ?></p>');
                        }
                    },
                    error: function() {
                        $container.html('<p><?php _e('Error loading categories. Please try again.', 'tutor-lms-academic-pro'); ?></p>');
                    }
                });
            }
            
            function loadChildCategories(parentId, level, container) {
                var $loading = $('<div class="tlms-loading"></div>');
                container.after($loading);
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'tlms_get_academic_categories',
                        education_type: currentEducationType,
                        parent_id: parentId,
                        level: level,
                        nonce: '<?php echo wp_create_nonce('tlms_ajax_nonce'); ?>'
                    },
                    success: function(response) {
                        $loading.remove();
                        if (response.success && response.data) {
                            container.after(response.data);
                        }
                    },
                    error: function() {
                        $loading.remove();
                    }
                });
            }
            
            // التحقق من الصحة قبل الإرسال
            $('form.tutor-registration-form').on('submit', function(e) {
                if (!$('#tlms_education_type').val()) {
                    alert('<?php _e('Please select an education type.', 'tutor-lms-academic-pro'); ?>');
                    e.preventDefault();
                    return false;
                }
                
                // التحقق من اكتمال سلسلة التصنيفات إذا لم تكن عامة
                if ($('#tlms_education_type').val() !== 'general') {
                    var $categorySelects = $('.tlms-category-select');
                    var isValid = true;
                    
                    $categorySelects.each(function() {
                        if (!$(this).val()) {
                            isValid = false;
                            $(this).css('border-color', '#e74c3c');
                        } else {
                            $(this).css('border-color', '#bdc3c7');
                        }
                    });
                    
                    if (!isValid) {
                        alert('<?php _e('Please complete all academic category selections.', 'tutor-lms-academic-pro'); ?>');
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
        </script>
        <?php
    }
    
    public function add_user_profile_fields($user) {
        $options = get_option('tlms_academic_pro_settings');
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $education_type = get_user_meta($user->ID, 'tlms_education_type', true);
        $academic_categories = get_user_meta($user->ID, 'tlms_academic_categories', true);
        if (!is_array($academic_categories)) {
            $academic_categories = array();
        }
        
        $education_types = isset($options['education_types']) ? $options['education_types'] : array();
        ?>
        <h3><?php _e('Academic Information', 'tutor-lms-academic-pro'); ?></h3>
        
        <table class="form-table">
            <tr>
                <th><label for="tlms_education_type"><?php _e('Education Type', 'tutor-lms-academic-pro'); ?></label></th>
                <td>
                    <select name="tlms_education_type" id="tlms_education_type">
                        <option value=""><?php _e('Select Education Type', 'tutor-lms-academic-pro'); ?></option>
                        <?php foreach ($education_types as $type): ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($education_type, $type); ?>>
                                <?php echo $this->get_education_type_label($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr id="tlms_academic_categories_row" style="display: <?php echo $education_type ? 'table-row' : 'none'; ?>;">
                <th><label><?php _e('Academic Categories', 'tutor-lms-academic-pro'); ?></label></th>
                <td id="tlms_academic_categories_container">
                    <?php if ($education_type): ?>
                        <?php echo $this->render_category_fields($education_type, $academic_categories); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        
        <script>
        jQuery(document).ready(function($) {
            $('#tlms_education_type').change(function() {
                var educationType = $(this).val();
                var $container = $('#tlms_academic_categories_container');
                var $row = $('#tlms_academic_categories_row');
                
                if (educationType) {
                    $row.show();
                    
                    // Show loading
                    $container.html('<div class="tlms-loading"></div>');
                    
                    // Load categories via AJAX
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'tlms_get_academic_categories',
                            education_type: educationType,
                            level: 0,
                            selected_categories: <?php echo json_encode($academic_categories); ?>,
                            nonce: '<?php echo wp_create_nonce('tlms_ajax_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $container.html(response.data);
                            } else {
                                $container.html('<p class="description"><?php _e('Error loading categories.', 'tutor-lms-academic-pro'); ?></p>');
                            }
                        },
                        error: function() {
                            $container.html('<p class="description"><?php _e('Error loading categories. Please try again.', 'tutor-lms-academic-pro'); ?></p>');
                        }
                    });
                } else {
                    $row.hide();
                    $container.empty();
                }
            });
        });
        </script>
        <?php
    }
    
    public function render_category_fields($education_type, $selected_categories = array()) {
        $output = '';
        $current_level = 0;
        $current_parent = 0;
        
        while ($current_level < 5) {
            $categories = $this->get_categories_by_parent_and_type($current_parent, $education_type);
            
            if (empty($categories)) {
                break;
            }
            
            $selected = isset($selected_categories[$current_level]) ? $selected_categories[$current_level] : '';
            
            $output .= '<div class="tlms-category-level" data-level="' . $current_level . '">';
            $output .= '<label>' . $this->get_level_label($education_type, $current_level) . '</label>';
            $output .= '<select name="tlms_academic_categories[' . $current_level . ']" class="tlms-category-select" data-level="' . $current_level . '">';
            $output .= '<option value="">' . __('Select', 'tutor-lms-academic-pro') . '</option>';
            
            foreach ($categories as $category) {
                $output .= '<option value="' . $category->term_id . '" ' . selected($selected, $category->term_id, false) . '>';
                $output .= $category->name;
                $output .= '</option>';
            }
            
            $output .= '</select>';
            $output .= '</div>';
            
            // Move to next level if we have a selected category
            if ($selected) {
                $current_parent = $selected;
                $current_level++;
            } else {
                break;
            }
        }
        
        return $output;
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
            ),
            'general' => array(
                __('Select Category', 'tutor-lms-academic-pro'),
                __('Select Subcategory', 'tutor-lms-academic-pro'),
                __('Select Topic', 'tutor-lms-academic-pro'),
                __('Select Level', 'tutor-lms-academic-pro'),
                __('Select Focus Area', 'tutor-lms-academic-pro')
            )
        );
        
        return isset($labels[$education_type][$level]) ? $labels[$education_type][$level] : sprintf(__('Level %d', 'tutor-lms-academic-pro'), $level + 1);
    }
    
    public function ajax_get_academic_categories() {
        check_ajax_referer('tlms_ajax_nonce', 'nonce');
        
        $education_type = sanitize_text_field($_POST['education_type']);
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $level = isset($_POST['level']) ? intval($_POST['level']) : 0;
        $selected_categories = isset($_POST['selected_categories']) ? $_POST['selected_categories'] : array();
        
        $categories = $this->get_categories_by_parent_and_type($parent_id, $education_type);
        
        if (empty($categories)) {
            wp_send_json_success(false);
            return;
        }
        
        $selected = isset($selected_categories[$level]) ? $selected_categories[$level] : '';
        
        ob_start();
        ?>
        <div class="tlms-category-level" data-level="<?php echo $level; ?>">
            <label><?php echo $this->get_level_label($education_type, $level); ?></label>
            <select name="tlms_academic_categories[<?php echo $level; ?>]" class="tlms-category-select" data-level="<?php echo $level; ?>">
                <option value=""><?php _e('Select', 'tutor-lms-academic-pro'); ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->term_id; ?>" <?php selected($selected, $category->term_id); ?>>
                        <?php echo $category->name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
        $output = ob_get_clean();
        
        wp_send_json_success($output);
    }
    
    private function get_categories_by_parent_and_type($parent_id, $education_type) {
        return get_terms(array(
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
    }
    
    private function get_education_type_label($type) {
        $labels = array(
            'university' => __('🏛️ Universities', 'tutor-lms-academic-pro'),
            'school' => __('🎓 Schools', 'tutor-lms-academic-pro'),
            'general' => __('📚 General Courses', 'tutor-lms-academic-pro')
        );
        
        return isset($labels[$type]) ? $labels[$type] : ucfirst($type);
    }
    
    public function save_user_academic_data($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        if (isset($_POST['tlms_education_type'])) {
            update_user_meta($user_id, 'tlms_education_type', sanitize_text_field($_POST['tlms_education_type']));
        }
        
        if (isset($_POST['tlms_academic_categories']) && is_array($_POST['tlms_academic_categories'])) {
            $academic_categories = array_map('intval', $_POST['tlms_academic_categories']);
            update_user_meta($user_id, 'tlms_academic_categories', $academic_categories);
        }
    }
    
    public function save_tutor_student_data($user_id) {
        $this->save_user_academic_data($user_id);
    }
    
    public function save_tutor_instructor_data($user_id) {
        $this->save_user_academic_data($user_id);
    }
}

?>