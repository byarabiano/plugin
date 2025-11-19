<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| جلب جميع الجامعات + كلياتها + أقسامها
|--------------------------------------------------------------------------
*/

$universities = get_terms([
    'taxonomy'   => 'tlms_university',
    'hide_empty' => false
]);

$faculties = get_terms([
    'taxonomy'   => 'tlms_faculty',
    'hide_empty' => false
]);

$departments = get_terms([
    'taxonomy'   => 'tlms_department',
    'hide_empty' => false
]);

/*
|--------------------------------------------------------------------------
| معالجة الإضافة
|--------------------------------------------------------------------------
*/

if (isset($_POST['tlms_add_university'])) {
    wp_insert_term(sanitize_text_field($_POST['tlms_university_name']), 'tlms_university');
    echo '<div class="updated"><p>تم إضافة الجامعة.</p></div>';
}

if (isset($_POST['tlms_add_faculty'])) {
    wp_insert_term(
        sanitize_text_field($_POST['tlms_faculty_name']),
        'tlms_faculty',
        ['parent' => intval($_POST['tlms_parent_university'])]
    );
    echo '<div class="updated"><p>تم إضافة الكلية.</p></div>';
}

if (isset($_POST['tlms_add_department'])) {
    wp_insert_term(
        sanitize_text_field($_POST['tlms_department_name']),
        'tlms_department',
        ['parent' => intval($_POST['tlms_parent_faculty'])]
    );
    echo '<div class="updated"><p>تم إضافة القسم.</p></div>';
}

/*
|--------------------------------------------------------------------------
| تصدير CSV
|--------------------------------------------------------------------------
*/
$export_url = admin_url('admin-post.php?action=tlms_export_academic_csv&type=university');

?>

<style>
.tlms-table th, .tlms-table td { padding: 6px 10px; border-bottom: 1px solid #ddd; }
.tlms-table tr:hover { background: #fafafa; }
</style>

<h2>إدارة الجامعات</h2>

<!-- إضافة جامعة -->
<form method="post" style="margin-top:20px;">
    <h3>إضافة جامعة</h3>
    <input type="text" name="tlms_university_name" placeholder="اسم الجامعة" required>
    <button class="button button-primary" name="tlms_add_university">إضافة</button>
</form>

<!-- إضافة كلية -->
<form method="post" style="margin-top:20px;">
    <h3>إضافة كلية</h3>
    <select name="tlms_parent_university" required>
        <option value="">اختر الجامعة</option>
        <?php foreach ($universities as $u): ?>
            <option value="<?php echo $u->term_id; ?>"><?php echo $u->name; ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="tlms_faculty_name" placeholder="اسم الكلية" required>
    <button class="button button-primary" name="tlms_add_faculty">إضافة</button>
</form>

<!-- إضافة قسم -->
<form method="post" style="margin-top:20px;">
    <h3>إضافة قسم</h3>

    <select id="tlms_parent_university_selector" onchange="tlmsUpdateFacultyDropdown()">
        <option value="">اختر الجامعة</option>
        <?php foreach ($universities as $u): ?>
            <option value="<?php echo $u->term_id; ?>"><?php echo $u->name; ?></option>
        <?php endforeach; ?>
    </select>

    <select name="tlms_parent_faculty" id="tlms_faculty_dropdown" required>
        <option value="">اختر الكلية</option>
        <?php foreach ($faculties as $f): ?>
            <option value="<?php echo $f->term_id; ?>" data-parent="<?php echo $f->parent; ?>">
                <?php echo $f->name; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="tlms_department_name" placeholder="اسم القسم" required>
    <button class="button button-primary" name="tlms_add_department">إضافة</button>
</form>

<hr>

<!-- زر تصدير -->
<a href="<?php echo $export_url; ?>" class="button button-secondary">تصدير CSV</a>

<!-- زر استيراد -->
<form action="<?php echo admin_url('admin-post.php?action=tlms_import_academic_csv'); ?>" method="post" enctype="multipart/form-data" style="margin-top:10px;">
    <input type="hidden" name="tlms_import_type" value="university">
    <input type="file" name="tlms_csv_file" accept=".csv" required>
    <button class="button button-primary">استيراد CSV</button>
</form>

<hr>

<h3>الهيكل الحالي</h3>

<table class="widefat tlms-table">
<thead><tr><th>الجامعة</th><th>الكلية</th><th>القسم</th></tr></thead>
<tbody>

<?php foreach ($universities as $u): ?>
    <?php foreach ($faculties as $f): if ($f->parent != $u->term_id) continue; ?>
        <?php foreach ($departments as $d): if ($d->parent != $f->term_id) continue; ?>
            <tr>
                <td><?php echo $u->name; ?></td>
                <td><?php echo $f->name; ?></td>
                <td><?php echo $d->name; ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endforeach; ?>

</tbody>
</table>

<script>
function tlmsUpdateFacultyDropdown() {
    var u = document.getElementById('tlms_parent_university_selector').value;
    var f = document.getElementById('tlms_faculty_dropdown').options;
    for (var i = 0; i < f.length; i++) {
        var parent = f[i].getAttribute('data-parent');
        f[i].style.display = (parent == u || f[i].value === '') ? '' : 'none';
    }
}
</script>
