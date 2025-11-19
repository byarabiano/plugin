<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TLMS_Custom_Taxonomy_Extend {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_academic_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_academic_fields' ) );
    }

    /**
     * Add meta boxes to Tutor LMS Course editor
     */
    public function add_academic_meta_boxes() {
        add_meta_box(
            'tlms_academic_relations',
            __('Academic Structure', 'tutor-lms-academic-pro'),
            array($this, 'render_academic_meta_box'),
            'tutor_course',
            'side',
            'default'
        );
    }

    /**
     * Render Meta Box Fields
     */
    public function render_academic_meta_box($post) {

        $selected_university = wp_get_post_terms($post->ID, 'tlms_university', array('fields' => 'ids'));
        $selected_faculty    = wp_get_post_terms($post->ID, 'tlms_faculty', array('fields' => 'ids'));
        $selected_department = wp_get_post_terms($post->ID, 'tlms_department', array('fields' => 'ids'));

        wp_nonce_field('tlms_academic_save', 'tlms_academic_nonce');

        ?>
        <p><strong><?php _e('University', 'tutor-lms-academic-pro'); ?></strong></p>
        <?php
        wp_dropdown_categories(array(
            'taxonomy'       => 'tlms_university',
            'name'           => 'tlms_university',
            'show_option_none' => __('Select University', 'tutor-lms-academic-pro'),
            'hide_empty'     => false,
            'selected'       => $selected_university ? $selected_university[0] : ''
        ));
        ?>

        <p><strong><?php _e('Faculty', 'tutor-lms-academic-pro'); ?></strong></p>
        <?php
        wp_dropdown_categories(array(
            'taxonomy'       => 'tlms_faculty',
            'name'           => 'tlms_faculty',
            'show_option_none' => __('Select Faculty', 'tutor-lms-academic-pro'),
            'hide_empty'     => false,
            'selected'       => $selected_faculty ? $selected_faculty[0] : ''
        ));
        ?>

        <p><strong><?php _e('Department', 'tutor-lms-academic-pro'); ?></strong></p>
        <?php
        wp_dropdown_categories(array(
            'taxonomy'       => 'tlms_department',
            'name'           => 'tlms_department',
            'show_option_none' => __('Select Department', 'tutor-lms-academic-pro'),
            'hide_empty'     => false,
            'selected'       => $selected_department ? $selected_department[0] : ''
        ));
        ?>

        <p class="description"><?php _e('Select the academic hierarchy for this course.', 'tutor-lms-academic-pro'); ?></p>
        <?php
    }

    /**
     * Save Fields
     */
    public function save_academic_fields($post_id) {

        if (!isset($_POST['tlms_academic_nonce']) || !wp_verify_nonce($_POST['tlms_academic_nonce'], 'tlms_academic_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = array(
            'tlms_university' => 'tlms_university',
            'tlms_faculty'    => 'tlms_faculty',
            'tlms_department' => 'tlms_department'
        );

        foreach ($fields as $field => $taxonomy) {
            if (isset($_POST[$field]) && $_POST[$field] != '') {
                wp_set_post_terms($post_id, intval($_POST[$field]), $taxonomy, false);
            } else {
                wp_set_post_terms($post_id, array(), $taxonomy, false);
            }
        }
    }
}

new TLMS_Custom_Taxonomy_Extend();
