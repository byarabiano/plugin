<?php
/*
Template: Tutor Branching - Subcategory Edit (Admin)
Description: ÕÝÍÉ áÊÚÏíá ÇáÊÕäíÝÇÊ ÇáÝÑÚíÉ ßäãæÐÌ ÊæÖíÍí.
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<div class="wrap">
    <h1><?php _e( 'ÇáÊÕäíÝÇÊ ÇáÝÑÚíÉ', 'tutor-branching' ); ?></h1>
    <p><?php _e( 'ÕÝÍÉ ÊæÖíÍíÉ áÅÏÇÑÉ ÇáÊÕäíÝÇÊ ÇáÝÑÚíÉ ÇáãÑÊÈØÉ ÈÇáßæÑÓÇÊ.', 'tutor-branching' ); ?></p>

    <!-- ÞÇÆãÉ ÈÓíØÉ ãä ÇáÊÕäíÝÇÊ ÇáÝÑÚíÉ ßÅØÇÑ Úãá -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'ÇáãÚÑÝ', 'tutor-branching' ); ?></th>
                <th><?php _e( 'ÇáÇÓã', 'tutor-branching' ); ?></th>
                <th><?php _e( 'ÇáÅÌÑÇÁÇÊ', 'tutor-branching' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $terms = get_terms( array(
                'taxonomy' => 'tb_subcategory',
                'hide_empty' => false,
            ) );
            foreach ( $terms as $t ) {
                echo '<tr>';
                echo '<td>' . esc_html( $t->term_id ) . '</td>';
                echo '<td>' . esc_html( $t->name ) . '</td>';
                echo '<td><a href="' . esc_url( get_term_link( $t ) ) . '">' . __( 'ÚÑÖ', 'tutor-branching' ) . '</a></td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>
<?php
get_footer();