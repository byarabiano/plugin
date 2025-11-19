<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class TLMS_Admin_Settings {
    
    private static $instance = null;
    private $settings_page;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'register_academic_structure_page'));
    }
    
    public function add_admin_menu() {
        $this->settings_page = add_submenu_page(
            'tutor',
            __('Academic Pro Settings', 'tutor-lms-academic-pro'),
            __('Academic Pro Settings', 'tutor-lms-academic-pro'),
            'manage_tutor',
            'tlms-academic-pro-settings',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('tlms_academic_pro_settings', 'tlms_academic_pro_settings', array($this, 'sanitize_settings'));
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== $this->settings_page) return;
        
        wp_enqueue_script('tlms-admin-settings', TLMS_ACADEMIC_PRO_URL . 'admin/js/admin.js', array('jquery'), TLMS_ACADEMIC_PRO_VERSION, true);
        wp_enqueue_style('tlms-admin-style', TLMS_ACADEMIC_PRO_URL . 'admin/css/admin.css', array(), TLMS_ACADEMIC_PRO_VERSION);
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        $sanitized['enabled'] = isset($input['enabled']) ? (bool)$input['enabled'] : false;
        $sanitized['max_levels'] = isset($input['max_levels']) ? intval($input['max_levels']) : 5;
        $sanitized['enable_integrations'] = isset($input['enable_integrations']) ? (bool)$input['enable_integrations'] : false;
        $sanitized['default_user_category'] = sanitize_text_field($input['default_user_category'] ?? '');
        
        $sanitized['education_types'] = array();
        if (isset($input['education_types']) && is_array($input['education_types'])) {
            foreach ($input['education_types'] as $type) {
                $sanitized['education_types'][] = sanitize_text_field($type);
            }
        }
        return $sanitized;
    }

    public function register_academic_structure_page() {
        add_menu_page(
            __('Academic Structure', 'tutor-lms-academic-pro'),
            __('Academic Structure', 'tutor-lms-academic-pro'),
            'manage_options',
            'tlms-academic-structure',
            array($this, 'render_academic_structure_page'),
            'dashicons-networking',
            56
        );
    }

    public function render_academic_structure_page() {
        include TLMS_ACADEMIC_PRO_PATH . 'admin/partials/academic-structure-page.php';
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('tlms_academic_pro_settings');
                do_settings_sections('tlms-academic-pro-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

?>
