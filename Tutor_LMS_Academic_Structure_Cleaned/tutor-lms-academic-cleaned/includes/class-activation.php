<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class TLMS_Activation {
    
    public static function init() {
        register_activation_hook(TLMS_ACADEMIC_PRO_FILE, array(__CLASS__, 'activate'));
        register_deactivation_hook(TLMS_ACADEMIC_PRO_FILE, array(__CLASS__, 'deactivate'));
    }
    
    public static function activate($network_wide) {
        if (is_multisite() && $network_wide) {
            self::activate_multisite($network_wide);
        } else {
            self::activate_single_site();
        }
    }
    
    private static function activate_single_site() {
        // Set default options
        $default_options = array(
            'enabled' => true,
            'max_levels' => 5,
            'education_types' => array('university', 'school', 'general'),
            'default_user_category' => 'general',
            'isolation_enabled' => true
        );
        
        update_option('tlms_academic_pro_settings', $default_options);
        
        // إنشاء تصنيفات مسبقة - محدث ومحسّن
        self::create_sample_categories();
        
        // Flush rewrite rules for custom taxonomies
        flush_rewrite_rules();
    }
    
    private static function create_sample_categories() {
        // تصنيفات الجامعات - محدثة
        $university_categories = array(
            'جامعة القاهرة' => array(
                'كلية الهندسة' => array(
                    'هندسة مدنية' => array(),
                    'هندسة كهربائية' => array(),
                    'هندسة ميكانيكية' => array()
                ),
                'كلية الطب' => array(
                    'طب عام' => array(),
                    'طب أسنان' => array(),
                    'صيدلة' => array()
                )
            ),
            'جامعة عين شمس' => array(
                'كلية التجارة' => array(
                    'محاسبة' => array(),
                    'إدارة أعمال' => array(),
                    'تسويق' => array()
                )
            ),
            'جامعة الأسكندرية' => array(
                'كلية الآداب' => array(
                    'لغة عربية' => array(),
                    'لغة إنجليزية' => array(),
                    'تاريخ' => array()
                )
            )
        );
        
        // تصنيفات المدارس - محدثة
        $school_categories = array(
            'حكومية' => array(
                'ابتدائية' => array(
                    'الصف الأول' => array(),
                    'الصف الثاني' => array(),
                    'الصف الثالث' => array()
                ),
                'إعدادية' => array(
                    'الصف الأول الإعدادي' => array(),
                    'الصف الثاني الإعدادي' => array(),
                    'الصف الثالث الإعدادي' => array()
                ),
                'ثانوية' => array(
                    'الصف الأول الثانوي' => array(),
                    'الصف الثاني الثانوي' => array(),
                    'الصف الثالث الثانوي' => array()
                )
            ),
            'خاصة' => array(
                'لغات' => array(
                    'الإنجليزية' => array(),
                    'الفرنسية' => array()
                ),
                'دولية' => array(
                    'البكالوريا الدولية' => array(),
                    'IGCSE' => array()
                )
            )
        );
        
        // تصنيفات عامة - محدثة
        $general_categories = array(
            'تطوير الذات' => array(
                'مهارات القيادة' => array(),
                'إدارة الوقت' => array(),
                'التخطيط الاستراتيجي' => array()
            ),
            'اللغات' => array(
                'الإنجليزية' => array(),
                'الفرنسية' => array(),
                'الألمانية' => array()
            ),
            'التكنولوجيا' => array(
                'برمجة' => array(),
                'تصميم جرافيك' => array(),
                'تحليل بيانات' => array()
            )
        );
        
        // إنشاء تصنيفات الجامعات
        foreach ($university_categories as $university => $colleges) {
            $univ_term = self::create_category($university, 'university');
            
            if (!is_wp_error($univ_term)) {
                foreach ($colleges as $college => $departments) {
                    $college_term = self::create_category($college, 'university', $univ_term['term_id']);
                    
                    if (!is_wp_error($college_term)) {
                        foreach ($departments as $department => $programs) {
                            $dept_term = self::create_category($department, 'university', $college_term['term_id']);
                        }
                    }
                }
            }
        }
        
        // إنشاء تصنيفات المدارس
        foreach ($school_categories as $school_type => $levels) {
            $type_term = self::create_category($school_type, 'school');
            
            if (!is_wp_error($type_term)) {
                foreach ($levels as $level => $grades) {
                    $level_term = self::create_category($level, 'school', $type_term['term_id']);
                    
                    if (!is_wp_error($level_term)) {
                        foreach ($grades as $grade => $empty) {
                            $grade_term = self::create_category($grade, 'school', $level_term['term_id']);
                        }
                    }
                }
            }
        }
        
        // إنشاء تصنيفات عامة
        foreach ($general_categories as $main_category => $sub_categories) {
            $main_term = self::create_category($main_category, 'general');
            
            if (!is_wp_error($main_term)) {
                foreach ($sub_categories as $sub_category => $empty) {
                    $sub_term = self::create_category($sub_category, 'general', $main_term['term_id']);
                }
            }
        }
    }
    
    private static function create_category($name, $education_type, $parent = 0) {
        $term = wp_insert_term(
            $name,
            'tlms_academic_category',
            array(
                'slug' => sanitize_title($name . '-' . $education_type),
                'parent' => $parent
            )
        );
        
        if (!is_wp_error($term)) {
            update_term_meta($term['term_id'], 'education_type', $education_type);
        }
        
        return $term;
    }
    
    private static function activate_multisite($network_wide) {
        global $wpdb;
        
        if ($network_wide) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                self::activate_single_site();
                restore_current_blog();
            }
        }
    }
    
    public static function deactivate() {
        flush_rewrite_rules();
    }
}

?>