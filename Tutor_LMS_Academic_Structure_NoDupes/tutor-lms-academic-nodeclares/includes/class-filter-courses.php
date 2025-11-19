<?php
if (!defined('ABSPATH')) exit;

class TLMS_Filter_Courses {

    public function __construct() {
        add_action('pre_get_posts', array($this, 'filter_course_queries'));
    }

    public function filter_course_queries($query) {
        if (is_admin() || !$query->is_main_query()) return;

        // تأكد أن الاستعلام خاص بالكورسات في Tutor LMS
        if (!(is_post_type_archive('courses') || is_tax('course-category') || is_page('courses'))) return;

        $user_id = get_current_user_id();
        if (!$user_id) return; // الزائر يرى كل شيء

        // قراءة مسار الطالب
        $type = get_user_meta($user_id, '_tlms_education_type', true);

        // لو مسجل كورسات عامة -> يرى العامة فقط + أي كورس بدون تصنيف أكاديمي
        if ($type === 'general') {
            $query->set('meta_query', array(
                'relation' => 'OR',
                array(
                    'key'   => '_tlms_course_education_type',
                    'value' => 'general',
                    'compare' => '='
                ),
                array(
                    'key'     => '_tlms_course_education_type',
                    'compare' => 'NOT EXISTS'
                )
            ));
            return;
        }

        /* جامعات */
        if ($type === 'university') {
            $university = get_user_meta($user_id, '_tlms_university', true);
            $faculty    = get_user_meta($user_id, '_tlms_faculty', true);
            $department = get_user_meta($user_id, '_tlms_department', true);

            $meta = array('relation' => 'OR');

            // الطالب يرى:
            // 1) كورسات تخصصه
            // 2) كورسات كليته
            // 3) كورسات جامعته
            if ($department) {
                $meta[] = array('key'=>'_tlms_course_department','value'=>$department);
            }
            if ($faculty) {
                $meta[] = array('key'=>'_tlms_course_faculty','value'=>$faculty);
            }
            if ($university) {
                $meta[] = array('key'=>'_tlms_course_university','value'=>$university);
            }

            // إضافة الكورسات العامة دائمًا
            $meta[] = array('key'=>'_tlms_course_education_type','value'=>'general');

            $query->set('meta_query', $meta);
            return;
        }

        /* مدارس */
        if ($type === 'school') {
            $level = get_user_meta($user_id, '_tlms_school_level', true);
            $grade = get_user_meta($user_id, '_tlms_school_grade', true);

            $meta = array('relation' => 'OR');

            if ($grade) $meta[] = array('key'=>'_tlms_course_school_grade','value'=>$grade);
            if ($level) $meta[] = array('key'=>'_tlms_course_school_level','value'=>$level);

            // الكورسات العامة تظهر دائمًا
            $meta[] = array('key'=>'_tlms_course_education_type', 'value'=>'general');

            $query->set('meta_query', $meta);
            return;
        }
    }
}

new TLMS_Filter_Courses();
