<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TB_Faculty_CRUD {

    public function __construct() {
        add_action( 'init', array( $this, 'register_branching_taxonomies' ) );
    }

    /**
     * تسجيل التصنيفات (جامعة - كلية - تخصص)
     */
    public function register_branching_taxonomies() {

        // ✅ الجامعات
        register_taxonomy(
            'tb_university',
            'courses', // قد يكون tutor_course حسب Tutor LMS لديك
            array(
                'labels' => array(
                    'name' => __( 'Universities', 'tutor-branching' ),
                    'singular_name' => __( 'University', 'tutor-branching' ),
                ),
                'hierarchical' => true,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_admin_column' => true,
                'rewrite' => array( 'slug' => 'university' ),
            )
        );

        // ✅ الكليات
        register_taxonomy(
            'tb_faculty',
            'courses',
            array(
                'labels' => array(
                    'name' => __( 'Faculties', 'tutor-branching' ),
                    'singular_name' => __( 'Faculty', 'tutor-branching' ),
                ),
                'hierarchical' => true,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_admin_column' => true,
                'rewrite' => array( 'slug' => 'faculty' ),
            )
        );

        // ✅ التخصصات
        register_taxonomy(
            'tb_department',
            'courses',
            array(
                'labels' => array(
                    'name' => __( 'Departments', 'tutor-branching' ),
                    'singular_name' => __( 'Department', 'tutor-branching' ),
                ),
                'hierarchical' => true,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_admin_column' => true,
                'rewrite' => array( 'slug' => 'department' ),
            )
        );
    }
}

new TB_Faculty_CRUD();
