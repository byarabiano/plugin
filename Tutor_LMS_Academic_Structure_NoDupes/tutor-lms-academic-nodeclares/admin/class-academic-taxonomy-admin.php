<?php
if (!defined('ABSPATH')) exit;

class TLMS_Academic_Taxonomy_Admin {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu_page']);
    }

    public static function add_menu_page() {
        add_menu_page(
            __('Academic Structure', 'tutor-lms-academic-pro'),
            __('Academic Structure', 'tutor-lms-academic-pro'),
            'manage_options',
            'tlms-academic-structure',
            [__CLASS__, 'render_admin_page'],
            'dashicons-welcome-learn-more',
            55
        );
    }

    public static function render_admin_page() {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'universities';

        ?>
        <div class="wrap">
            <h1><?php _e('Academic Structure', 'tutor-lms-academic-pro'); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=tlms-academic-structure&tab=universities" class="nav-tab <?php echo ($tab=='universities')?'nav-tab-active':''; ?>">
                    <?php _e('Universities', 'tutor-lms-academic-pro'); ?>
                </a>
                <a href="?page=tlms-academic-structure&tab=schools" class="nav-tab <?php echo ($tab=='schools')?'nav-tab-active':''; ?>">
                    <?php _e('Schools', 'tutor-lms-academic-pro'); ?>
                </a>
                <a href="?page=tlms-academic-structure&tab=general" class="nav-tab <?php echo ($tab=='general')?'nav-tab-active':''; ?>">
                    <?php _e('General Courses', 'tutor-lms-academic-pro'); ?>
                </a>
            </h2>

            <div style="margin-top:20px;">
                <?php
                switch ($tab) {

                    case 'universities':
                        require TLMS_ACADEMIC_PRO_PATH . 'admin/partials/tab-universities.php';
                        break;

                    case 'schools':
                        require TLMS_ACADEMIC_PRO_PATH . 'admin/partials/tab-schools.php';
                        break;

                    case 'general':
                        require TLMS_ACADEMIC_PRO_PATH . 'admin/partials/tab-general.php';
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
}

TLMS_Academic_Taxonomy_Admin::init();
