<?php
if (!defined('ABSPATH')) exit;

class TLMS_Academic_Category_Mapping {

    public function __construct() {
        // Add fields to category edit page
        add_action('tlms_general_category_add_form_fields', array($this, 'add_mapping_fields'));
        add_action('tlms_general_category_edit_form_fields', array($this, 'edit_mapping_fields'), 10, 2);

        // Save when adding category
        add_action('created_tlms_general_category', array($this, 'save_mapping_fields'), 10, 2);
        add_action('edited_tlms_general_category', array($this, 'save_mapping_fields'), 10, 2);

        // AJAX Dynamic Dropdown
        add_action('wp_ajax_tlms_get_faculties', array($this, 'load_faculties'));
        add_action('wp_ajax_tlms_get_departments', array($this, 'load_departments'));
    }

    /**
     * Add fields when creating a new Course Category
     */
    public function add_mapping_fields() {
        ?>
        <div class="form-field">
            <label for="tlms_university"><?php _e('University', 'tutor-lms-academic-pro'); ?></label>
            <?php $this->dropdown('tlms_university'); ?>
        </div>

        <div class="form-field">
            <label for="tlms_faculty"><?php _e('Faculty', 'tutor-lms-academic-pro'); ?></label>
            <?php $this->dropdown('tlms_faculty'); ?>
        </div>

        <div class="form-field">
            <label for="tlms_department"><?php _e('Department', 'tutor-lms-academic-pro'); ?></label>
            <?php $this->dropdown('tlms_department'); ?>
        </div>
        <?php
    }

    /**
     * Edit existing category
     */
    public function edit_mapping_fields($term) {
        $university = get_term_meta($term->term_id, 'tlms_university', true);
        $faculty = get_term_meta($term->term_id, 'tlms_faculty', true);
        $department = get_term_meta($term->term_id, 'tlms_department', true);

        ?>
        <tr class="form-field">
            <th scope="row"><label><?php _e('University', 'tutor-lms-academic-pro'); ?></label></th>
            <td><?php $this->dropdown('tlms_university', $university); ?></td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label><?php _e('Faculty', 'tutor-lms-academic-pro'); ?></label></th>
            <td><?php $this->dropdown('tlms_faculty', $faculty); ?></td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label><?php _e('Department', 'tutor-lms-academic-pro'); ?></label></th>
            <td><?php $this->dropdown('tlms_department', $department); ?></td>
        </tr>
        <?php
    }

    /**
     * Save mapping selections
     */
    public function save_mapping_fields($term_id) {
        foreach (['tlms_university', 'tlms_faculty', 'tlms_department'] as $field) {
            if (isset($_POST[$field])) {
                update_term_meta($term_id, $field, intval($_POST[$field]));
            }
        }
    }

    /**
     * Render dropdown for taxonomy
     */
    private function dropdown($taxonomy, $selected = '') {
        wp_dropdown_categories(array(
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'show_option_none' => __('— None —'),
            'hide_empty' => false,
            'selected' => $selected
        ));
    }
}

new TLMS_Academic_Category_Mapping();
