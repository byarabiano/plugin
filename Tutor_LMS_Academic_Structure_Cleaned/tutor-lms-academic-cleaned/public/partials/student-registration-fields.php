<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$selected_type = $_POST['tlms_education_type'] ?? '';
?>

<style>
.tlms-registration-row { display:flex; gap:12px; margin-top:12px; flex-wrap:wrap; }
.tlms-registration-row select { padding:6px 8px; border-radius:4px; border:1px solid #ccc; min-width:160px; }
</style>

<div class="tutor-form-group">
    <label><?php _e('Education Type', 'tutor-lms-academic-pro'); ?></label>
    <select name="tlms_education_type" id="tlms_education_type" required>
        <option value=""><?php _e('Select Education Type', 'tutor-lms-academic-pro'); ?></option>
        <option value="general"><?php _e('General', 'tutor-lms-academic-pro'); ?></option>
        <option value="school"><?php _e('School', 'tutor-lms-academic-pro'); ?></option>
        <option value="university"><?php _e('University', 'tutor-lms-academic-pro'); ?></option>
    </select>
</div>

<!-- University Fields -->
<div id="tlms_university_fields" style="display:none;">
    <label><strong><?php _e('Academic Path (University):', 'tutor-lms-academic-pro'); ?></strong></label>
    <div class="tlms-registration-row">
        <?php wp_dropdown_categories([
            'taxonomy' => 'tlms_universities',
            'name' => 'tlms_universities',
            'show_option_none' => __('Select University','tutor-lms-academic-pro'),
            'hide_empty' => false
        ]); ?>

        <?php wp_dropdown_categories([
            'taxonomy' => 'tlms_faculties',
            'name' => 'tlms_faculties',
            'show_option_none' => __('Select Faculty','tutor-lms-academic-pro'),
            'hide_empty' => false
        ]); ?>

        <?php wp_dropdown_categories([
            'taxonomy' => 'tlms_departments',
            'name' => 'tlms_departments',
            'show_option_none' => __('Select Department','tutor-lms-academic-pro'),
            'hide_empty' => false
        ]); ?>
    </div>
</div>

<!-- School Fields -->
<div id="tlms_school_fields" style="display:none;">
    <label><strong><?php _e('Academic Path (School):', 'tutor-lms-academic-pro'); ?></strong></label>
    <div class="tlms-registration-row">
        <?php wp_dropdown_categories([
            'taxonomy' => 'tlms_schools',
            'name' => 'tlms_schools',
            'show_option_none' => __('School Type','tutor-lms-academic-pro'),
            'hide_empty' => false
        ]); ?>

        <?php wp_dropdown_categories([
            'taxonomy' => 'tlms_grades',
            'name' => 'tlms_grades',
            'show_option_none' => __('Grade Level','tutor-lms-academic-pro'),
            'hide_empty' => false
        ]); ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var type = document.getElementById('tlms_education_type');
    var uni = document.getElementById('tlms_university_fields');
    var sch = document.getElementById('tlms_school_fields');

    function toggle() {
        uni.style.display = (type.value === 'university') ? 'block' : 'none';
        sch.style.display = (type.value === 'school') ? 'block' : 'none';
    }

    type.addEventListener('change', toggle);
    toggle();
});
</script>
