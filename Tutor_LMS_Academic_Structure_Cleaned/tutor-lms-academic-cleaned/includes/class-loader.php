<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Simple loader for Tutor LMS Academic Structure plugin classes.
 * Adjust ordering if you add/remove classes.
 */

$base = dirname( __FILE__ );

$files = array(
    'class-activation.php',
    'class-admin-settings.php',
    'class-compatibility.php',
    'class-course-visibility.php',
    'class-integration-handler.php',
    'class-multisite-support.php',
    'class-register-taxonomies.php',
    'class-filter-courses.php',
);

foreach ( $files as $file ) {
    $path = $base . DIRECTORY_SEPARATOR . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}
