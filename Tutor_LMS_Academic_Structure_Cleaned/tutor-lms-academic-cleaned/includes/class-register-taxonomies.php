<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TLMS_Custom_Taxonomies' ) ) {

class TLMS_Custom_Taxonomies {

    public function __construct() {
        add_action('init', array($this, 'register_taxonomies'), 11);
        add_action('init', array($this, 'force_attach'), 20);
    }

    public function register_taxonomies() {

        if ( ! post_type_exists('tutor_course') ) {
            return;
        }

        register_taxonomy('tlms_university', array('tutor_course'), array(
            'label' => 'Universities',
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
        ));

        register_taxonomy('tlms_faculty', array('tutor_course'), array(
            'label' => 'Faculties',
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
        ));

        register_taxonomy('tlms_department', array('tutor_course'), array(
            'label' => 'Departments',
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
        ));
    }

    public function force_attach() {
        register_taxonomy_for_object_type('tlms_university', 'tutor_course');
        register_taxonomy_for_object_type('tlms_faculty', 'tutor_course');
        register_taxonomy_for_object_type('tlms_department', 'tutor_course');
    }
}

new TLMS_Custom_Taxonomies();

}
