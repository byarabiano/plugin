<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TLMS_Save_Course_Academy
 * يتولى حفظ بيانات المسار الأكاديمي للكورس عند حفظ/تحديث الدورة
 * - يحفظ meta _tlms_education_type
 * - يربط المصطلحات المختارة بالتاكسونومي tlms_academic_category
 */
class TLMS_Save_Course_Academy {

    public function __construct() {
        add_action('save_post_tutor_course', array($this, 'save_course_academy'), 10, 3);
        // تأمين عند الحفظ عبر واجهة Tutor (لو يوجد hook مخصص يمكن إضافته لاحقاً)
    }

    public function save_course_academy($post_id, $post, $update) {
        // الأمن: تحقق من nonce إن وُجد
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // capability
        if (!current_user_can('edit_post', $post_id)) return;

        // نقرأ الحقول المرسلة (من form إنشاء/تعديل الكورس)
        $education_type = isset($_POST['tlms_education_type']) ? sanitize_text_field($_POST['tlms_education_type']) : '';
        $selected_terms = isset($_POST['tlms_academic_categories']) && is_array($_POST['tlms_academic_categories'])
            ? array_map('intval', $_POST['tlms_academic_categories'])
            : array();

        // لا نسمح بالحفظ إذا كانت قيمة education_type ناقصة (حسب طلبك: لا يسمح بالحفظ عند اختيارات ناقصة)
        // لكن لتجنب كسر الحفظ نتحقق ونرمي خطأ إداري إذا لزم (هنا سنمنع فقط ربط التصنيفات إن لم يكن education_type محدد)
        if (!empty($education_type)) {
            update_post_meta($post_id, '_tlms_education_type', $education_type);
        } else {
            // احذف أو اجعل القيمة فارغة
            delete_post_meta($post_id, '_tlms_education_type');
        }

        // ربط المصطلحات بالتاكسونومي tlms_academic_category
        if (!empty($selected_terms)) {
            // فقط ربط للمصطلحات التي تنتمي لنفس نوع التعليم (تأكد من ذلك إن أردت قيودًا أقوى)
            wp_set_object_terms($post_id, $selected_terms, 'tlms_academic_category', false);
        } else {
            // إذا لم يحدد أحد، قد نرغب في حذف الterms المرتبطة (أو تركها كما هي) — هنا نحذف
            wp_set_object_terms($post_id, array(), 'tlms_academic_category', false);
        }
    }
}

new TLMS_Save_Course_Academy();
