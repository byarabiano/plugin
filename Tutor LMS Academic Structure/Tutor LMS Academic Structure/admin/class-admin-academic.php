<?php
if (!defined('ABSPATH')) exit;

class TLMS_Admin_Academic_Page {

    public function __construct() {
        add_action('admin_menu', array($this, 'register_menu_page'));
    }

    public function register_menu_page() {
        add_menu_page(
            __('Academic Structure', 'tutor-lms-academic-pro'),
            __('Academic Structure', 'tutor-lms-academic-pro'),
            'manage_options',
            'tlms-academic-structure',
            array($this, 'render_page'),
            'dashicons-welcome-learn-more',
            56
        );
    }

    public function render_page() {

        $active = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'universities';

        echo '<div class="wrap"><h1>' . __('Academic Structure Management', 'tutor-lms-academic-pro') . '</h1>';

        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=tlms-academic-structure&tab=universities" class="nav-tab ' . ($active === 'universities' ? 'nav-tab-active' : '') . '">الجامعات</a>';
        echo '<a href="?page=tlms-academic-structure&tab=schools" class="nav-tab ' . ($active === 'schools' ? 'nav-tab-active' : '') . '">المدارس</a>';
        echo '<a href="?page=tlms-academic-structure&tab=general" class="nav-tab ' . ($active === 'general' ? 'nav-tab-active' : '') . '">كورسات عامة</a>';
        echo '</h2>';

        echo '<div style="margin-top:20px;">';

        switch ($active) {
            case 'schools':
                include TLMS_ACADEMIC_PRO_PATH . 'admin/partials/tab-schools.php';
                break;

            case 'general':
                include TLMS_ACADEMIC_PRO_PATH . 'admin/partials/tab-general.php';
                break;

            case 'universities':
            default:
                include TLMS_ACADEMIC_PRO_PATH . 'admin/partials/tab-universities.php';
                break;
        }

        echo '</div></div>';
    }
}

new TLMS_Admin_Academic_Page();
