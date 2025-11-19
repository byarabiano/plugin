<?php
return;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TLMS_Custom_Taxonomies {

    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // التسجيل خلف الكواليس فقط - بدون إظهار أي قوائم
        add_action('init', array($this, 'register_academic_taxonomies'), 8);
    }

    public function register_academic_taxonomies() {

        // 1) الجامعات
        register_taxonomy(
            'tlms_university',
            'courses',
            array(
                'labels' => array(
                    'name' => __('Universities', 'tutor-lms-academic-pro'),
                    'singular_name' => __('University', 'tutor-lms-academic-pro')
                ),
                'public' => false,
                'show_ui' => false,
                'hierarchical' => true,
                'show_in_quick_edit' => false,
                'meta_box_cb' => false,
                'rewrite' => false
            )
        );

        // 2) الكليات
        register_taxonomy(
            'tlms_faculty',
            'courses',
            array(
                'labels' => array(
                    'name' => __('Faculties', 'tutor-lms-academic-pro'),
                    'singular_name' => __('Faculty', 'tutor-lms-academic-pro')
                ),
                'public' => false,
                'show_ui' => false,
                'hierarchical' => true,
                'show_in_quick_edit' => false,
                'meta_box_cb' => false,
                'rewrite' => false
            )
        );

        // 3) الأقسام
        register_taxonomy(
            'tlms_department',
            'courses',
            array(
                'labels' => array(
                    'name' => __('Departments', 'tutor-lms-academic-pro'),
                    'singular_name' => __('Department', 'tutor-lms-academic-pro')
                ),
                'public' => false,
                'show_ui' => false,
                'hierarchical' => true,
                'show_in_quick_edit' => false,
                'meta_box_cb' => false,
                'rewrite' => false
            )
        );

        // 4) المرحلة الدراسية (ابتدائي / إعدادي / ثانوي)
        register_taxonomy(
            'tlms_school_level',
            'courses',
            array(
                'labels' => array(
                    'name' => __('School Levels', 'tutor-lms-academic-pro'),
                    'singular_name' => __('School Level', 'tutor-lms-academic-pro')
                ),
                'public' => false,
                'show_ui' => false,
                'hierarchical' => true,
                'show_in_quick_edit' => false,
                'meta_box_cb' => false,
                'rewrite' => false
            )
        );

        // 5) الصف الدراسي (أولى – ثانية – ...)
        register_taxonomy(
            'tlms_school_grade',
            'courses',
            array(
                'labels' => array(
                    'name' => __('School Grades', 'tutor-lms-academic-pro'),
                    'singular_name' => __('School Grade', 'tutor-lms-academic-pro')
                ),
                'public' => false,
                'show_ui' => false,
                'hierarchical' => true,
                'show_in_quick_edit' => false,
                'meta_box_cb' => false,
                'rewrite' => false
            )
        );

        // 6) الكورسات العامة (لن نستخدم تاكسونومي - سيتم إدارتها عبر Flag فقط)
    }

}
