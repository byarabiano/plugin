<?php
/*
Plugin Name: Tutor Branching
Description: إضافة بسيطة لإدارة الكليات والتخصصات وربطها بـ Tutor LMS مع قيود الرؤية.
Version: 1.0.0
Author: اسمك
Text Domain: tutor-branching
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once plugin_dir_path( __FILE__ ) . 'includes/class-tb-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-tb-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-tb-metaboxes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-tb-user.php';

class Tutor_Branching {
    public function __construct() {
        add_action( 'init', array( $this, 'tb_register_taxonomies' ) );
        add_action( 'plugins_loaded', array( $this, 'tb_load_textdomain' ) );

        // Safe, conditional admin menu setup
        if ( class_exists( 'TB_Admin' ) && method_exists( 'TB_Admin', 'tb_setup_menu' ) ) {
            add_action( 'admin_menu', array( 'TB_Admin', 'tb_setup_menu' ) );
        }

        // Meta boxes for Tutor LMS courses
        if ( class_exists( 'TB_Metaboxes' ) ) {
            if ( method_exists( 'TB_Metaboxes', 'tb_register_course_metabox' ) ) {
                add_action( 'add_meta_boxes', array( 'TB_Metaboxes', 'tb_register_course_metabox' ) );
            }
            if ( method_exists( 'TB_Metaboxes', 'tb_save_course_meta' ) ) {
                add_action( 'save_post', array( 'TB_Metaboxes', 'tb_save_course_meta' ), 10, 2 );
            }
        }

        // User profile fields
        if ( class_exists( 'TB_User' ) ) {
            if ( method_exists( 'TB_User', 'tb_user_profile_fields' ) ) {
                add_action( 'show_user_profile', array( 'TB_User', 'tb_user_profile_fields' ) );
            }
            if ( method_exists( 'TB_User', 'tb_user_profile_fields' ) ) {
                add_action( 'edit_user_profile', array( 'TB_User', 'tb_user_profile_fields' ) );
            }
            if ( method_exists( 'TB_User', 'tb_save_user_profile' ) ) {
                add_action( 'personal_options_update', array( 'TB_User', 'tb_save_user_profile' ) );
                add_action( 'edit_user_profile_update', array( 'TB_User', 'tb_save_user_profile' ) );
            }
        }

        // Initialize TB_Settings (ensure settings exist)
        if ( class_exists( 'TB_Settings' ) ) {
            // instantiate to ensure hooks are registered
            new TB_Settings();
        }
    }

    public function tb_register_taxonomies() {
        // Faculties (الكليات)
        if ( function_exists( 'register_taxonomy' ) ) {
            register_taxonomy( 'tb_faculty', 'user', array(
                'label' => 'الكليات',
                'rewrite' => array( 'slug' => 'faculty' ),
                'hierarchical' => true,
                'public' => true,
            ) );
        }

        // Subcategories (التصنيفات الفرعية) على مستوى الكورسات
        if ( function_exists( 'register_taxonomy' ) ) {
            register_taxonomy( 'tb_subcategory', 'course', array(
                'label' => 'التصنيفات الفرعية',
                'rewrite' => array( 'slug' => 'subcategory' ),
                'hierarchical' => true,
                'public' => true,
            ) );
        }
    }

    public function tb_load_textdomain() {
        load_plugin_textdomain( 'tutor-branching', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }
}

// Initialize
new Tutor_Branching();