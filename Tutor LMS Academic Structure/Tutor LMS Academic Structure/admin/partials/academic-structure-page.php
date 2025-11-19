<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Academic Structure admin page partial
 * File: admin/partials/academic-structure-page.php
 *
 * يعتمد على أن يوجد handler في class-ajax-handler.php يستجيب للأكشانات:
 *  - tlms_get_academic_data
 *  - tlms_add_university, tlms_add_school_type, tlms_add_general_term (أسماء أمثلة — يجب مطابقتها مع ajax handler لديك)
 *  - tlms_delete_term
 *
 * تأكد أن AJAX handlers موجودة وتعيد JSON (wp_send_json_success).
 */

// نُحمّل المراجع الأولية (يمكن حذفه إن لم تكن ضرورية)
$universities = get_terms(array('taxonomy' => 'tlms_university', 'hide_empty' => false));
$schools      = get_terms(array('taxonomy' => 'tlms_school', 'hide_empty' => false));
$general      = get_terms(array('taxonomy' => 'tlms_general', 'hide_empty' => false));
?>

<style>
.tlms-tabs { display:flex; gap:6px; border-bottom:1px solid #ddd; margin-bottom:18px; }
.tlms-tab { padding:8px 14px; cursor:pointer; background:#f1f1f1; border:1px solid #ddd; border-bottom:none; border-radius:4px 4px 0 0; }
.tlms-tab.active { background:#fff; font-weight:600; }
.tlms-section { display:none; padding:18px; background:#fff; border:1px solid #ddd; border-top:none; border-radius:0 4px 4px 4px; }
.tlms-section.active { display:block; }
.tlms-field-row { display:flex; gap:8px; align-items:center; margin-bottom:12px; flex-wrap:wrap; }
.tlms-field-row input[type="text"], .tlms-field-row select { padding:6px 8px; border:1px solid #ccd0d4; border-radius:4px; min-width:200px; }
.tlms-list { margin-top:12px; }
.tlms-list ul { list-style: none; padding-left:10px; }
.tlms-list li { margin:6px 0; }
.tlms-actions .button { margin-left:6px; }
.tlms-empty { color:#666; }
</style>

<div class="wrap">
    <h1><?php esc_html_e('Academic Structure', 'tutor-lms-academic-pro'); ?></h1>

    <div class="tlms-tabs" role="tablist" aria-label="Academic structure tabs">
        <div class="tlms-tab active" data-target="tlms_tab_universities"><?php _e('Universities', 'tutor-lms-academic-pro'); ?></div>
        <div class="tlms-tab" data-target="tlms_tab_schools"><?php _e('Schools', 'tutor-lms-academic-pro'); ?></div>
        <div class="tlms-tab" data-target="tlms_tab_general"><?php _e('General Courses', 'tutor-lms-academic-pro'); ?></div>
    </div>

    <!-- UNIVERSITIES TAB -->
    <div id="tlms_tab_universities" class="tlms-section active" role="tabpanel">
        <h2><?php _e('Universities', 'tutor-lms-academic-pro'); ?></h2>

        <div class="tlms-field-row">
            <input type="text" id="tlms_new_university" placeholder="<?php esc_attr_e('New university name', 'tutor-lms-academic-pro'); ?>">
            <button id="tlms_add_university" class="button button-primary"><?php _e('Add', 'tutor-lms-academic-pro'); ?></button>
        </div>

        <div class="tlms-list" id="tlms_university_list">
            <p class="tlms-empty"><?php _e('Loading…', 'tutor-lms-academic-pro'); ?></p>
        </div>
    </div>

    <!-- SCHOOLS TAB -->
    <div id="tlms_tab_schools" class="tlms-section" role="tabpanel">
        <h2><?php _e('Schools', 'tutor-lms-academic-pro'); ?></h2>

        <div class="tlms-field-row">
            <select id="tlms_school_type_parent">
                <option value=""><?php _e('Select parent (optional)', 'tutor-lms-academic-pro'); ?></option>
            </select>
            <input type="text" id="tlms_new_school_type" placeholder="<?php esc_attr_e('New school type or branch', 'tutor-lms-academic-pro'); ?>">
            <button id="tlms_add_school_type" class="button button-primary"><?php _e('Add', 'tutor-lms-academic-pro'); ?></button>
        </div>

        <div class="tlms-list" id="tlms_school_list">
            <p class="tlms-empty"><?php _e('Loading…', 'tutor-lms-academic-pro'); ?></p>
        </div>
    </div>

    <!-- GENERAL COURSES TAB -->
    <div id="tlms_tab_general" class="tlms-section" role="tabpanel">
        <h2><?php _e('General Courses', 'tutor-lms-academic-pro'); ?></h2>

        <div class="tlms-field-row">
            <input type="text" id="tlms_new_general" placeholder="<?php esc_attr_e('New general course category', 'tutor-lms-academic-pro'); ?>">
            <button id="tlms_add_general" class="button button-primary"><?php _e('Add', 'tutor-lms-academic-pro'); ?></button>
        </div>

        <div class="tlms-list" id="tlms_general_list">
            <p class="tlms-empty"><?php _e('Loading…', 'tutor-lms-academic-pro'); ?></p>
        </div>
    </div>
</div>

<script>
(function(){
    // Tabs behavior
    document.querySelectorAll('.tlms-tab').forEach(function(tab){
        tab.addEventListener('click', function(){
            document.querySelectorAll('.tlms-tab').forEach(t=>t.classList.remove('active'));
            tab.classList.add('active');
            var target = tab.getAttribute('data-target');
            document.querySelectorAll('.tlms-section').forEach(s=>s.classList.remove('active'));
            document.getElementById(target).classList.add('active');
            load_data();
        });
    });

    // AJAX loader and actions using jQuery (WordPress admin exposes ajaxurl)
    function load_data() {
        if (typeof jQuery === 'undefined') return;
        jQuery.post(ajaxurl, { action: 'tlms_get_academic_data', nonce: tlms_admin_ajax ? tlms_admin_ajax.nonce : '' }, function(response){
            if (!response || !response.success) {
                // if error, show fallback
                jQuery('#tlms_university_list').html('<p class="tlms-empty"><?php echo esc_js(__('Unable to load data', 'tutor-lms-academic-pro')); ?></p>');
                jQuery('#tlms_school_list').html('<p class="tlms-empty"><?php echo esc_js(__('Unable to load data', 'tutor-lms-academic-pro')); ?></p>');
                jQuery('#tlms_general_list').html('<p class="tlms-empty"><?php echo esc_js(__('Unable to load data', 'tutor-lms-academic-pro')); ?></p>');
                return;
            }
            // Populate lists and selects
            jQuery('#tlms_university_list').html(response.data.universities_html || '<p class="tlms-empty"><?php echo esc_js(__('No universities', 'tutor-lms-academic-pro')); ?></p>');
            jQuery('#tlms_school_list').html(response.data.schools_html || '<p class="tlms-empty"><?php echo esc_js(__('No schools', 'tutor-lms-academic-pro')); ?></p>');
            jQuery('#tlms_general_list').html(response.data.general_html || '<p class="tlms-empty"><?php echo esc_js(__('No general categories', 'tutor-lms-academic-pro')); ?></p>');

            jQuery('#tlms_school_type_parent').html(response.data.university_options || '<option value=""><?php echo esc_js(__('No parent', 'tutor-lms-academic-pro')); ?></option>');
        }, 'json');
    }

    // initial load
    if (typeof jQuery !== 'undefined') {
        jQuery(function($){
            load_data();

            // Add actions
            $('#tlms_add_university').on('click', function(e){
                e.preventDefault();
                var name = $('#tlms_new_university').val();
                if (!name) return alert('<?php echo esc_js(__('Please enter a name', 'tutor-lms-academic-pro')); ?>');
                $.post(ajaxurl, { action: 'tlms_add_university', name: name, nonce: tlms_admin_ajax ? tlms_admin_ajax.nonce : '' }, function(res){
                    $('#tlms_new_university').val('');
                    load_data();
                }, 'json');
            });

            $('#tlms_add_school_type').on('click', function(e){
                e.preventDefault();
                var name = $('#tlms_new_school_type').val();
                var parent = $('#tlms_school_type_parent').val();
                if (!name) return alert('<?php echo esc_js(__('Please enter a name', 'tutor-lms-academic-pro')); ?>');
                $.post(ajaxurl, { action: 'tlms_add_school_type', name: name, parent: parent, nonce: tlms_admin_ajax ? tlms_admin_ajax.nonce : '' }, function(res){
                    $('#tlms_new_school_type').val('');
                    load_data();
                }, 'json');
            });

            $('#tlms_add_general').on('click', function(e){
                e.preventDefault();
                var name = $('#tlms_new_general').val();
                if (!name) return alert('<?php echo esc_js(__('Please enter a name', 'tutor-lms-academic-pro')); ?>');
                $.post(ajaxurl, { action: 'tlms_add_general', name: name, nonce: tlms_admin_ajax ? tlms_admin_ajax.nonce : '' }, function(res){
                    $('#tlms_new_general').val('');
                    load_data();
                }, 'json');
            });

            // Delete (delegated)
            $(document).on('click', '.tlms-delete', function(e){
                e.preventDefault();
                if (!confirm('<?php echo esc_js(__('Delete this item?', 'tutor-lms-academic-pro')); ?>')) return;
                var id = $(this).data('id');
                var tax = $(this).data('tax');
                $.post(ajaxurl, { action: 'tlms_delete_term', id: id, tax: tax, nonce: tlms_admin_ajax ? tlms_admin_ajax.nonce : '' }, function(res){
                    load_data();
                }, 'json');
            });

        });
    } else {
        // jQuery not available — still attempt to call load_data once (some admin pages load jQuery late)
        setTimeout(load_data, 500);
    }

})();
</script>
