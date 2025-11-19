<?php
/**
 * Plugin Name: Tutor LMS Academic Pro
 * Plugin URI: https://yourwebsite.com
 * Description: Advanced academic structure for Tutor LMS with multi-level categories and content isolation
 * Version: 1.0.0
 * Author: byarabiano
 * Text Domain: tutor-lms-academic-pro
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TLMS_ACADEMIC_PRO_VERSION', '1.0.0');
define('TLMS_ACADEMIC_PRO_FILE', __FILE__);
define('TLMS_ACADEMIC_PRO_PATH', plugin_dir_path(__FILE__));
define('TLMS_ACADEMIC_PRO_URL', plugin_dir_url(__FILE__));

register_activation_hook(__FILE__, 'tlms_academic_pro_check_dependencies');

function tlms_academic_pro_check_dependencies() {
    if (!class_exists('TUTOR\Tutor')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Tutor LMS Academic Pro requires Tutor LMS to be installed and activated.', 'tutor-lms-academic-pro'));
    }
}

class TutorLMS_Academic_Pro {
    
    private static $instance = null;
    private $components = array();

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        // require class files (no immediate instance() calls inside those files)
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-activation.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-custom-taxonomies.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-admin-settings.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-user-registration.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-course-visibility.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-multisite-support.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-integration-handler.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-ajax-handler.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-export-import.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-compatibility.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-register-taxonomies.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-save-course-academy.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-save-user-academy.php';
        require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-filter-courses.php';
		require_once TLMS_ACADEMIC_PRO_PATH . 'includes/class-academic-category-mapping.php';
    }
    
    private function safe_init_component($class_name) {
        if (! class_exists($class_name)) {
            return null;
        }
        // if class implements a singleton instance static method, use it
        if (method_exists($class_name, 'instance')) {
            return call_user_func(array($class_name, 'instance'));
        }
        // otherwise create a new object (if constructor exists)
        return new $class_name();
    }

    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        // Delay initialization to 'init' to be safer about WP internals
        add_action('init', array($this, 'init_plugin'), 20);

        if (is_multisite()) {
            add_action('network_admin_menu', array($this, 'add_network_admin_menu'));
        }

        // add filter for course archive args (example)
        add_filter('tutor_course_archive_arg', array($this, 'filter_course_archive_query'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            'tutor-lms-academic-pro', 
            false, 
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    public function init_plugin() {
        // Initialize components using safe_init_component helper
        $this->components['activation'] = $this->safe_init_component('TLMS_Activation');
        $this->components['taxonomies'] = $this->safe_init_component('TLMS_Custom_Taxonomies');
        $this->components['admin_settings'] = $this->safe_init_component('TLMS_Admin_Settings');
        $this->components['user_registration'] = $this->safe_init_component('TLMS_User_Registration');
        $this->components['course_visibility'] = $this->safe_init_component('TLMS_Course_Visibility');
        $this->components['multisite'] = $this->safe_init_component('TLMS_Multisite_Support');
        $this->components['integration'] = $this->safe_init_component('TLMS_Integration_Handler');
        $this->components['ajax'] = $this->safe_init_component('TLMS_Ajax_Handler');
        $this->components['export_import'] = $this->safe_init_component('TLMS_Export_Import');
        $this->components['compatibility'] = $this->safe_init_component('TLMS_Compatibility');
        $this->components['register_taxonomies'] = $this->safe_init_component('TLMS_Register_Academic_Taxonomies');
        $this->components['save_course_academy'] = $this->safe_init_component('TLMS_Save_Course_Academy');
        $this->components['save_user_academy'] = $this->safe_init_component('TLMS_Save_User_Academy');
        $this->components['filter_courses'] = $this->safe_init_component('TLMS_Filter_Courses_By_Academic_Path');

        // If register_taxonomies was not set up inside TLMS_Custom_Taxonomies earlier,
        // ensure taxonomies get registered now:
        if (isset($this->components['taxonomies']) && method_exists($this->components['taxonomies'], 'register_taxonomies')) {
            // registration is already hooked on init from constructor of that class
        }

        // flush rewrite rules on activation only (handled by activation class)
    }

    public function filter_course_archive_query($args) {
        // Filtering logic — actual implementation likely in class-filter-courses.php
        return $args;
    }
    
    public function add_network_admin_menu() {
        // list network admin menu items if multisite
    }
}

function tutor_lms_academic_pro() {
    return TutorLMS_Academic_Pro::instance();
}

add_action('plugins_loaded', 'tutor_lms_academic_pro');
