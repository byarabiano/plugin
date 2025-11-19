<?php
if (!defined('ABSPATH')) exit;

class TLMS_Academic_Manager_Page {

    public function __construct() {
        add_action('admin_menu', array($this, 'register_menu_page'));
    }

    public function register_menu_page() {
        add_menu_page(
            __('Academic Structure', 'tutor-lms-academic-pro'),
            __('Academic Structure', 'tutor-lms-academic-pro'),
            'manage_options',
            'tlms-academic-manager',
            array($this, 'render_page'),
            'dashicons-welcome-learn-more',
            60
        );
    }

    public function render_page() {

        $active = $_GET['tab'] ?? 'universities';

        ?>
        <div class="wrap">
            <h1><?php _e('Academic Structure Manager', 'tutor-lms-academic-pro'); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=tlms-academic-manager&tab=universities" class="nav-tab <?php echo ($active == 'universities') ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Universities', 'tutor-lms-academic-pro'); ?>
                </a>
                <a href="?page=tlms-academic-manager&tab=schools" class="nav-tab <?php echo ($active == 'schools') ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Schools', 'tutor-lms-academic-pro'); ?>
                </a>
                <a href="?page=tlms-academic-manager&tab=general" class="nav-tab <?php echo ($active == 'general') ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General Courses', 'tutor-lms-academic-pro'); ?>
                </a>
            </h2>

            <div style="margin-top:20px;">
                <?php
                switch($active) {
                    case 'universities':
                        $this->render_taxonomy('tlms_university');
                        $this->render_taxonomy('tlms_faculty');
                        $this->render_taxonomy('tlms_department');
                        break;

                    case 'schools':
                        $this->render_taxonomy('tlms_school');
                        $this->render_taxonomy('tlms_grade');
                        break;

                    case 'general':
                        echo "<p style='font-size:16px;'>" . __('General courses do not require academic classification. Just set Education Type to General when creating a course.', 'tutor-lms-academic-pro') . "</p>";
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    private function render_taxonomy($taxonomy) {
        if (!taxonomy_exists($taxonomy)) return;

        $tax = get_taxonomy($taxonomy);

        echo "<h2>{$tax->labels->name}</h2>";
        echo '<p><a class="button button-primary" href="edit-tags.php?taxonomy='.$taxonomy.'&post_type=tutor_course">';
        _e('Manage', 'tutor-lms-academic-pro');
        echo '</a></p><hr>';
    }
}

new TLMS_Academic_Manager_Page();
