<?php
// includes/class-tb-faculty-crud.php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TB_Faculty_CRUD {
    // مرجع للـ taxonomy
    const TAX = 'tb_faculty';

    public static function get_all() {
        $terms = get_terms( array(
            'taxonomy' => self::TAX,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ) );
        return $terms;
    }

    public static function add($name, $description = '', $parent = 0) {
        $args = array(
            'description' => $description,
            'parent' => intval($parent),
        );
        $result = wp_insert_term( $name, self::TAX, $args );
        return $result;
    }

    public static function update_term($term_id, $name, $description = '', $parent = 0) {
        $args = array(
            'name' => $name,
            'description' => $description,
            'parent' => intval($parent),
        );
        return wp_update_term( intval($term_id), self::TAX, $args );
    }

    public static function delete_term($term_id) {
        return wp_delete_term( intval($term_id), self::TAX );
    }
}