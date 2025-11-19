<?php
/*
Template: Tutor Branching - Faculty Edit (Admin)
Description: ÕÝÍÉ ÅÏÇÑÉ ÇáßáíÇÊ (CRUD ÃÓÇÓí ßãËÇá). íãßä ÊæÓíÚåÇ áÇÍÞÇð.
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<div class="wrap">
    <h1><?php _e( 'ÅÏÇÑÉ ÇáßáíÇÊ', 'tutor-branching' ); ?></h1>
    <p><?php _e( 'åÐå ÕÝÍÉ äãæÐÌíÉ áÅÏÇÑÉ ÇáßáíÇÊ ãä áæÍÉ ÇáÊÍßã. íãßä ÊæÓíÚåÇ áÅÖÇÝÉ CRUD ßÇãá.', 'tutor-branching' ); ?></p>

    <!-- äãæÐÌ ÈÓíØ áÅÖÇÝÉ ßáíÉ ÌÏíÏÉ ßãËÇá -->
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="tb_add_faculty">
        <?php wp_nonce_field( 'tb_add_faculty_nonce', 'tb_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="faculty_name"><?php _e( 'ÇÓã ÇáßáíÉ', 'tutor-branching' ); ?></label></th>
                <td><input name="faculty_name" id="faculty_name" type="text" value="" class="regular-text" required></td>
            </tr>
        </table>
        <?php submit_button( __( 'ÅÖÇÝÉ ÇáßáíÉ', 'tutor-branching' ) ); ?>
    </form>
</div>
<?php
get_footer();