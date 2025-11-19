<?php
class TB_User {
    public static function tb_user_profile_fields( $user ) {
        $faculty = get_user_meta( $user->ID, '_tb_faculty', true );
        ?>
        <h3>التخصصات والكليات</h3>
        <table class="form-table">
            <tr>
                <th><label for="tb_faculty">الكليات</label></th>
                <td>
                    <?php
                    $terms = get_terms( array(
                        'taxonomy' => 'tb_faculty',
                        'hide_empty' => false,
                    ) );
                    ?>
                    <select name="tb_faculty" id="tb_faculty" class="regular-text">
                        <option value=""><?php _e( 'اختر كلية', 'tutor-branching' ); ?></option>
                        <?php foreach ( $terms as $t ) : ?>
                            <option value="<?php echo esc_attr( $t->term_id ); ?>" <?php selected( $faculty, $t->term_id ); ?>>
                                <?php echo esc_html( $t->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function tb_save_user_profile( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) { return false; }
        if ( isset( $_POST['tb_faculty'] ) ) {
            update_user_meta( $user_id, '_tb_faculty', intval( $_POST['tb_faculty'] ) );
        }
    }
}