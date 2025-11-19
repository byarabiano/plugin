<?php
class TB_Admin {
    public static function tb_setup_menu() {
        add_menu_page(
            'Tutor Branching',
            'Tutor Branching',
            'manage_options',
            'tb-settings',
            array( 'TB_Admin', 'tb_settings_page' ),
            'dashicons-welcome-learn-more',
            60
        );
        // ÕÝÍÉ ÅÚÏÇÏÇÊ ÝÑÚíÉ ßãËÇá
        add_submenu_page(
            'tb-settings',
            __( 'ÇáßáíÇÊ æÇáÊÕäíÝÇÊ', 'tutor-branching' ),
            __( 'ÇáßáíÇÊ æÇáÊÕäíÝÇÊ', 'tutor-branching' ),
            'manage_options',
            'tb-faculties',
            array( 'TB_Admin', 'tb_faculties_page' )
        );
    }

    public static function tb_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'tutor-branching' ) );
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Tutor Branching - ÇáÅÚÏÇÏÇÊ', 'tutor-branching' ); ?></h1>
            <p><?php _e( 'ÅÏÇÑÉ ÇáßáíÇÊ æÇáÊÕäíÝÇÊ ÇáÝÑÚíÉ æÑÈØåÇ ÈÇáßæÑÓÇÊ Ýí Tutor LMS.', 'tutor-branching' ); ?></p>
            <a href="<?php echo admin_url( 'admin.php?page=tb-faculties' ); ?>" class="button button-primary">
                <?php _e( 'ÅÏÇÑÉ ÇáßáíÇÊ æÇáÊÕäíÝÇÊ', 'tutor-branching' ); ?>
            </a>
        </div>
        <?php
    }

    public static function tb_faculties_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'tutor-branching' ) );
        }
        // ãËÇá ÈÓíØ áæÇÌåÉ CRUD: íãßäß ÊæÓíÚåÇ áÇÍÞÇð
        ?>
        <div class="wrap">
            <h1><?php _e( 'ÅÏÇÑÉ ÇáßáíÇÊ æÇáÊÕäíÝÇÊ', 'tutor-branching' ); ?></h1>
            <p><?php _e( 'åÐÇ ÇáÞÓã åæ ãßÇä ÊæÓíÚ CRUD áÅÖÇÝÉ/ÊÚÏíá/ÍÐÝ ÇáßáíÇÊ æÇáÊÕäíÝÇÊ ÇáÝÑÚíÉ.', 'tutor-branching' ); ?></p>
            <!-- íãßä ÅÖÇÝÉ ÌÏÇæá Ãæ äãÇÐÌ åäÇ áÇÍÞÇð -->
        </div>
        <?php
    }
}