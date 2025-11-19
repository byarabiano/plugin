<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| جلب المراحل الدراسية + الصفوف التابعة لها
|--------------------------------------------------------------------------
*/

$school_levels = get_terms([
    'taxonomy'   => 'tlms_school_level',
    'hide_empty' => false
]);

$school_grades = get_terms([
    'taxonomy'   => 'tlms_school_grade',
    'hide_empty' => false
]);

/*
|--------------------------------------------------------------------------
| معالجة الإضافة
|--------------------------------------------------------------------------
*/

if (isset($_POST['tlms_add_school_level'])) {
    wp_insert_term(sanitize_text_field($_POST['tlms_school_level_name']), 'tlms_school_level');
    echo '<div class="updated"><p>تم إضافة المرحلة الدراسية.</p></div>';
}

if (isset($_POST['tlms_add_school_grade'])) {
    wp_insert_term(
        sanitize_text_field($_POST['tlms_school_grade_name']),
        'tlms_school_grade',
        ['parent' => intval($_POST['tlms_parent_level'])]
    );
    echo '<div class="updated"><p>تم إضافة الصف الدراسي.</p></div>';
}

/*
|--------------------------------------------------------------------------
| تصدير CSV
|--------------------------------------------------------------------------
*/
$export_url = admin_url('admin-post.php?action=tlms_export_academic_csv&type=schools');

?>

<style>
.tlms-table th, .tlms-table td { padding: 6px 10px; border-bottom: 1px solid #ddd; }
.tlms-table tr:hover { background: #fafafa; }
</style>

<h2>إدارة المدارس</h2>

<!-- إضافة مرحلة -->
<form method="post" style="margin-top:20px;">
    <h3>إضافة مرحلة دراسية</h3>
    <input type="text" name="tlms_school_level_name" placeholder="مثال: ابتدائي / إعدادي / ثانوي" required>
    <button class="button button-primary" name="tlms_add_school_level">إضافة</button>
</form>

<!-- إضافة صف -->
<form method="post" style="margin-top:20px;">
    <h3>إضافة صف دراسي</h3>

    <select name="tlms_parent_level" required>
        <option value="">اختر المرحلة الدراسية</option>
        <?php foreach ($school_levels as $l): ?>
            <option value="<?php echo $l->term_id; ?>"><?php echo $l->name; ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="tlms_school_grade_name" placeholder="مثال: الصف الأول / الصف الثاني ..." required>
    <button class="button button-primary" name="tlms_add_school_grade">إضافة</button>
</form>

<hr>

<!-- زر تصدير -->
<a href="<?php echo $export_url; ?>" class="button button-secondary">تصدير CSV</a>

<!-- زر استيراد -->
<form action="<?php echo admin_url('admin-post.php?action=tlms_import_academic_csv'); ?>" method="post" enctype="multipart/form-data" style="margin-top:10px;">
    <input type="hidden" name="tlms_import_type" value="schools">
    <input type="file" name="tlms_csv_file" accept=".csv" required>
    <button class="button button-primary">استيراد CSV</button>
</form>

<hr>

<h3>الهيكل الحالي</h3>

<table class="widefat tlms-table">
<thead><tr><th>المرحلة الدراسية</th><th>الصف الدراسي</th></tr></thead>
<tbody>

<?php foreach ($school_levels as $l): ?>
    <?php foreach ($school_grades as $g): if ($g->parent != $l->term_id) continue; ?>
        <tr>
            <td><?php echo $l->name; ?></td>
            <td><?php echo $g->name; ?></td>
        </tr>
    <?php endforeach; ?>
<?php endforeach; ?>

</tbody>
</table>
