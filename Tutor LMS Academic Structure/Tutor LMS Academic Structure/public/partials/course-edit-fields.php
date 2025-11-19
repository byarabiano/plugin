<?php
if (!defined('ABSPATH')) exit;

global $post;
$course_id = $post->ID;

// قراءة بيانات الكورس المخزنة مسبقًا
$education_type = get_post_meta($course_id, '_tlms_course_education_type', true);
$university = get_post_meta($course_id, '_tlms_course_university', true);
$faculty = get_post_meta($course_id, '_tlms_course_faculty', true);
$department = get_post_meta($course_id, '_tlms_course_department', true);

$school_level = get_post_meta($course_id, '_tlms_course_school_level', true);
$school_grade = get_post_meta($course_id, '_tlms_course_school_grade', true);

$general_parent = get_post_meta($course_id, '_tlms_course_general_parent', true);
$general_child = get_post_meta($course_id, '_tlms_course_general_child', true);
?>

<style>
.tlms-row { display:flex; gap:12px; margin-top:12px; flex-wrap:wrap; }
.tlms-row select { min-width:180px; padding:6px 8px; border-radius:4px; border:1px solid #ccc; }
.tlms-type-btns { display:flex; gap:10px; margin-bottom:14px; }
.tlms-type-btns button { border:1px solid #2271b1; padding:6px 12px; cursor:pointer; background:#fff; border-radius:4px; }
.tlms-type-btns button.active { background:#2271b1; color:#fff; }
</style>

<h4>الإعدادات الأكاديمية للكورس</h4>

<div class="tlms-type-btns">
    <button type="button" class="tlms-type <?php echo ($education_type=='university')?'active':''; ?>" data-type="university">جامعة</button>
    <button type="button" class="tlms-type <?php echo ($education_type=='school')?'active':''; ?>" data-type="school">مدرسة</button>
    <button type="button" class="tlms-type <?php echo ($education_type=='general')?'active':''; ?>" data-type="general">كورسات عامة</button>
</div>

<input type="hidden" name="tlms_course_education_type" id="tlms_course_education_type" value="<?php echo esc_attr($education_type); ?>">

<!-- جامعة -->
<div id="tlms_university_box" style="display:none;">
    <div class="tlms-row">
        <select name="tlms_course_university" id="tlms_course_university">
            <option value="">اختر الجامعة</option>
            <?php foreach (get_terms(['taxonomy'=>'tlms_university','hide_empty'=>false]) as $u): ?>
            <option value="<?php echo $u->term_id; ?>" <?php selected($university, $u->term_id); ?>><?php echo $u->name; ?></option>
            <?php endforeach; ?>
        </select>

        <select name="tlms_course_faculty" id="tlms_course_faculty">
            <option value="">اختر الكلية</option>
            <?php foreach (get_terms(['taxonomy'=>'tlms_faculty','hide_empty'=>false]) as $f): ?>
            <option value="<?php echo $f->term_id; ?>" data-parent="<?php echo $f->parent; ?>" <?php selected($faculty, $f->term_id); ?>><?php echo $f->name; ?></option>
            <?php endforeach; ?>
        </select>

        <select name="tlms_course_department" id="tlms_course_department">
            <option value="">اختر القسم</option>
            <?php foreach (get_terms(['taxonomy'=>'tlms_department','hide_empty'=>false]) as $d): ?>
            <option value="<?php echo $d->term_id; ?>" data-parent="<?php echo $d->parent; ?>" <?php selected($department, $d->term_id); ?>><?php echo $d->name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- مدرسة -->
<div id="tlms_school_box" style="display:none;">
    <div class="tlms-row">
        <select name="tlms_course_school_level" id="tlms_course_school_level">
            <option value="">اختر المرحلة</option>
            <?php foreach (get_terms(['taxonomy'=>'tlms_school_level','hide_empty'=>false]) as $l): ?>
            <option value="<?php echo $l->term_id; ?>" <?php selected($school_level, $l->term_id); ?>><?php echo $l->name; ?></option>
            <?php endforeach; ?>
        </select>

        <select name="tlms_course_school_grade" id="tlms_course_school_grade">
            <option value="">اختر الصف</option>
            <?php foreach (get_terms(['taxonomy'=>'tlms_school_grade','hide_empty'=>false]) as $g): ?>
            <option value="<?php echo $g->term_id; ?>" data-parent="<?php echo $g->parent; ?>" <?php selected($school_grade, $g->term_id); ?>><?php echo $g->name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- كورسات عامة -->
<div id="tlms_general_box" style="display:none;">
    <div class="tlms-row">
        <select name="tlms_course_general_parent" id="tlms_course_general_parent">
            <option value="">تصنيف رئيسي</option>
            <?php foreach (get_terms(['taxonomy'=>'tlms_general_category','parent'=>0,'hide_empty'=>false]) as $pm): ?>
            <option value="<?php echo $pm->term_id; ?>" <?php selected($general_parent, $pm->term_id); ?>><?php echo $pm->name; ?></option>
            <?php endforeach; ?>
        </select>

        <select name="tlms_course_general_child" id="tlms_course_general_child">
            <option value="">تصنيف فرعي</option>
            <?php foreach (get_terms(['taxonomy'=>'tlms_general_category','hide_empty'=>false]) as $sm): ?>
            <option value="<?php echo $sm->term_id; ?>" data-parent="<?php echo $sm->parent; ?>" <?php selected($general_child, $sm->term_id); ?>><?php echo $sm->name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<script>
(function($){

function showType(type){
    $("#tlms_course_education_type").val(type);
    $("#tlms_university_box, #tlms_school_box, #tlms_general_box").hide();
    if(type==="university") $("#tlms_university_box").show();
    if(type==="school") $("#tlms_school_box").show();
    if(type==="general") $("#tlms_general_box").show();
}

$(".tlms-type").click(function(){
    $(".tlms-type").removeClass("active");
    $(this).addClass("active");
    showType($(this).data("type"));
});

$("#tlms_course_university").change(function(){
    var u=$(this).val();
    $("#tlms_course_faculty option").each(function(){
        $(this).toggle($(this).data("parent")==u||$(this).val()=="");
    });
});

$("#tlms_course_faculty").change(function(){
    var f=$(this).val();
    $("#tlms_course_department option").each(function(){
        $(this).toggle($(this).data("parent")==f||$(this).val()=="");
    });
});

showType("<?php echo $education_type; ?>");

})(jQuery);
</script>
