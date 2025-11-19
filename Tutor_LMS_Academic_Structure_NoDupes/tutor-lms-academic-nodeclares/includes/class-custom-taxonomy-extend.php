<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TLMS_Custom_Taxonomy_Extend {

    private $taxonomy = 'tlms_academic_category';

    public function __construct() {
        add_action('tlms_academic_category_add_form_fields', array($this, 'add_meta_field'));
        add_action('tlms_academic_category_edit_form_fields', array($this, 'edit_meta_field'));
        add_action('created_tlms_academic_category', array($this, 'save_meta'), 10, 2);
        add_action('edited_tlms_academic_category', array($this, 'save_meta'), 10, 2);
    }

    public function add_meta_field() { ?>
        <div class="form-field">
            <label for="academic_type"><?php _e('Academic Type', 'tutor-lms-academic-pro'); ?></label>
            <select name="academic_type" id="academic_type">
                <option value=""><?php _e('Select Type', 'tutor-lms-academic-pro'); ?></option>
                <option value="university"><?php _e('University', 'tutor-lms-academic-pro'); ?></option>
                <option value="faculty"><?php _e('Faculty', 'tutor-lms-academic-pro'); ?></option>
                <option value="department"><?php _e('Department', 'tutor-lms-academic-pro'); ?></option>
            </select>
            <p class="description"><?php _e('Select whether this category is a University, Faculty, or Department.', 'tutor-lms-academic-pro'); ?></p>
        </div>
    <?php }

    public function edit_meta_field($term) {
        $value = get_term_meta($term->term_id, 'academic_type', true); ?>
        <tr class="form-field">
            <th scope="row"><label for="academic_type"><?php _e('Academic Type', 'tutor-lms-academic-pro'); ?></label></th>
            <td>
                <select name="academic_type" id="academic_type">
                    <option value=""><?php _e('Select Type', 'tutor-lms-academic-pro'); ?></option>
                    <option value="university" <?php selected($value, 'university'); ?>><?php _e('University', 'tutor-lms-academic-pro'); ?></option>
                    <option value="faculty" <?php selected($value, 'faculty'); ?>><?php _e('Faculty', 'tutor-lms-academic-pro'); ?></option>
                    <option value="department" <?php selected($value, 'department'); ?>><?php _e('Department', 'tutor-lms-academic-pro'); ?></option>
                </select>
                <p class="description"><?php _e('Select category type.', 'tutor-lms-academic-pro'); ?></p>
            </td>
        </tr>
    <?php }

    public function save_meta($term_id) {
        if (isset($_POST['academic_type'])) {
            update_term_meta($term_id, 'academic_type', sanitize_text_field($_POST['academic_type']));
        }
    }

}

new TLMS_Custom_Taxonomy_Extend();
