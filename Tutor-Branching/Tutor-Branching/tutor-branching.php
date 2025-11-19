<?php
/*
Plugin Name: Tutor Branching
Description: Simple branching for faculties/specializations integrated with Tutor LMS.
Version: 1.0.0
Author: Your Name
Text Domain: tutor-branching
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TB_VERSION', '1.0.0' );

/* Include files safely */
$includes = array(
    'includes/class-tb-settings.php',
    'includes/class-tb-admin.php',
    'includes/class-tb-metaboxes.php',
    'includes/class-tb-user.php',
    'includes/class-tb-faculty-crud.php',
);

foreach ( $includes as $file ) {
    $path = TB_PLUGIN_DIR . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

/* Load translations */
function tb_load_textdomain() {
    load_plugin_textdomain( 'tutor-branching', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'tb_load_textdomain' );

/* Determine Tutor LMS course post type (common names) */
function tb_get_course_post_type() {
    if ( post_type_exists( 'tutor_course' ) ) {
        return 'tutor_course';
    }
    if ( post_type_exists( 'courses' ) ) {
        return 'courses';
    }
    // Fallback checks (non-exhaustive)
    if ( class_exists( 'Tutor\\Course' ) ) {
        return 'courses';
    }
    return 'courses';
}

/* Register taxonomies on init */
function tb_register_taxonomies() {
    $course_post_type = tb_get_course_post_type();

    $labels_faculty = array(
        'name' => __( 'Faculties', 'tutor-branching' ),
        'singular_name' => __( 'Faculty', 'tutor-branching' ),
    );
    register_taxonomy( 'tb_faculty', array( $course_post_type ), array(
        'labels' => $labels_faculty,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'rewrite' => array( 'slug' => 'faculty' ),
    ) );

    $labels_sub = array(
        'name' => __( 'Specializations', 'tutor-branching' ),
        'singular_name' => __( 'Specialization', 'tutor-branching' ),
    );
    register_taxonomy( 'tb_subcategory', array( $course_post_type ), array(
        'labels' => $labels_sub,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'rewrite' => array( 'slug' => 'subcategory' ),
    ) );
}
add_action( 'init', 'tb_register_taxonomies', 5 );

/* Activation: run migrations and import sample terms */
function tb_activate_plugin() {
    // Ensure taxonomies are registered before flush
    tb_register_taxonomies();
    flush_rewrite_rules();

    // Run SQL migrations if file exists
    $sql_file = TB_PLUGIN_DIR . 'migrations/tb-migrations.sql';
    if ( file_exists( $sql_file ) ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = file_get_contents( $sql_file );
        if ( ! empty( $sql ) ) {
            // dbDelta expects SQL statements for CREATE TABLE; ensure file formatted accordingly
            dbDelta( $sql );
        }
    }

    // Import sample terms if provided
    $json = TB_PLUGIN_DIR . 'samples/sample-terms.json';
    if ( file_exists( $json ) ) {
        $data = json_decode( file_get_contents( $json ), true );
        if ( is_array( $data ) ) {
            foreach ( $data as $taxonomy => $terms ) {
                if ( ! taxonomy_exists( $taxonomy ) ) {
                    continue;
                }
                foreach ( $terms as $term ) {
                    if ( is_array( $term ) && isset( $term['name'] ) ) {
                        $name = $term['name'];
                        $args = array();
                        if ( isset( $term['parent'] ) ) {
                            $parent = term_exists( $term['parent'], $taxonomy );
                            if ( $parent && isset( $parent['term_id'] ) ) {
                                $args['parent'] = intval( $parent['term_id'] );
                            }
                        }
                        if ( ! term_exists( $name, $taxonomy ) ) {
                            wp_insert_term( $name, $taxonomy, $args );
                        }
                    } elseif ( is_string( $term ) ) {
                        if ( ! term_exists( $term, $taxonomy ) ) {
                            wp_insert_term( $term, $taxonomy );
                        }
                    }
                }
            }
        }
    }
}
register_activation_hook( __FILE__, 'tb_activate_plugin' );

/* Deactivation cleanup (don't drop terms/tables by default) */
function tb_deactivate_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tb_deactivate_plugin' );

/* Helper: get course post type to ensure compatibility with Tutor LMS */
function tb_get_course_post_type_public() {
    return tb_get_course_post_type();
}

/* Optional: filter course queries to restrict by user's faculty (example) */
function tb_filter_courses_by_user( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $course_pt = tb_get_course_post_type();

    if ( ( is_post_type_archive( $course_pt ) ) || ( is_tax( array( 'tb_faculty', 'tb_subcategory' ) ) ) ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return;
        }
        $user_faculty = get_user_meta( $user_id, 'tb_faculty', true ); // expecting term_id
        if ( $user_faculty ) {
            $existing_tax_query = $query->get( 'tax_query' );
            if ( ! is_array( $existing_tax_query ) ) {
                $existing_tax_query = array();
            }
            $existing_tax_query[] = array(
                'taxonomy' => 'tb_faculty',
                'field'    => 'term_id',
                'terms'    => intval( $user_faculty ),
            );
            $query->set( 'tax_query', $existing_tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'tb_filter_courses_by_user' );

/* Ensure assets enqueued (styles) */
function tb_enqueue_assets() {
    wp_enqueue_style( 'tb-styles', TB_PLUGIN_URL . 'assets/css/tb-styles.css', array(), TB_VERSION );
    // Future JS: wp_enqueue_script( 'tb-scripts', TB_PLUGIN_URL . 'assets/js/tb-scripts.js', array('jquery'), TB_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'tb_enqueue_assets' );
add_action( 'admin_enqueue_scripts', 'tb_enqueue_assets' );