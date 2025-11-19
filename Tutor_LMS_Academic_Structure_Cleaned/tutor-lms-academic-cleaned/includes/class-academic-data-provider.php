<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TLMS_Academic_Data_Provider {

    private $taxonomy = 'tlms_academic_category';

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'inject_academic_data_into_builder'));
    }

    public function inject_academic_data_into_builder() {

        // تحميل السكربت الذي صنعناه للـ Course Builder
        wp_enqueue_script(
            'tlms-academic-panel',
            plugin_dir_url(__FILE__) . '../public/js/course-builder-academic-panel.js',
            array('wp-hooks', 'wp-element', 'jquery'),
            time(),
            true
        );

        // جلب كل المصطلحات
        $terms = get_terms(array(
            'taxonomy' => $this->taxonomy,
            'hide_empty' => false
        ));

        $universities = [];
        $faculties = [];
        $departments = [];

        foreach ($terms as $term) {
            $type = get_term_meta($term->term_id, 'academic_type', true);

            if ($type === 'university') {
                $universities[] = [
                    'id' => $term->term_id,
                    'name' => $term->name
                ];
            }

            if ($type === 'faculty') {
                $faculties[] = [
                    'id' => $term->term_id,
                    'name' => $term->name
                ];
            }

            if ($type === 'department') {
                $departments[] = [
                    'id' => $term->term_id,
                    'name' => $term->name
                ];
            }
        }

        // تمريرها للجافاسكربت
        wp_localize_script(
            'tlms-academic-panel',
            'tlms_academic_data',
            array(
                'universities' => $universities,
                'faculties' => $faculties,
                'departments' => $departments
            )
        );
    }

}

new TLMS_Academic_Data_Provider();
