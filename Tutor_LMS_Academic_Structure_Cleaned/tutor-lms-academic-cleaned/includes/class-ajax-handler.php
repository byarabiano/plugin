<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TLMS_Ajax_Handler {

	private static $instance = null;

	/**
	 * Get Singleton Instance
	 */
	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		// إضافة جامعة
		add_action( 'wp_ajax_tlms_add_university', array( $this, 'add_university' ) );

		// إضافة كلية
		add_action( 'wp_ajax_tlms_add_faculty', array( $this, 'add_faculty' ) );

		// إضافة قسم
		add_action( 'wp_ajax_tlms_add_department', array( $this, 'add_department' ) );

		// الحصول على الكليات حسب الجامعة
		add_action( 'wp_ajax_tlms_get_faculties', array( $this, 'get_faculties' ) );

		// الحصول على الأقسام حسب الكلية
		add_action( 'wp_ajax_tlms_get_departments', array( $this, 'get_departments' ) );
	}

	/**
	 * إضافة جامعة
	 */
	public function add_university() {
		check_ajax_referer('tlms_admin_nonce', 'nonce');

		$name = sanitize_text_field($_POST['name']);

		if ( empty($name) ) {
			wp_send_json_error('يرجى إدخال اسم الجامعة.');
		}

		$term = wp_insert_term($name, 'tlms_universities');

		wp_send_json( $term );
	}

	/**
	 * إضافة كلية
	 */
	public function add_faculty() {
		check_ajax_referer('tlms_admin_nonce', 'nonce');

		$university_id = intval($_POST['university']);
		$name         = sanitize_text_field($_POST['name']);

		$term = wp_insert_term($name, 'tlms_faculties', array(
			'parent' => $university_id
		));

		wp_send_json($term);
	}

	/**
	 * إضافة قسم
	 */
	public function add_department() {
		check_ajax_referer('tlms_admin_nonce', 'nonce');

		$faculty_id = intval($_POST['faculty']);
		$name      = sanitize_text_field($_POST['name']);

		$term = wp_insert_term($name, 'tlms_departments', array(
			'parent' => $faculty_id
		));

		wp_send_json($term);
	}

	/**
	 * جلب الكليات حسب الجامعة
	 */
	public function get_faculties() {
		check_ajax_referer('tlms_admin_nonce', 'nonce');

		$university_id = intval($_POST['university']);

		$faculties = get_terms(array(
			'taxonomy'   => 'tlms_faculties',
			'hide_empty' => false,
			'parent'     => $university_id
		));

		wp_send_json($faculties);
	}

	/**
	 * جلب الأقسام حسب الكلية
	 */
	public function get_departments() {
		check_ajax_referer('tlms_admin_nonce', 'nonce');

		$faculty_id = intval($_POST['faculty']);

		$departments = get_terms(array(
			'taxonomy'   => 'tlms_departments',
			'hide_empty' => false,
			'parent'     => $faculty_id
		));

		wp_send_json($departments);
	}
}

// Initialize AJAX Handler on load
TLMS_Ajax_Handler::instance();
