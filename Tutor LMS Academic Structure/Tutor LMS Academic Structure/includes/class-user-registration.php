<?php
if (!defined('ABSPATH')) exit;

class TLMS_User_Registration {

    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
            $instance->hooks();
        }
        return $instance;
    }

    public function hooks() {

        // Inject our custom fields into registration forms
        add_action('tutor_student_registration_after_terms', [$this, 'show_registration_fields']);
        add_action('tutor_instructor_registration_after_terms', [$this, 'show_registration_fields']);

        // Validate fields before account creation
        add_filter('registration_errors', [$this, 'validate_registration'], 10, 3);

        // Save fields to user_meta
        add_action('user_register', [$this, 'save_user_academic_data']);
    }

    /**
     * Show the custom fields form
     */
    public function show_registration_fields() {
        require TLMS_ACADEMIC_PRO_PATH . 'public/partials/registration-fields.php';
    }

    /**
     * Required fields validation
     */
    public function validate_registration($errors, $username, $email) {

        if (!isset($_POST['tlms_education_type'])) return $errors;

        $type = sanitize_text_field($_POST['tlms_education_type']);

        if ($type === 'university') {

            if (empty($_POST['tlms_faculty'])) {
                $errors->add('tlms_faculty_missing', __('Please select your faculty.', 'tutor-lms-academic-pro'));
            }

        } elseif ($type === 'school') {

            if (empty($_POST['tlms_school_stage'])) {
                $errors->add('tlms_school_stage_missing', __('Please select your school stage.', 'tutor-lms-academic-pro'));
            }

            if (empty($_POST['tlms_school_grade'])) {
                $errors->add('tlms_school_grade_missing', __('Please select your academic grade.', 'tutor-lms-academic-pro'));
            }
        }

        return $errors;
    }

    /**
     * Save data after successful registration
     */
    public function save_user_academic_data($user_id) {

        if (!isset($_POST['tlms_education_type'])) return;

        $type = sanitize_text_field($_POST['tlms_education_type']);
        update_user_meta($user_id, '_tlms_education_type', $type);

        if ($type === 'university') {
            update_user_meta($user_id, '_tlms_faculty', sanitize_text_field($_POST['tlms_faculty']));
        }

        if ($type === 'school') {
            update_user_meta($user_id, '_tlms_school_stage', sanitize_text_field($_POST['tlms_school_stage']));
            update_user_meta($user_id, '_tlms_school_grade', sanitize_text_field($_POST['tlms_school_grade']));
        }
    }
}
TLMS_User_Registration::instance();
