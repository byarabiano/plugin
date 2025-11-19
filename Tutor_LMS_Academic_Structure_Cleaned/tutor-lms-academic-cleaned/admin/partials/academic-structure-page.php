<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$universities = get_terms(array(
    'taxonomy' => 'tlms_university',
    'hide_empty' => false
));

$faculties = get_terms(array(
    'taxonomy' => 'tlms_faculty',
    'hide_empty' => false
));

$departments = get_terms(array(
    'taxonomy' => 'tlms_department',
    'hide_empty' => false
));
?>

<style>
.tlms-tabs { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 15px; }
.tlms-tab { padding: 10px 18px; cursor: pointer; border: 1px solid #ddd; border-bottom: none; margin-right: 5px; background: #f7f7f7; }
.tlms-tab.active { background: #fff; font-weight: bold; border-bottom: 2px solid #fff; }
.tlms-section { display: none; }
.tlms-section.active { display: block; }
</style>

<div class="wrap">
    <h1>Academic Structure</h1>

    <div class="tlms-tabs">
        <div class="tlms-tab active" data-target="universities">Universities</div>
        <div class="tlms-tab" data-target="faculties">Faculties</div>
        <div class="tlms-tab" data-target="departments">Departments</div>
    </div>

    <!-- Universities -->
    <div id="universities" class="tlms-section active">
        <h2>Universities</h2>
        <a href="<?php echo admin_url('edit-tags.php?taxonomy=tlms_university&post_type=tutor_course'); ?>" class="button button-primary">Manage Universities</a>
    </div>

    <!-- Faculties -->
    <div id="faculties" class="tlms-section">
        <h2>Faculties</h2>
        <a href="<?php echo admin_url('edit-tags.php?taxonomy=tlms_faculty&post_type=tutor_course'); ?>" class="button button-primary">Manage Faculties</a>
    </div>

    <!-- Departments -->
    <div id="departments" class="tlms-section">
        <h2>Departments</h2>
        <a href="<?php echo admin_url('edit-tags.php?taxonomy=tlms_department&post_type=tutor_course'); ?>" class="button button-primary">Manage Departments</a>
    </div>
</div>

<script>
document.querySelectorAll('.tlms-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.tlms-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        const target = this.getAttribute('data-target');
        document.querySelectorAll('.tlms-section').forEach(sec => sec.classList.remove('active'));
        document.getElementById(target).classList.add('active');
    });
});
</script>
