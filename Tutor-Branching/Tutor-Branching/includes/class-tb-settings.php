<?php  
if ( ! defined( 'ABSPATH' ) ) {  
    exit;  
}  

class TB_Settings {  
    private $option_name = 'tb_settings';  

    public function __construct() {  
        add_action( 'admin_menu', array( $this, 'tb_add_settings_page' ) );  
        add_action( 'admin_post_tb_save_settings', array( $this, 'tb_handle_settings_save' ) );  
        $this->ensure_default_settings();  
    }  

    public function tb_add_settings_page() {  
        add_options_page(  
            __( 'Tutor Branching Settings', 'tutor-branching' ),  
            __( 'Tutor Branching', 'tutor-branching' ),  
            'manage_options',  
            'tb-settings',  
            array( $this, 'tb_render_settings_page' )  
        );  
    }  

    public function tb_render_settings_page() {  
        if ( ! current_user_can( 'manage_options' ) ) {  
            return;  
        }  

        $settings = get_option( $this->option_name, $this->get_default_settings() );  
        $settings = wp_parse_args( $settings, $this->get_default_settings() );  
        ?>  
        <div class="wrap">  
            <h1><?php esc_html_e( 'Tutor Branching Settings', 'tutor-branching' ); ?></h1>  

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">  
                <?php wp_nonce_field( 'tb_settings_nonce', 'tb_settings_nonce_field' ); ?>  
                <input type="hidden" name="action" value="tb_save_settings">  

                <table class="form-table" role="presentation">  
                    <tbody>  
                        <tr>  
                            <th scope="row">  
                                <label for="tb_visibility"><?php esc_html_e( 'Visibility Default', 'tutor-branching' ); ?></label>  
                            </th>  
                            <td>  
                                <select id="tb_visibility" name="tb_visibility">  
                                    <option value="same_school_only" <?php selected( $settings['visibility_default'], 'same_school_only' ); ?>>  
                                        <?php esc_html_e( 'Same school only', 'tutor-branching' ); ?>  
                                    </option>  
                                    <option value="all" <?php selected( $settings['visibility_default'], 'all' ); ?>>  
                                        <?php esc_html_e( 'All', 'tutor-branching' ); ?>  
                                    </option>  
                                </select>  
                                <p class="description"><?php esc_html_e( 'تحديد الافتراضي لرؤية المحتوى (يمكن تعديله لاحقاً في التكوين).', 'tutor-branching' ); ?></p>  
                            </td>  
                        </tr>  
                        <tr>  
                            <th scope="row">  
                                <label for="tb_enable_feature"><?php esc_html_e( 'Enable Sample Feature', 'tutor-branching' ); ?></label>  
                            </th>  
                            <td>  
                                <input type="checkbox" id="tb_enable_feature" name="tb_enable_feature" value="1" <?php checked( ! empty( $settings['enable_feature'] ), true ); ?> />  
                                <span class="description"><?php esc_html_e( 'تشغيل ميزة تجريبية بسيطة (يمكن توسيعها لاحقاً).', 'tutor-branching' ); ?></span>  
                            </td>  
                        </tr>  
                    </tbody>  
                </table>  

                <?php submit_button( __( 'Save Settings', 'tutor-branching' ) ); ?>  
            </form>  
        </div>  
        <?php  
    }  

    public function tb_handle_settings_save() {  
        if ( ! isset( $_POST['tb_settings_nonce_field'] ) || ! wp_verify_nonce( wp_unslash( $_POST['tb_settings_nonce_field'] ), 'tb_settings_nonce' ) ) {  
            wp_die( __( 'Security check failed', 'tutor-branching' ) );  
        }  

        if ( ! current_user_can( 'manage_options' ) ) {  
            wp_die( __( 'Insufficient permissions', 'tutor-branching' ) );  
        }  

        // Read and sanitize inputs  
        $visibility = isset( $_POST['tb_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['tb_visibility'] ) ) : 'same_school_only';  
        $enable_feature = isset( $_POST['tb_enable_feature'] ) ? 1 : 0;  

        $new_settings = array(  
            'visibility_default' => $visibility,  
            'enable_feature'     => $enable_feature,  
        );  

        update_option( $this->option_name, $new_settings );  

        wp_safe_redirect( add_query_arg( 'settings-updated', 'true', admin_url( 'options-general.php?page=tb-settings' ) ) );  
        exit();  
    }  

    private function get_default_settings() {  
        return array(  
            'visibility_default' => 'same_school_only',  
            'enable_feature' => 0,  
        );  
    }  

    private function ensure_default_settings() {  
        $current = get_option( $this->option_name, null );  
        if ( $current === null || ! is_array( $current ) ) {  
            update_option( $this->option_name, $this->get_default_settings() );  
        }  
    }  
}  

new TB_Settings();