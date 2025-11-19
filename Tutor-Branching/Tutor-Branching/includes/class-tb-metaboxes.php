<?php
class TB_Metaboxes {
    public static function tb_register_course_metabox() {
        add_meta_box(
            'tb_subcategory_box',
            __( 'التصنيف الفرعي المستهدف', 'tutor-branching' ),
            array( __CLASS__, 'tb_render_subcategory_meta' ),
            'course',
            'side',
            'default'
        );
    }

    public static function tb_render_subcategory_meta( $post ) {
        $selected = get_post_meta( $post->ID, '_tb_subcategory', true );
        $terms = get_terms( array(
            'taxonomy' => 'tb_subcategory',
            'hide_empty' => false,
        ) );
        ?>
        <label for="tb_subcategory"><?php _e( 'اختر تصنيف فرعي', 'tutor-branching' ); ?></label>
        <select name="tb_subcategory" id="tb_subcategory" style="width:100%;">
            <option value=""><?php _e( 'اختر تصنيف فرعي', 'tutor-branching' ); ?></option>
            <?php foreach ( $terms as $t ): ?>
                <option value="<?php echo esc_attr( $t->term_id ); ?>" <?php selected( $selected, $t->term_id ); ?>>
                    <?php echo esc_html( $t->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function tb_save_course_meta( $post_id, $post ) {
        if ( $post->post_type !== 'course' ) { return; }
        if ( isset( $_POST['tb_subcategory'] ) ) {
            update_post_meta( $post_id, '_tb_subcategory', intval( $_POST['tb_subcategory'] ) );
        } else {
            delete_post_meta( $post_id, '_tb_subcategory' );
        }
    }
}