<?php
if (!defined('ABSPATH')) exit;

$education_type = isset($_POST['tlms_education_type']) ? sanitize_text_field($_POST['tlms_education_type']) : '';
?>

<style>
.tlms-switch-buttons { display:flex; gap:12px; margin-bottom:18px; }
.tlms-switch-buttons button {
    padding:8px 14px;
    border:1px solid #2271b1;
    background:#fff;
    cursor:pointer;
    border-radius:4px;
    font-weight:600;
}
.tlms-switch-buttons button.active {
    background:#2271b1;
    color:#fff;
}
.tlms-inline-fields { display:flex; gap:12px; margin-top:12px; flex-wrap:wrap; }
.tlms-inline-fields select {
    min-width:160px; padding:6px 8px; border-radius:4px; border:1px solid #ccc;
}
</style>

<h3 class="tutor-form-heading">المسار الأكاديمي</h3>

<div class="tlms-switch-buttons">
    <button type="button" class="tlms-choose-type <?php echo ($education_type=='university')?'active':''; ?>" data-type="university">جامعة</button>
    <button type="button" class="tlms-choose-type <?php echo ($education_type=='school')?'active':''; ?>" data-type="school">مدرسة</button>
    <button type="button" class="tlms-choose-type <?php echo ($education_type=='general')?'active':''; ?>" data-type="general">كورسات عامة</button>
</div>

<input type="hidden" name="tlms_education_type" id="tlms_education_type" value="<?php echo esc_attr($education_type); ?>">

<!-- حقول الجامعات -->
<div id="tlms_university_fields" style="display:none;">
    <div class="tlms-inline-fields">
        <select name="tlms_university" id="tlms_university_select">
            <option value="">اختر الجامعة</option>
            <?php
            $universities = get_terms(['taxonomy'=>'tlms_university','hide_empty'=>false]);
            foreach ($universities as $u) echo "<option value='{$u->term_id}'>{$u->name}</option>";
            ?>
        </select>

        <select name="tlms_faculty" id="tlms_faculty_select">
            <option value="">اختر الكلية</option>
            <?php
            $faculties = get_terms(['taxonomy'=>'tlms_faculty','hide_empty'=>false]);
            foreach ($faculties as $f) echo "<option value='{$f->term_id}' data-parent='{$f->parent}'>{$f->name}</option>";
            ?>
        </select>

        <select name="tlms_department" id="tlms_department_select">
            <option value="">اختر القسم</option>
            <?php
            $departments = get_terms(['taxonomy'=>'tlms_department','hide_empty'=>false]);
            foreach ($departments as $d) echo "<option value='{$d->term_id}' data-parent='{$d->parent}'>{$d->name}</option>";
            ?>
        </select>
    </div>
</div>

<!-- حقول المدارس -->
<div id="tlms_school_fields" style="display:none;">
    <div class="tlms-inline-fields">
        <select name="tlms_school_level" id="tlms_school_level_select">
            <option value="">اختر المرحلة</option>
            <?php
            $levels = get_terms(['taxonomy'=>'tlms_school_level','hide_empty'=>false]);
            foreach ($levels as $l) echo "<option value='{$l->term_id}'>{$l->name}</option>";
            ?>
        </select>

        <select name="tlms_school_grade" id="tlms_school_grade_select">
            <option value="">اختر الصف</option>
            <?php
            $grades = get_terms(['taxonomy'=>'tlms_school_grade','hide_empty'=>false]);
            foreach ($grades as $g) echo "<option value='{$g->term_id}' data-parent='{$g->parent}'>{$g->name}</option>";
            ?>
        </select>
    </div>
</div>

<!-- كورسات عامة - لا يوجد حقول إضافية -->
<div id="tlms_general_fields" style="display:none;">
    <p>سيتمكن هذا الطالب من رؤية الكورسات العامة فقط.</p>
</div>

<script>
(function($){

function updateVisibility(type){
    $("#tlms_education_type").val(type);
    $("#tlms_university_fields, #tlms_school_fields, #tlms_general_fields").hide();
    if(type === "university") $("#tlms_university_fields").show();
    if(type === "school") $("#tlms_school_fields").show();
    if(type === "general") $("#tlms_general_fields").show();
}

$(".tlms-choose-type").click(function(){
    $(".tlms-choose-type").removeClass("active");
    $(this).addClass("active");
    updateVisibility($(this).data("type"));
});

$("#tlms_university_select").change(function(){
    var u = $(this).val();
    $("#tlms_faculty_select option").each(function(){
        var parent = $(this).data("parent");
        $(this).toggle(parent == u || $(this).val() == "");
    });
    $("#tlms_faculty_select").val("");
    $("#tlms_department_select").val("");
});

$("#tlms_faculty_select").change(function(){
    var f = $(this).val();
    $("#tlms_department_select option").each(function(){
        var parent = $(this).data("parent");
        $(this).toggle(parent == f || $(this).val() == "");
    });
});

$("#tlms_school_level_select").change(function(){
    var l = $(this).val();
    $("#tlms_school_grade_select option").each(function(){
        var parent = $(this).data("parent");
        $(this).toggle(parent == l || $(this).val() == "");
    });
});

updateVisibility($("#tlms_education_type").val());

})(jQuery);
</script>
