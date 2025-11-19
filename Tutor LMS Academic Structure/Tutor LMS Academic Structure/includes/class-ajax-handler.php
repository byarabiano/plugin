<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TLMS_Ajax_Handler {

    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_ajax_tlms_get_academic_data', array($this, 'get_academic_data'));
        add_action('wp_ajax_tlms_add_university', array($this, 'add_university'));
        add_action('wp_ajax_tlms_add_faculty', array($this, 'add_faculty'));
        add_action('wp_ajax_tlms_add_department', array($this, 'add_department'));
        add_action('wp_ajax_tlms_delete_term', array($this, 'delete_term'));
    }

    // -------- Load Data -------- //
    public function get_academic_data() {

        $universities = get_terms([
            'taxonomy' => 'tlms_university',
            'hide_empty' => false,
        ]);

        $faculties = get_terms([
            'taxonomy' => 'tlms_faculty',
            'hide_empty' => false,
        ]);

        $departments = get_terms([
            'taxonomy' => 'tlms_department',
            'hide_empty' => false,
        ]);

        // Universities List HTML
        $universities_html = '';
        foreach($universities as $u){
            $universities_html .= "<li>{$u->name} <a href='#' class='tlms-delete' data-id='{$u->term_id}' data-tax='tlms_university'>Delete</a></li>";
        }

        // Faculties List HTML
        $faculties_html = '';
        foreach($faculties as $f){
            $parent = get_term($f->parent, 'tlms_university');
            $pname = $parent ? " ({$parent->name})" : '';
            $faculties_html .= "<li>{$f->name}{$pname} <a href='#' class='tlms-delete' data-id='{$f->term_id}' data-tax='tlms_faculty'>Delete</a></li>";
        }

        // Departments List HTML
        $departments_html = '';
        foreach($departments as $d){
            $parent = get_term($d->parent, 'tlms_faculty');
            $pname = $parent ? " ({$parent->name})" : '';
            $departments_html .= "<li>{$d->name}{$pname} <a href='#' class='tlms-delete' data-id='{$d->term_id}' data-tax='tlms_department'>Delete</a></li>";
        }

        // Dropdown Options
        $university_options = '<option value="">Select University</option>';
        foreach($universities as $u){
            $university_options .= "<option value='{$u->term_id}'>{$u->name}</option>";
        }

        $faculty_options = '<option value="">Select Faculty</option>';
        foreach($faculties as $f){
            $faculty_options .= "<option value='{$f->term_id}'>{$f->name}</option>";
        }

        wp_send_json_success([
            'universities_html' => $universities_html,
            'faculties_html' => $faculties_html,
            'departments_html' => $departments_html,
            'university_options' => $university_options,
            'faculty_options' => $faculty_options,
        ]);
    }


    // -------- Add University -------- //
    public function add_university() {
        $name = sanitize_text_field($_POST['name']);
        wp_insert_term($name, 'tlms_university');
        wp_send_json_success();
    }

    // -------- Add Faculty -------- //
    public function add_faculty() {
        $name = sanitize_text_field($_POST['name']);
        $parent = intval($_POST['parent']);
        wp_insert_term($name, 'tlms_faculty', ['parent' => $parent]);
        wp_send_json_success();
    }

    // -------- Add Department -------- //
    public function add_department() {
        $name = sanitize_text_field($_POST['name']);
        $faculty = intval($_POST['faculty']);
        wp_insert_term($name, 'tlms_department', ['parent' => $faculty]);
        wp_send_json_success();
    }

    // -------- Delete Term -------- //
    public function delete_term() {
        $id = intval($_POST['id']);
        $tax = sanitize_text_field($_POST['tax']);
        wp_delete_term($id, $tax);
        wp_send_json_success();
    }

}

TLMS_Ajax_Handler::instance();
