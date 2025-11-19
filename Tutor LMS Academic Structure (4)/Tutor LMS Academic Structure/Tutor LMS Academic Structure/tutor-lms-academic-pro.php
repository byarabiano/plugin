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

// منع الوصول المباشر
if (!defined('ABSPATH')) {
    exit;
}

// ثوابت الإضافة
define('TLMS_ACADEMIC_PRO_VERSION', '1.0.0');
define('TLMS_ACADEMIC_PRO_FILE', __FILE__);
define('TLMS_ACADEMIC_PRO_PATH', plugin_dir_path(__FILE__));
define('TLMS_ACADEMIC_PRO_URL', plugin_dir_url(__FILE__));

// التحقق من وجود Tutor LMS
register_activation_hook(__FILE__, 'tlms_academic_pro_check_dependencies');

function tlms_academic_pro_check_dependencies() {
    if (!class_exists('TUTOR\Tutor')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Tutor LMS Academic Pro requires Tutor LMS to be installed and activated.', 'tutor-lms-academic-pro'));
    }
}

// الفئة الرئيسية للإضافة
class TutorLMS_Academic_Pro {
    
    private static $instance = null;
    
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
    }
    
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init_plugin'), 5);
        
        // دعم Multisite
        if (is_multisite()) {
            add_action('network_admin_menu', array($this, 'add_network_admin_menu'));
        }
        
        // إضافة فلتر التصنيفات - محدث مع الخطاف الصحيح
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
        // تهيئة جميع المكونات
        TLMS_Activation::init();
        TLMS_Custom_Taxonomies::instance();
        TLMS_Admin_Settings::instance();
        TLMS_User_Registration::instance();
        TLMS_Course_Visibility::instance();
        TLMS_Multisite_Support::instance();
        TLMS_Integration_Handler::instance();
        TLMS_Ajax_Handler::instance();
        TLMS_Export_Import::instance();
        TLMS_Compatibility::instance();
    }
    
    public function filter_course_archive_query($args) {
        // سيتم تنفيذ التصفية في class-course-visibility
        return $args;
    }
    
    public function add_network_admin_menu() {
        // قائمة إدارة الشبكة لـ Multisite
    }
}

// تهيئة الإضافة
function tutor_lms_academic_pro() {
    return TutorLMS_Academic_Pro::instance();
}

// بدء الإضافة
add_action('plugins_loaded', 'tutor_lms_academic_pro');
?>