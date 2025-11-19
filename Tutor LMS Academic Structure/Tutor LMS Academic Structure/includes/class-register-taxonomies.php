<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register academic taxonomies in a safe way (prevent double-declare).
 * This file replaces previous duplicated implementations and ensures
 * taxonomies are registered on init.
 */

if ( ! class_exists( 'TLMS_Custom_Taxonomies' ) ) {

    class TLMS_Custom_Taxonomies {

        private static $instance = null;

        /**
         * Singleton
         */
        public static function instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // Use a relatively late init priority to avoid early WP issues.
            add_action( 'init', array( $this, 'register_academic_taxonomies' ), 20 );
            // If you need to flush rewrite rules on activation, do that in activation hook class.
        }

        /**
         * Register all custom taxonomies used by the plugin.
         */
        public function register_academic_taxonomies() {

            $post_type = 'tutor_course';

            // Universities (hierarchical: University -> Faculty -> Department handled as separate taxonomies)
            register_taxonomy(
                'tlms_university',
                $post_type,
                array(
                    'labels'            => array(
                        'name'          => __( 'Universities', 'tutor-lms-academic-pro' ),
                        'singular_name' => __( 'University', 'tutor-lms-academic-pro' ),
                        'search_items'  => __( 'Search Universities', 'tutor-lms-academic-pro' ),
                        'all_items'     => __( 'All Universities', 'tutor-lms-academic-pro' ),
                        'edit_item'     => __( 'Edit University', 'tutor-lms-academic-pro' ),
                        'view_item'     => __( 'View University', 'tutor-lms-academic-pro' ),
                        'update_item'   => __( 'Update University', 'tutor-lms-academic-pro' ),
                        'add_new_item'  => __( 'Add New University', 'tutor-lms-academic-pro' ),
                    ),
                    'hierarchical'      => true,
                    'public'            => true,
                    'show_ui'           => true,
                    'show_in_menu'      => true,
                    'show_in_nav_menus' => false,
                    'show_admin_column' => true,
                    'show_in_rest'      => true,
                    'rewrite'           => array( 'slug' => 'university' ),
                    'capabilities'      => array(
                        'manage_terms' => 'manage_categories',
                        'edit_terms'   => 'manage_categories',
                        'delete_terms' => 'manage_categories',
                        'assign_terms' => 'edit_posts',
                    ),
                )
            );

            // Faculties (belong to universities conceptually; implemented as separate taxonomy)
            register_taxonomy(
                'tlms_faculty',
                $post_type,
                array(
                    'labels'            => array(
                        'name'          => __( 'Faculties', 'tutor-lms-academic-pro' ),
                        'singular_name' => __( 'Faculty', 'tutor-lms-academic-pro' ),
                    ),
                    'hierarchical'      => true,
                    'public'            => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => array( 'slug' => 'faculty' ),
                    'capabilities'      => array(
                        'manage_terms' => 'manage_categories',
                        'edit_terms'   => 'manage_categories',
                        'delete_terms' => 'manage_categories',
                        'assign_terms' => 'edit_posts',
                    ),
                )
            );

            // Departments
            register_taxonomy(
                'tlms_department',
                $post_type,
                array(
                    'labels'            => array(
                        'name'          => __( 'Departments', 'tutor-lms-academic-pro' ),
                        'singular_name' => __( 'Department', 'tutor-lms-academic-pro' ),
                    ),
                    'hierarchical'      => true,
                    'public'            => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => array( 'slug' => 'department' ),
                    'capabilities'      => array(
                        'manage_terms' => 'manage_categories',
                        'edit_terms'   => 'manage_categories',
                        'delete_terms' => 'manage_categories',
                        'assign_terms' => 'edit_posts',
                    ),
                )
            );

            // Schools types (e.g., public / private / azhari)
            register_taxonomy(
                'tlms_school_type',
                $post_type,
                array(
                    'labels'            => array(
                        'name'          => __( 'School Types', 'tutor-lms-academic-pro' ),
                        'singular_name' => __( 'School Type', 'tutor-lms-academic-pro' ),
                    ),
                    'hierarchical'      => true,
                    'public'            => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => array( 'slug' => 'school-type' ),
                )
            );

            // School grades (level: primary/secondary and then years are implemented via terms)
            register_taxonomy(
                'tlms_school_grade',
                $post_type,
                array(
                    'labels'            => array(
                        'name'          => __( 'School Grades', 'tutor-lms-academic-pro' ),
                        'singular_name' => __( 'School Grade', 'tutor-lms-academic-pro' ),
                    ),
                    'hierarchical'      => true,
                    'public'            => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => array( 'slug' => 'school-grade' ),
                )
            );

            // General course categories for cross-cutting course taxonomy (if you also want to map to tutor's Course Categories later)
            register_taxonomy(
                'tlms_general_course_cat',
                $post_type,
                array(
                    'labels'            => array(
                        'name'          => __( 'General Course Categories', 'tutor-lms-academic-pro' ),
                        'singular_name' => __( 'General Course Category', 'tutor-lms-academic-pro' ),
                    ),
                    'hierarchical'      => true,
                    'public'            => true,
                    'show_ui'           => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => array( 'slug' => 'general-course-cat' ),
                )
            );
        }
    } // end class

} // end if !class_exists

// Initialize (only if not previously initialized elsewhere)
if ( function_exists( 'add_action' ) ) {
    // Create once — prevents multiple instantiations if file accidentally included twice.
    if ( class_exists( 'TLMS_Custom_Taxonomies' ) && ! defined( 'TLMS_CUSTOM_TAXONOMIES_INITIALIZED' ) ) {
        define( 'TLMS_CUSTOM_TAXONOMIES_INITIALIZED', true );
        TLMS_Custom_Taxonomies::instance();
    }
}
