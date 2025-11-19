<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TLMS_Course_Filter {

    private $taxonomy = 'tlms_academic_category';

    public function __construct() {
        add_action('pre_get_posts', array($this, 'filter_courses_for_students'));
    }

    public function filter_courses_for_students($query) {

        // نطبق الفلترة فقط على الواجهة وليس داخل لوحة التحكم
        if (is_admin()) return;

        // فقط على الكورسات وليس المقالات أو أي شيء آخر
        if (!isset($query->query['post_type']) || $query->query['post_type'] !== 'tutor_course') return;

        // تأكيد تسجيل الدخول
        if (!is_user_logged_in()) return;

        $user_id = get_current_user_id();
        $user_terms = get_user_meta($user_id, 'tlms_user_academic_terms', true);

        // إذا لم يكن لدى الطالب مسار أكاديمي → يرى فقط الكورسات العامة
        if (empty($user_terms) || !is_array($user_terms)) {
            $query->set('tax_query', array(
                'relation' => 'OR',
                array(
                    'taxonomy' => $this->taxonomy,
                    'operator' => 'NOT EXISTS'
                )
            ));
            return;
        }

        // فلترة الكورسات حتى تتوافق مع المسار
        $query->set('tax_query', array(
            'relation' => 'OR',

            // الكورسات التي تطابق التصنيف الأكاديمي للطالب
            array(
                'taxonomy' => $this->taxonomy,
                'field' => 'term_id',
                'terms' => $user_terms,
                'include_children' => true,
            ),

            // السماح بعرض الكورسات العامة دائماً
            array(
                'taxonomy' => $this->taxonomy,
                'operator' => 'NOT EXISTS'
            )
        ));
    }
}

new TLMS_Course_Filter();
