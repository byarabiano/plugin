<?php

class TLMS_Ajax_Handler {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('wp_ajax_tlms_admin_actions', array($this, 'handle_admin_actions'));
        add_action('wp_ajax_tlms_export_settings', array($this, 'export_settings'));
        add_action('wp_ajax_tlms_import_settings', array($this, 'import_settings'));
        add_action('wp_ajax_tlms_bulk_assign_categories', array($this, 'bulk_assign_categories'));
    }
    
    public function handle_admin_actions() {
        check_ajax_referer('tlms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_tutor')) {
            wp_send_json_error(__('Insufficient permissions.', 'tutor-lms-academic-pro'));
        }
        
        $action = sanitize_text_field($_POST['action_type']);
        
        switch ($action) {
            case 'migrate_existing_users':
                $this->migrate_existing_users();
                break;
                
            case 'clear_cache':
                $this->clear_cache();
                break;
                
            case 'validate_categories':
                $this->validate_categories();
                break;
                
            default:
                wp_send_json_error(__('Unknown action.', 'tutor-lms-academic-pro'));
        }
    }
    
    private function migrate_existing_users() {
        $options = get_option('tlms_academic_pro_settings');
        $default_category = isset($options['default_user_category']) ? $options['default_user_category'] : 'general';
        
        $users = get_users(array(
            'meta_query' => array(
                array(
                    'key' => 'tlms_education_type',
                    'compare' => 'NOT EXISTS'
                )
            )
        ));
        
        $migrated = 0;
        foreach ($users as $user) {
            update_user_meta($user->ID, 'tlms_education_type', $default_category);
            $migrated++;
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('Migrated %d users to default category.', 'tutor-lms-academic-pro'), $migrated),
            'migrated' => $migrated
        ));
    }
    
    private function clear_cache() {
        // Clear any transients or cached data
        $cache_cleared = array();
        
        // Clear category cache
        wp_cache_delete('tlms_categories_tree', 'tlms');
        $cache_cleared[] = __('Category tree cache', 'tutor-lms-academic-pro');
        
        // Clear user counts cache
        wp_cache_delete('tlms_user_counts', 'tlms');
        $cache_cleared[] = __('User counts cache', 'tutor-lms-academic-pro');
        
        wp_send_json_success(array(
            'message' => __('Cache cleared successfully.', 'tutor-lms-academic-pro'),
            'cleared_items' => $cache_cleared
        ));
    }
    
    private function validate_categories() {
        $issues = array();
        
        // Check for orphaned categories
        $all_categories = get_terms(array(
            'taxonomy' => 'tlms_academic_category',
            'hide_empty' => false,
            'hierarchical' => false
        ));
        
        foreach ($all_categories as $category) {
            // Check if category has education type meta
            $education_type = get_term_meta($category->term_id, 'education_type', true);
            if (!$education_type) {
                $issues[] = array(
                    'type' => 'missing_meta',
                    'category' => $category->name,
                    'message' => __('Category missing education type.', 'tutor-lms-academic-pro')
                );
            }
            
            // Check for circular references
            if ($category->parent) {
                $parent = get_term($category->parent, 'tlms_academic_category');
                if ($parent && $this->is_circular_reference($category->term_id, $parent->term_id)) {
                    $issues[] = array(
                        'type' => 'circular_reference',
                        'category' => $category->name,
                        'message' => __('Circular reference detected.', 'tutor-lms-academic-pro')
                    );
                }
            }
        }
        
        wp_send_json_success(array(
            'issues' => $issues,
            'total_categories' => count($all_categories)
        ));
    }
    
    private function is_circular_reference($child_id, $parent_id, $depth = 0) {
        if ($depth > 10) { // Prevent infinite loops
            return true;
        }
        
        if ($child_id == $parent_id) {
            return true;
        }
        
        $parent_term = get_term($parent_id, 'tlms_academic_category');
        if ($parent_term->parent) {
            return $this->is_circular_reference($child_id, $parent_term->parent, $depth + 1);
        }
        
        return false;
    }
    
    public function export_settings() {
        check_ajax_referer('tlms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_tutor')) {
            wp_die(__('Insufficient permissions.', 'tutor-lms-academic-pro'));
        }
        
        $export_data = array(
            'settings' => get_option('tlms_academic_pro_settings'),
            'categories' => $this->export_categories(),
            'export_date' => current_time('mysql'),
            'version' => TLMS_ACADEMIC_PRO_VERSION
        );
        
        $filename = 'tlms-academic-pro-settings-' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        
        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    private function export_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'tlms_academic_category',
            'hide_empty' => false,
            'get' => 'all'
        ));
        
        $export_categories = array();
        
        foreach ($categories as $category) {
            $export_categories[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'parent' => $category->parent,
                'education_type' => get_term_meta($category->term_id, 'education_type', true),
                'term_order' => $category->term_order
            );
        }
        
        return $export_categories;
    }
    
    public function import_settings() {
        check_ajax_referer('tlms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_tutor')) {
            wp_send_json_error(__('Insufficient permissions.', 'tutor-lms-academic-pro'));
        }
        
        if (empty($_FILES['import_file'])) {
            wp_send_json_error(__('No file uploaded.', 'tutor-lms-academic-pro'));
        }
        
        $file = $_FILES['import_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(__('File upload error.', 'tutor-lms-academic-pro'));
        }
        
        $content = file_get_contents($file['tmp_name']);
        $import_data = json_decode($content, true);
        
        if (!$import_data) {
            wp_send_json_error(__('Invalid JSON file.', 'tutor-lms-academic-pro'));
        }
        
        $results = array();
        
        // Import settings
        if (isset($import_data['settings'])) {
            update_option('tlms_academic_pro_settings', $import_data['settings']);
            $results[] = __('Settings imported successfully.', 'tutor-lms-academic-pro');
        }
        
        // Import categories
        if (isset($import_data['categories']) && is_array($import_data['categories'])) {
            $imported_categories = $this->import_categories($import_data['categories']);
            $results[] = sprintf(__('Imported %d categories.', 'tutor-lms-academic-pro'), $imported_categories);
        }
        
        wp_send_json_success(array(
            'message' => __('Import completed successfully.', 'tutor-lms-academic-pro'),
            'results' => $results
        ));
    }
    
    private function import_categories($categories) {
        $imported = 0;
        
        foreach ($categories as $category_data) {
            $term = wp_insert_term(
                $category_data['name'],
                'tlms_academic_category',
                array(
                    'description' => $category_data['description'],
                    'slug' => $category_data['slug'],
                    'parent' => $category_data['parent']
                )
            );
            
            if (!is_wp_error($term)) {
                update_term_meta($term['term_id'], 'education_type', $category_data['education_type']);
                $imported++;
            }
        }
        
        return $imported;
    }
    
    public function bulk_assign_categories() {
        check_ajax_referer('tlms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_tutor')) {
            wp_send_json_error(__('Insufficient permissions.', 'tutor-lms-academic-pro'));
        }
        
        $user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : array();
        $education_type = sanitize_text_field($_POST['education_type']);
        $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : array();
        
        if (empty($user_ids)) {
            wp_send_json_error(__('No users selected.', 'tutor-lms-academic-pro'));
        }
        
        $processed = 0;
        foreach ($user_ids as $user_id) {
            update_user_meta($user_id, 'tlms_education_type', $education_type);
            update_user_meta($user_id, 'tlms_academic_categories', $categories);
            $processed++;
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('Updated %d users.', 'tutor-lms-academic-pro'), $processed),
            'processed' => $processed
        ));
    }
}

?>