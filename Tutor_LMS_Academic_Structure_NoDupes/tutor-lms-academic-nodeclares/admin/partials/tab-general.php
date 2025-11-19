<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| جلب التصنيفات العامة + الفئات الفرعية
|--------------------------------------------------------------------------
*/

$general_main = get_terms([
    'taxonomy'   => 'tlms_general_category',
    'parent'     => 0,
    'hide_empty' => false
]);

$general_sub = get_terms([
    'taxonomy'   => 'tlms_general_category',
    'hide_empty' => false
]);

/*
|--------------------------------------------------------------------------
| معالجة الإضافة
|--------------------------------------------------------------------------
*/

if (isset($_POST['tlms_add_general_parent'])) {
    wp_insert_term(sanitize_text_field($_POST['tlms_general_parent_name']), 'tlms_general_category');
    echo '<div class="updated"><p>تم إضافة تصنيف رئيسي.</p></div>';
}

if (isset($_POST['tlms_add_general_child'])) {
    wp_insert_term(
        sanitize_text_field($_POST['tlms_general_child_name']),
        'tlms_general_category',
        ['parent' => intval($_POST['tlms_parent_general'])]
    );
    echo '<div class="updated"><p>تم إضافة تصنيف فرعي.</p></div>';
}

/*
|--------------------------------------------------------------------------
| تصدير CSV
|--------------------------------------------------------------------------
*/
$export_url = admin_url('admin-post.php?action=tlms_export_academic_csv&type=general');

?>

<style>
.tlms-table th, .tlms-table td { padding: 6px 10px; border-bottom: 1px solid #ddd; }
.tlms-table tr:hover { background: #fafafa; }
</style>

<h2>إدارة كورسات عامة</h2>

<!-- إضافة تصنيف رئيسي -->
<form method="post" style="margin-top:20px;">
    <h3>إضافة تصنيف رئيسي</h3>
    <input type="text" name="tlms_general_parent_name" placeholder="مثال: مهارات / تنمية / لغات" required>
    <button class="button button-primary" name="tlms_add_general_parent">إضافة</button>
</form>

<!-- إضافة تصنيف فرعي -->
<form method="post" style="margin-top:20px;">
    <h3>إضافة تصنيف فرعي</h3>

    <select name="tlms_parent_general" required>
        <option value="">اختر التصنيف الرئيسي</option>
        <?php foreach ($general_main as $pm): ?>
            <option value="<?php echo $pm->term_id; ?>"><?php echo $pm->name; ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="tlms_general_child_name" placeholder="مثال: Excel / لغة عربية / تطوير ذات" required>
    <button class="button button-primary" name="tlms_add_general_child">إضافة</button>
</form>

<hr>

<!-- زر تصدير -->
<a href="<?php echo $export_url; ?>" class="button button-secondary">تصدير CSV</a>

<!-- زر استيراد -->
<form action="<?php echo admin_url('admin-post.php?action=tlms_import_academic_csv'); ?>" method="post" enctype="multipart/form-data" style="margin-top:10px;">
    <input type="hidden" name="tlms_import_type" value="general">
    <input type="file" name="tlms_csv_file" accept=".csv" required>
    <button class="button button-primary">استيراد CSV</button>
</form>

<hr>

<h3>الهيكل الحالي</h3>

<table class="widefat tlms-table">
<thead><tr><th>تصنيف رئيسي</th><th>تصنيف فرعي</th></tr></thead>
<tbody>

<?php foreach ($general_main as $pm): ?>
    <?php foreach ($general_sub as $sm): if ($sm->parent != $pm->term_id) continue; ?>
        <tr>
            <td><?php echo $pm->name; ?></td>
            <td><?php echo $sm->name; ?></td>
        </tr>
    <?php endforeach; ?>
<?php endforeach; ?>

</tbody>
</table>
