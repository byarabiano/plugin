<?php  
if ( ! defined( 'ABSPATH' ) ) {  
    exit; // منع الوصول المباشر  
}  

class TB_Admin {  

    // إعداد قائمة القائمة الجانبية  
    public static function tb_setup_menu() {  
        // لا نتحقق من current_user_can هنا لأن add_menu_page يتعامل مع capability  
        add_menu_page(  
            __( 'Tutor Branching', 'tutor-branching' ),  
            __( 'Tutor Branching', 'tutor-branching' ),  
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
            wp_die( __( 'غير مسموح', 'tutor-branching' ) );  
        }  
        ?>  
        <div class="wrap">  
            <h1><?php esc_html_e( 'Tutor Branching - الإعدادات', 'tutor-branching' ); ?></h1>  
            <p><?php esc_html_e( 'إدارة الكليات والتصنيفات الفرعية وربطها بالكورسات في Tutor LMS.', 'tutor-branching' ); ?></p>  
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=tb-faculties' ) ); ?>" class="button button-primary">  
                <?php esc_html_e( 'إدارة الكليات والتصنيفات', 'tutor-branching' ); ?>  
            </a>  
        </div>  
        <?php  
    }  

    // صفحة الكليات  
    public static function tb_faculties_page() {  
        if ( ! current_user_can( 'manage_options' ) ) {  
            wp_die( __( 'غير مسموح', 'tutor-branching' ) );  
        }  

        // معالجة POST لإضافة/تحديث (مثال بسيط)  
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['tb_faculty_nonce'] ) ) {  
            if ( check_admin_referer( 'tb_faculty_crud', 'tb_faculty_nonce' ) ) {  
                if ( ! empty( $_POST['tb_faculty_name'] ) ) {  
                    $name = sanitize_text_field( wp_unslash( $_POST['tb_faculty_name'] ) );  
                    $desc = isset( $_POST['tb_faculty_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tb_faculty_desc'] ) ) : '';  

                    // إضافة كليات جديدة عبر CRUD class إن وُجد  
                    if ( class_exists( 'TB_Faculty_CRUD' ) ) {  
                        TB_Faculty_CRUD::add( $name, $desc );  
                    } else {  
                        // بديل: إنشاء ترم في taxonomy إذا لم توجد الكلاس  
                        if ( ! term_exists( $name, 'tb_faculty' ) ) {  
                            wp_insert_term( $name, 'tb_faculty', array( 'description' => $desc ) );  
                        }  
                    }  

                    // بعد الإضافة نعيد التوجيه لتجنب إعادة الإرسال عند تحديث الصفحة  
                    wp_safe_redirect( admin_url( 'admin.php?page=tb-faculties' ) );  
                    exit;  
                }  
            }  
        }  

        // عرض قائمة الكليات  
        $terms = array();  

        if ( class_exists( 'TB_Faculty_CRUD' ) ) {  
            $terms = TB_Faculty_CRUD::get_all();  
        } else {  
            // جلب المصطلحات من taxonomy كبديل  
            $f_terms = get_terms( array(  
                'taxonomy'   => 'tb_faculty',  
                'hide_empty' => false,  
            ) );  
            if ( ! is_wp_error( $f_terms ) && ! empty( $f_terms ) ) {  
                foreach ( $f_terms as $t ) {  
                    $terms[] = array(  
                        'name' => $t->name,  
                        'desc' => $t->description,  
                        'term_id' => $t->term_id,  
                    );  
                }  
            }  
        }  
        ?>  
        <div class="wrap">  
            <h1><?php esc_html_e( 'إدارة الكليات والتصنيفات', 'tutor-branching' ); ?></h1>  

            <form method="post" style="max-width: 700px;">  
                <?php wp_nonce_field( 'tb_faculty_crud', 'tb_faculty_nonce' ); ?>  
                <table class="form-table">  
                    <tr valign="top">  
                        <th scope="row"><label for="tb_faculty_name"><?php esc_html_e( 'اسم الكلية', 'tutor-branching' ); ?></label></th>  
                        <td>  
                            <input type="text" id="tb_faculty_name" name="tb_faculty_name" class="regular-text" required />  
                        </td>  
                    </tr>  
                    <tr valign="top">  
                        <th scope="row"><label for="tb_faculty_desc"><?php esc_html_e( 'الوصف (اختياري)', 'tutor-branching' ); ?></label></th>  
                        <td>  
                            <textarea id="tb_faculty_desc" name="tb_faculty_desc" rows="4" cols="50" class="large-text"></textarea>  
                        </td>  
                    </tr>  
                </table>  
                <p>  
                    <input type="submit" class="button button-primary" value="<?php echo esc_attr__( 'إضافة الكلية', 'tutor-branching' ); ?>">  
                </p>  
            </form>  

            <hr />  

            <h2><?php esc_html_e( 'قائمة الكليات المسجلة', 'tutor-branching' ); ?></h2>  
            <?php if ( ! empty( $terms ) && is_array( $terms ) ) : ?>  
                <ul>  
                    <?php foreach ( $terms as $term ) : ?>  
                        <li>  
                            <?php  
                            $name = isset( $term['name'] ) ? $term['name'] : '';  
                            $desc = isset( $term['desc'] ) ? $term['desc'] : '';  
                            $term_id = isset( $term['term_id'] ) ? intval( $term['term_id'] ) : 0;  
                            echo esc_html( $name );  
                            if ( $desc ) {  
                                echo ' - ' . esc_html( $desc );  
                            }  
                            // رابط حذف/تعديل بسيط (إذا أردت إضافة وظائف CRUD كاملة فسنوسع)  
                            if ( $term_id ) {  
                                                                $delete_url = wp_nonce_url( add_query_arg( array(
                                    'action'   => 'tb_delete_faculty',
                                    'term_id'  => $term_id,
                                ), admin_url( 'admin.php?page=tb-faculties' ) ), 'tb_delete_faculty_' . $term_id );
                                echo ' &nbsp; <a href="' . esc_url( $delete_url ) . '" onclick="return confirm(\'' . esc_js( __( 'هل أنت متأكد من الحذف؟', 'tutor-branching' ) ) . '\')">' . esc_html__( 'حذف', 'tutor-branching' ) . '</a>';
                            }
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e( 'لا توجد كليات مسجلة حتى الآن.', 'tutor-branching' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Handle deletion (called via admin_init hook)
     */
    public static function maybe_handle_actions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Handle delete action
        if ( isset( $_GET['action'] ) && 'tb_delete_faculty' === $_GET['action'] && isset( $_GET['term_id'] ) ) {
            $term_id = intval( $_GET['term_id'] );
            $nonce = 'tb_delete_faculty_' . $term_id;

            if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '', $nonce ) ) {
                wp_die( esc_html__( 'رمز الأمان غير صالح.', 'tutor-branching' ) );
            }

            // إما استخدام CRUD class أو wp_delete_term كبديل
            if ( class_exists( 'TB_Faculty_CRUD' ) && method_exists( 'TB_Faculty_CRUD', 'delete' ) ) {
                TB_Faculty_CRUD::delete( $term_id );
            } else {
                wp_delete_term( $term_id, 'tb_faculty' );
            }

            // إعادة توجيه بعد الحذف لتجنب إعادة التنفيذ عند إعادة تحميل الصفحة
            wp_safe_redirect( remove_query_arg( array( 'action', 'term_id', '_wpnonce' ), wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=tb-faculties' ) ) );
            exit;
        }
    }
}

// تسجيل القوائم والإجراءات عند التحميل
add_action( 'admin_menu', array( 'TB_Admin', 'tb_setup_menu' ) );
add_action( 'admin_init', array( 'TB_Admin', 'maybe_handle_actions' ) );