<?php
if (!defined('ABSPATH')) exit;

global $post;
$course_id = $post->ID;

// Read meta
$education_type = get_post_meta($course_id, '_tlms_education_type', true);
$university     = get_post_meta($course_id, '_tlms_university', true);
$faculty        = get_post_meta($course_id, '_tlms_faculty', true);
$department     = get_post_meta($course_id, '_tlms_department', true);

// Nothing to display if course is general
if (!$education_type || $education_type === 'general') return;

$labels = [
    'university' => __('University', 'tutor-lms-academic-pro'),
    'faculty'    => __('Faculty', 'tutor-lms-academic-pro'),
    'department' => __('Department', 'tutor-lms-academic-pro'),
];

$uni_name  = $university ? get_term($university)->name : '';
$fac_name  = $faculty ? get_term($faculty)->name : '';
$dep_name  = $department ? get_term($department)->name : '';
?>

<style>
.tlms-academic-box {
    border:1px solid #e5e5e5;
    background:#fafafa;
    padding:12px 15px;
    border-radius:6px;
    margin-top:18px;
}
.tlms-academic-box h4 { margin:0 0 10px 0; font-size:16px; font-weight:600; }
.tlms-academic-line { margin-bottom:6px; font-size:14px; }
.tlms-academic-line span { font-weight:600; color:#333; }
</style>

<div class="tlms-academic-box">
    <h4><?php _e('Academic Details', 'tutor-lms-academic-pro'); ?></h4>

    <?php if ($uni_name): ?>
        <div class="tlms-academic-line">
            <span><?php echo $labels['university']; ?>:</span> <?php echo esc_html($uni_name); ?>
        </div>
    <?php endif; ?>

    <?php if ($fac_name): ?>
        <div class="tlms-academic-line">
            <span><?php echo $labels['faculty']; ?>:</span> <?php echo esc_html($fac_name); ?>
        </div>
    <?php endif; ?>

    <?php if ($dep_name): ?>
        <div class="tlms-academic-line">
            <span><?php echo $labels['department']; ?>:</span> <?php echo esc_html($dep_name); ?>
        </div>
    <?php endif; ?>
</div>
