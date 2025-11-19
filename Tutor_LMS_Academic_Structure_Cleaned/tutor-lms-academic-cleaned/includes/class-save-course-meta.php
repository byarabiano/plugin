<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TLMS_Save_Course_Meta {

    private $taxonomy = 'tlms_academic_category';

    public function __construct() {
        add_action('tutor_course_builder_after_save', array($this, 'save_academic_terms'), 10, 2);
    }

    public function save_academic_terms($course_id, $request_data) {

        if (!isset($_POST['tlms_university']) && !isset($_POST['tlms_faculty']) && !isset($_POST['tlms_department'])) {
            return;
        }

        $university = intval($_POST['tlms_university']);
        $faculty = intval($_POST['tlms_faculty']);
        $department = intval($_POST['tlms_department']);

        $terms_to_assign = [];

        if ($university) $terms_to_assign[] = $university;
        if ($faculty) $terms_to_assign[] = $faculty;
        if ($department) $terms_to_assign[] = $department;

        if (!empty($terms_to_assign)) {
            wp_set_post_terms($course_id, $terms_to_assign, $this->taxonomy, false);
        }
    }

}

new TLMS_Save_Course_Meta();
