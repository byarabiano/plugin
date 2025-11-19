<?php
if (!defined('ABSPATH')) exit;

class TLMS_Export_Import {

    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
            $instance->hooks();
        }
        return $instance;
    }

    public function hooks() {
        add_action('admin_post_tlms_import_academic_csv', [$this, 'import_csv']);
        add_action('admin_post_tlms_export_academic_csv', [$this, 'export_csv']);
    }

    public function import_csv() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized.');
        if (!isset($_FILES['tlms_csv_file'])) wp_die('No file uploaded.');

        $file = fopen($_FILES['tlms_csv_file']['tmp_name'], 'r');

        $type = sanitize_text_field($_POST['tlms_import_type']);
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            if ($type === 'university' && $data['university'] !== '') {
                $university = $this->create_term('tlms_university', $data['university']);

                if (!empty($data['faculty'])) {
                    $faculty = $this->create_term('tlms_faculty', $data['faculty'], $university);
                    if (!empty($data['department'])) {
                        $this->create_term('tlms_department', $data['department'], $faculty);
                    }
                }
            }

            if ($type === 'school' && $data['stage'] !== '') {
                $stage = $this->create_term('tlms_school_stage', $data['stage']);
                if (!empty($data['grade'])) {
                    $this->create_term('tlms_school_grade', $data['grade'], $stage);
                }
            }

            if ($type === 'general' && !empty($data['group'])) {
                $this->create_term('tlms_general_group', $data['group']);
            }
        }

        fclose($file);
        wp_redirect(wp_get_referer());
        exit;
    }

    public function create_term($taxonomy, $name, $parent = 0) {
        $existing = term_exists($name, $taxonomy);
        if ($existing) return $existing['term_id'];

        $result = wp_insert_term($name, $taxonomy, ['parent' => $parent]);
        return (is_wp_error($result)) ? 0 : $result['term_id'];
    }

    public function export_csv() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized.');

        $type = sanitize_text_field($_GET['type']);

        switch ($type) {
            case 'university':
                $filename = "universities.csv";
                $terms1 = get_terms(['taxonomy'=>'tlms_university','hide_empty'=>false]);
                $terms2 = get_terms(['taxonomy'=>'tlms_faculty','hide_empty'=>false]);
                $terms3 = get_terms(['taxonomy'=>'tlms_department','hide_empty'=>false]);

                header('Content-Type: text/csv; charset=UTF-8');
                header("Content-Disposition: attachment; filename=$filename");
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                $output = fopen("php://output", "w");
                fputcsv($output, ['university','faculty','department']);

                foreach ($terms1 as $uni) {
                    foreach ($terms2 as $faculty) {
                        if ($faculty->parent == $uni->term_id) {
                            foreach ($terms3 as $dep) {
                                if ($dep->parent == $faculty->term_id) {
                                    fputcsv($output, [$uni->name, $faculty->name, $dep->name]);
                                }
                            }
                        }
                    }
                }

                fclose($output);
                exit;

            case 'school':
                $filename = "schools.csv";
                $stages = get_terms(['taxonomy'=>'tlms_school_stage','hide_empty'=>false]);
                $grades = get_terms(['taxonomy'=>'tlms_school_grade','hide_empty'=>false]);

                header('Content-Type: text/csv; charset=UTF-8');
                header("Content-Disposition: attachment; filename=$filename");
                echo "\xEF\xBB\xBF";
                $output = fopen("php://output", "w");
                fputcsv($output, ['stage','grade']);

                foreach ($stages as $s) {
                    foreach ($grades as $g) {
                        if ($g->parent == $s->term_id) {
                            fputcsv($output, [$s->name, $g->name]);
                        }
                    }
                }

                fclose($output);
                exit;

            case 'general':
                $filename = "general_courses.csv";
                $groups = get_terms(['taxonomy'=>'tlms_general_group','hide_empty'=>false]);

                header('Content-Type: text/csv; charset=UTF-8');
                header("Content-Disposition: attachment; filename=$filename");
                echo "\xEF\xBB\xBF";
                $output = fopen("php://output", "w");
                fputcsv($output, ['group']);

                foreach ($groups as $g) fputcsv($output, [$g->name]);

                fclose($output);
                exit;
        }
    }
}

TLMS_Export_Import::instance();
