<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // منع الوصول المباشر
}

class TB_Admin {

    // إعداد قائمة القائمة الجانبية
    public static function tb_setup_menu() {
        if ( ! function_exists( 'add_menu_page' ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        add_menu_page(
            'Tutor Branching',
            'Tutor Branching',
            'manage_options',
            'tb-settings',
            array( 'TB_Admin', 'tb_settings_page' ),
            'dashicons-welcome-learn-more',
            60
        );

        add_submenu_page(
            'tb-settings',
            __( 'الكليات والتصنيفات', 'tutor-branching' ),
            __( 'الكليات والتصنيفات', 'tutor-branching' ),
            'manage_options',
            'tb-faculties',
            array( 'TB_Admin', 'tb_faculties_page' )
        );
    }

    // صفحة الإعدادات
    public static function tb_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'tutor-branching' ) );
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Tutor Branching - الإعدادات', 'tutor-branching' ); ?></h1>
            <p><?php _e( 'إدارة الكليات والتصنيفات الفرعية وربطها بالكورسات في Tutor LMS.', 'tutor-branching' ); ?></p>
            <a href="<?php echo admin_url( 'admin.php?page=tb-faculties' ); ?>" class="button button-primary">
                <?php _e( 'إدارة الكليات والتصنيفات', 'tutor-branching' ); ?>
            </a>
        </div>
        <?php
    }

    // صفحة الكليات
    public static function tb_faculties_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'tutor-branching' ) );
        }

        // معالجة POST لإضافة/تحديث (مثال بسيط)
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['tb_faculty_nonce'] ) ) {
            if ( check_admin_referer( 'tb_faculty_crud', 'tb_faculty_nonce' ) ) {
                if ( ! empty( $_POST['tb_faculty_name'] ) ) {
                    $name = sanitize_text_field( $_POST['tb_faculty_name'] );
                    $desc = isset( $_POST['tb_faculty_desc'] ) ? sanitize_textarea_field( $_POST['tb_faculty_desc'] ) : '';

                    // إضافة كليات جديدة
                    if ( class_exists( 'TB_Faculty_CRUD' ) ) {
                        TB_Faculty_CRUD::add( $name, $desc );
                    }
                }
            }
        }

        // عرض قائمة الكليات
        $terms = ( class_exists( 'TB_Faculty_CRUD' ) ) ? TB_Faculty_CRUD::get_all() : array();
        ?>
        <div class="wrap">
            <h1><?php _e( 'إدارة الكليات والتصنيفات', 'tutor-branching' ); ?></h1>

            <form method="post" style="max-width: 700px;">
                <?php wp_nonce_field( 'tb_faculty_crud', 'tb_faculty_nonce' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="tb_faculty_name"><?php _e( 'اسم الكلية', 'tutor-branching' ); ?></label></th>
                        <td>
                            <input type="text" id="tb_faculty_name" name="tb_faculty_name" class="regular-text" required />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="tb_faculty_desc"><?php _e( 'الوصف (اختياري)', 'tutor-branching' ); ?></label></th>
                        <td>
                            <textarea id="tb_faculty_desc" name="tb_faculty_desc" rows="4" cols="50" class="large-text"></textarea>
                        </td>
                    </tr>
                </table>
                <p>
                    <input type="submit" class="button button-primary" value="<?php _e( 'إضافة الكلية', 'tutor-branching' ); ?>">
                </p>
            </form>

            <hr />

            <h2><?php _e( 'قائمة الكليات المسجلة', 'tutor-branching' ); ?></h2>
            <?php if ( ! empty( $terms ) && is_array( $terms ) ) : ?>
                <ul>
                    <?php foreach ( $terms as $term ) : ?>
                        <li><?php echo esc_html( $term['name'] . ' - ' . $term['desc'] ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php _e( 'لا توجد كليات مسجلة حتى الآن.', 'tutor-branching' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}