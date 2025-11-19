<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function tb_uninstall_cleanup() {
    global $wpdb;
    // اختياري: تنظيف metadata مرتبطة بالإضافات
    $wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = '_tb_subcategory'" );
    // يمكن إضافة تنظيفات أخرى حسب الحاجة
}
register_uninstall_hook( __FILE__, 'tb_uninstall_cleanup' );