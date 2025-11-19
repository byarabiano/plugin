<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TLMS_Save_User_Academy
 * يحفظ بيانات المسار الأكاديمي للمستخدم (عند التسجيل أو عند تحديث الملف الشخصي من قبل المشرف/الأدمن)
 * - meta: tlms_education_type
 * - meta: tlms_academic_categories (array of term IDs)
 *
 * ملاحظة: الحقول تظهر في واجهة التسجيل (public/partials/registration-fields.php) ويجب إرسال نفس أسماء الحقول
 */
class TLMS_Save_User_Academy {

    public function __construct() {
        // عند تسجيل مستخدم جديد (إذا تستخدم صفحة تسجيل مخصصة، تأكد أنها ترسل الحقول)
        add_action('user_register', array($this, 'save_user_academy_on_register'), 10, 1);

        // عند تحديث ملف المستخدم من قبل الأدمن أو من الprofile
        add_action('profile_update', array($this, 'save_user_academy_on_profile_update'), 10, 2);

        // إذا لديك AJAX تسجيل مستخدم مخصص، اربط هنا الـ handler المناسب
    }

    public function save_user_academy_on_register($user_id) {
        if (isset($_POST['tlms_education_type'])) {
            $education_type = sanitize_text_field($_POST['tlms_education_type']);
            update_user_meta($user_id, 'tlms_education_type', $education_type);
        }

        if (isset($_POST['tlms_academic_categories']) && is_array($_POST['tlms_academic_categories'])) {
            $cats = array_map('intval', $_POST['tlms_academic_categories']);
            update_user_meta($user_id, 'tlms_academic_categories', $cats);
        }
    }

    public function save_user_academy_on_profile_update($user_id, $old_user_data = null) {
        // فقط الأدمن أو المشرف يمكنه التعديل على هذه الحقول (حسب طلبك)
        if (!current_user_can('manage_options') && !current_user_can('manage_tutor')) {
            return;
        }

        if (isset($_POST['tlms_education_type'])) {
            $education_type = sanitize_text_field($_POST['tlms_education_type']);
            update_user_meta($user_id, 'tlms_education_type', $education_type);
        }

        if (isset($_POST['tlms_academic_categories']) && is_array($_POST['tlms_academic_categories'])) {
            $cats = array_map('intval', $_POST['tlms_academic_categories']);
            update_user_meta($user_id, 'tlms_academic_categories', $cats);
        }
    }
}

new TLMS_Save_User_Academy();
