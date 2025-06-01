<?php
/**
 * Forms Template
 * 
 * This template creates form handling functionality following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/plugins/security/nonces/
 * - https://developer.wordpress.org/plugins/security/securing-input/
 * - https://developer.wordpress.org/plugins/security/securing-output/
 * - https://developer.wordpress.org/reference/functions/wp_nonce_field/
 * - https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Forms Handler Class
 * 
 * Handles creation and processing of forms
 */
if ( ! class_exists( 'YOUR_PREFIX_Forms_Handler' ) ) {
    
    class YOUR_PREFIX_Forms_Handler {
        
        /**
         * Form actions
         */
        private $actions = array(
            'your_prefix_contact_form',
            'your_prefix_subscribe_form'
        );
        
        /**
         * Constructor
         */
        public function __construct() {
            // Register form submission handlers
            foreach ( $this->actions as $action ) {
                add_action( 'admin_post_' . $action, array( $this, 'handle_form_submission' ) );
                add_action( 'admin_post_nopriv_' . $action, array( $this, 'handle_form_submission' ) );
            }
            
            // Add shortcodes for forms
            add_shortcode( 'your_prefix_contact_form', array( $this, 'render_contact_form' ) );
            add_shortcode( 'your_prefix_subscribe_form', array( $this, 'render_subscribe_form' ) );
            
            // Enqueue form scripts and styles
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_form_assets' ) );
        }
        
        /**
         * Handle form submissions
         */
        public function handle_form_submission() {
            $action = isset( $_POST['action'] ) ? sanitize_key( $_POST['action'] ) : '';
            
            if ( ! in_array( $action, $this->actions ) ) {
                wp_die( __( 'Invalid form action.', 'your-textdomain' ), __( 'Error', 'your-textdomain' ), array( 'response' => 403 ) );
            }
            
            // Verify nonce
            if ( ! isset( $_POST['your_prefix_form_nonce'] ) || ! wp_verify_nonce( $_POST['your_prefix_form_nonce'], $action ) ) {
                wp_die( __( 'Security check failed.', 'your-textdomain' ), __( 'Error', 'your-textdomain' ), array( 'response' => 403 ) );
            }
            
            // Process based on form type
            switch ( $action ) {
                case 'your_prefix_contact_form':
                    $this->process_contact_form();
                    break;
                    
                case 'your_prefix_subscribe_form':
                    $this->process_subscribe_form();
                    break;
                    
                default:
                    wp_die( __( 'Unknown form type.', 'your-textdomain' ), __( 'Error', 'your-textdomain' ), array( 'response' => 403 ) );
            }
        }
        
        /**
         * Process contact form submission
         */
        private function process_contact_form() {
            // Sanitize and validate inputs
            $name = isset( $_POST['your_prefix_name'] ) ? sanitize_text_field( $_POST['your_prefix_name'] ) : '';
            $email = isset( $_POST['your_prefix_email'] ) ? sanitize_email( $_POST['your_prefix_email'] ) : '';
            $subject = isset( $_POST['your_prefix_subject'] ) ? sanitize_text_field( $_POST['your_prefix_subject'] ) : '';
            $message = isset( $_POST['your_prefix_message'] ) ? sanitize_textarea_field( $_POST['your_prefix_message'] ) : '';
            
            // Validate required fields
            $errors = array();
            
            if ( empty( $name ) ) {
                $errors[] = __( 'Name is required.', 'your-textdomain' );
            }
            
            if ( empty( $email ) ) {
                $errors[] = __( 'Email is required.', 'your-textdomain' );
            } elseif ( ! is_email( $email ) ) {
                $errors[] = __( 'Email is not valid.', 'your-textdomain' );
            }
            
            if ( empty( $message ) ) {
                $errors[] = __( 'Message is required.', 'your-textdomain' );
            }
            
            // Check for errors
            if ( ! empty( $errors ) ) {
                $redirect_url = add_query_arg( array(
                    'your_prefix_errors' => urlencode( implode( '|', $errors ) ),
                    'form' => 'contact'
                ), wp_get_referer() );
                
                wp_safe_redirect( $redirect_url );
                exit;
            }
            
            // Process form (e.g., send email, save to database)
            $to = get_option( 'admin_email' );
            $subject = sprintf( __( 'Contact Form: %s', 'your-textdomain' ), $subject );
            $email_message = sprintf( __( "Name: %s\nEmail: %s\n\nMessage:\n%s", 'your-textdomain' ), $name, $email, $message );
            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'From: ' . sprintf( '%s <%s>', $name, $email ),
                'Reply-To: ' . $email
            );
            
            $mail_sent = wp_mail( $to, $subject, $email_message, $headers );
            
            // Optional: Save to database
            $this->save_form_submission( array(
                'type' => 'contact',
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'status' => $mail_sent ? 'sent' : 'failed'
            ) );
            
            // Redirect with success/error message
            $redirect_url = add_query_arg( array(
                'your_prefix_status' => $mail_sent ? 'success' : 'error',
                'form' => 'contact'
            ), wp_get_referer() );
            
            wp_safe_redirect( $redirect_url );
            exit;
        }
        
        /**
         * Process subscribe form submission
         */
        private function process_subscribe_form() {
            // Sanitize and validate inputs
            $email = isset( $_POST['your_prefix_email'] ) ? sanitize_email( $_POST['your_prefix_email'] ) : '';
            $name = isset( $_POST['your_prefix_name'] ) ? sanitize_text_field( $_POST['your_prefix_name'] ) : '';
            
            // Validate required fields
            $errors = array();
            
            if ( empty( $email ) ) {
                $errors[] = __( 'Email is required.', 'your-textdomain' );
            } elseif ( ! is_email( $email ) ) {
                $errors[] = __( 'Email is not valid.', 'your-textdomain' );
            }
            
            // Check for errors
            if ( ! empty( $errors ) ) {
                $redirect_url = add_query_arg( array(
                    'your_prefix_errors' => urlencode( implode( '|', $errors ) ),
                    'form' => 'subscribe'
                ), wp_get_referer() );
                
                wp_safe_redirect( $redirect_url );
                exit;
            }
            
            // Save subscription
            $subscription_saved = $this->save_subscription( array(
                'email' => $email,
                'name' => $name,
                'date' => current_time( 'mysql' )
            ) );
            
            // Redirect with success/error message
            $redirect_url = add_query_arg( array(
                'your_prefix_status' => $subscription_saved ? 'success' : 'error',
                'form' => 'subscribe'
            ), wp_get_referer() );
            
            wp_safe_redirect( $redirect_url );
            exit;
        }
        
        /**
         * Save form submission to database
         * 
         * @param array $data Form data
         * @return int|false The ID of the inserted row, or false on failure
         */
        private function save_form_submission( $data ) {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'your_prefix_form_submissions';
            
            $result = $wpdb->insert(
                $table_name,
                array(
                    'form_type' => $data['type'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'subject' => isset( $data['subject'] ) ? $data['subject'] : '',
                    'message' => isset( $data['message'] ) ? $data['message'] : '',
                    'status' => $data['status'],
                    'created_at' => current_time( 'mysql' )
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );
            
            return $result ? $wpdb->insert_id : false;
        }
        
        /**
         * Save subscription to database
         * 
         * @param array $data Subscription data
         * @return int|false The ID of the inserted row, or false on failure
         */
        private function save_subscription( $data ) {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'your_prefix_subscriptions';
            
            // Check if email already exists
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE email = %s",
                $data['email']
            ) );
            
            if ( $exists ) {
                return false; // Email already exists
            }
            
            $result = $wpdb->insert(
                $table_name,
                array(
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'created_at' => current_time( 'mysql' ),
                    'status' => 'active'
                ),
                array( '%s', '%s', '%s', '%s' )
            );
            
            return $result ? $wpdb->insert_id : false;
        }
        
        /**
         * Render contact form
         * 
         * @param array $atts Shortcode attributes
         * @return string Form HTML
         */
        public function render_contact_form( $atts ) {
            $atts = shortcode_atts( array(
                'title' => __( 'Contact Us', 'your-textdomain' ),
                'class' => '',
                'id' => '',
                'redirect' => '',
            ), $atts, 'your_prefix_contact_form' );
            
            // Sanitize attributes
            $atts['title'] = sanitize_text_field( $atts['title'] );
            $atts['class'] = sanitize_html_class( $atts['class'] );
            $atts['id'] = sanitize_html_class( $atts['id'] );
            $atts['redirect'] = esc_url_raw( $atts['redirect'] );
            
            // Check for messages
            $form_status = isset( $_GET['your_prefix_status'] ) && $_GET['form'] === 'contact' ? sanitize_key( $_GET['your_prefix_status'] ) : '';
            $form_errors = isset( $_GET['your_prefix_errors'] ) && $_GET['form'] === 'contact' ? explode( '|', urldecode( $_GET['your_prefix_errors'] ) ) : array();
            
            // Start output buffer
            ob_start();
            
            // Build CSS classes
            $css_classes = array( 'your-prefix-form', 'your-prefix-contact-form' );
            if ( ! empty( $atts['class'] ) ) {
                $css_classes[] = $atts['class'];
            }
            $css_class = implode( ' ', $css_classes );
            
            // Generate unique ID if not provided
            $unique_id = ! empty( $atts['id'] ) ? $atts['id'] : 'your-prefix-contact-form-' . uniqid();
            ?>
            <div id="<?php echo esc_attr( $unique_id ); ?>" class="<?php echo esc_attr( $css_class ); ?>">
                <?php if ( ! empty( $atts['title'] ) ) : ?>
                    <h3 class="form-title"><?php echo esc_html( $atts['title'] ); ?></h3>
                <?php endif; ?>
                
                <?php if ( $form_status === 'success' ) : ?>
                    <div class="your-prefix-form-message your-prefix-form-success">
                        <p><?php _e( 'Thank you for your message. We will get back to you as soon as possible.', 'your-textdomain' ); ?></p>
                    </div>
                <?php elseif ( $form_status === 'error' ) : ?>
                    <div class="your-prefix-form-message your-prefix-form-error">
                        <p><?php _e( 'There was a problem sending your message. Please try again later.', 'your-textdomain' ); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $form_errors ) ) : ?>
                    <div class="your-prefix-form-errors">
                        <ul>
                            <?php foreach ( $form_errors as $error ) : ?>
                                <li><?php echo esc_html( $error ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'your_prefix_contact_form', 'your_prefix_form_nonce' ); ?>
                    <input type="hidden" name="action" value="your_prefix_contact_form">
                    <?php if ( ! empty( $atts['redirect'] ) ) : ?>
                        <input type="hidden" name="redirect" value="<?php echo esc_url( $atts['redirect'] ); ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <label for="your-prefix-name"><?php _e( 'Your Name', 'your-textdomain' ); ?> <span class="required">*</span></label>
                        <input type="text" id="your-prefix-name" name="your_prefix_name" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="your-prefix-email"><?php _e( 'Your Email', 'your-textdomain' ); ?> <span class="required">*</span></label>
                        <input type="email" id="your-prefix-email" name="your_prefix_email" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="your-prefix-subject"><?php _e( 'Subject', 'your-textdomain' ); ?></label>
                        <input type="text" id="your-prefix-subject" name="your_prefix_subject">
                    </div>
                    
                    <div class="form-row">
                        <label for="your-prefix-message"><?php _e( 'Message', 'your-textdomain' ); ?> <span class="required">*</span></label>
                        <textarea id="your-prefix-message" name="your_prefix_message" rows="5" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <button type="submit" class="your-prefix-form-submit"><?php _e( 'Send Message', 'your-textdomain' ); ?></button>
                    </div>
                </form>
            </div>
            <?php
            return ob_get_clean();
        }
        
        /**
         * Render subscribe form
         * 
         * @param array $atts Shortcode attributes
         * @return string Form HTML
         */
        public function render_subscribe_form( $atts ) {
            $atts = shortcode_atts( array(
                'title' => __( 'Subscribe to Our Newsletter', 'your-textdomain' ),
                'class' => '',
                'id' => '',
                'redirect' => '',
                'show_name' => 'yes',
            ), $atts, 'your_prefix_subscribe_form' );
            
            // Sanitize attributes
            $atts['title'] = sanitize_text_field( $atts['title'] );
            $atts['class'] = sanitize_html_class( $atts['class'] );
            $atts['id'] = sanitize_html_class( $atts['id'] );
            $atts['redirect'] = esc_url_raw( $atts['redirect'] );
            $atts['show_name'] = in_array( $atts['show_name'], array( 'yes', 'no' ) ) ? $atts['show_name'] : 'yes';
            
            // Check for messages
            $form_status = isset( $_GET['your_prefix_status'] ) && $_GET['form'] === 'subscribe' ? sanitize_key( $_GET['your_prefix_status'] ) : '';
            $form_errors = isset( $_GET['your_prefix_errors'] ) && $_GET['form'] === 'subscribe' ? explode( '|', urldecode( $_GET['your_prefix_errors'] ) ) : array();
            
            // Start output buffer
            ob_start();
            
            // Build CSS classes
            $css_classes = array( 'your-prefix-form', 'your-prefix-subscribe-form' );
            if ( ! empty( $atts['class'] ) ) {
                $css_classes[] = $atts['class'];
            }
            $css_class = implode( ' ', $css_classes );
            
            // Generate unique ID if not provided
            $unique_id = ! empty( $atts['id'] ) ? $atts['id'] : 'your-prefix-subscribe-form-' . uniqid();
            ?>
            <div id="<?php echo esc_attr( $unique_id ); ?>" class="<?php echo esc_attr( $css_class ); ?>">
                <?php if ( ! empty( $atts['title'] ) ) : ?>
                    <h3 class="form-title"><?php echo esc_html( $atts['title'] ); ?></h3>
                <?php endif; ?>
                
                <?php if ( $form_status === 'success' ) : ?>
                    <div class="your-prefix-form-message your-prefix-form-success">
                        <p><?php _e( 'Thank you for subscribing to our newsletter!', 'your-textdomain' ); ?></p>
                    </div>
                <?php elseif ( $form_status === 'error' ) : ?>
                    <div class="your-prefix-form-message your-prefix-form-error">
                        <p><?php _e( 'There was a problem with your subscription. This email may already be subscribed.', 'your-textdomain' ); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $form_errors ) ) : ?>
                    <div class="your-prefix-form-errors">
                        <ul>
                            <?php foreach ( $form_errors as $error ) : ?>
                                <li><?php echo esc_html( $error ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'your_prefix_subscribe_form', 'your_prefix_form_nonce' ); ?>
                    <input type="hidden" name="action" value="your_prefix_subscribe_form">
                    <?php if ( ! empty( $atts['redirect'] ) ) : ?>
                        <input type="hidden" name="redirect" value="<?php echo esc_url( $atts['redirect'] ); ?>">
                    <?php endif; ?>
                    
                    <?php if ( $atts['show_name'] === 'yes' ) : ?>
                        <div class="form-row">
                            <label for="your-prefix-name"><?php _e( 'Your Name', 'your-textdomain' ); ?></label>
                            <input type="text" id="your-prefix-name" name="your_prefix_name">
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <label for="your-prefix-email"><?php _e( 'Your Email', 'your-textdomain' ); ?> <span class="required">*</span></label>
                        <input type="email" id="your-prefix-email" name="your_prefix_email" required>
                    </div>
                    
                    <div class="form-row">
                        <button type="submit" class="your-prefix-form-submit"><?php _e( 'Subscribe', 'your-textdomain' ); ?></button>
                    </div>
                </form>
            </div>
            <?php
            return ob_get_clean();
        }
        
        /**
         * Enqueue form assets
         */
        public function enqueue_form_assets() {
            wp_enqueue_style( 
                'your-prefix-forms-style', 
                plugin_dir_url( __FILE__ ) . 'css/forms.css', 
                array(), 
                '1.0.0' 
            );
            
            wp_enqueue_script( 
                'your-prefix-forms-script', 
                plugin_dir_url( __FILE__ ) . 'js/forms.js', 
                array( 'jquery' ), 
                '1.0.0', 
                true 
            );
            
            wp_localize_script( 'your-prefix-forms-script', 'your_prefix_forms', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'your_prefix_forms_nonce' ),
                'messages' => array(
                    'required' => __( 'This field is required.', 'your-textdomain' ),
                    'email' => __( 'Please enter a valid email address.', 'your-textdomain' ),
                )
            ));
        }
        
        /**
         * Create database tables on plugin activation
         */
        public static function create_tables() {
            global $wpdb;
            
            $charset_collate = $wpdb->get_charset_collate();
            
            // Form submissions table
            $submissions_table = $wpdb->prefix . 'your_prefix_form_submissions';
            
            $sql = "CREATE TABLE $submissions_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                form_type varchar(50) NOT NULL,
                name varchar(100) NOT NULL,
                email varchar(100) NOT NULL,
                subject varchar(255) NOT NULL,
                message text NOT NULL,
                status varchar(20) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            // Subscriptions table
            $subscriptions_table = $wpdb->prefix . 'your_prefix_subscriptions';
            
            $sql .= "CREATE TABLE $subscriptions_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                email varchar(100) NOT NULL,
                name varchar(100) NOT NULL,
                created_at datetime NOT NULL,
                status varchar(20) NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY email (email)
            ) $charset_collate;";
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Forms_Handler();
}

/**
 * Helper function to render contact form
 * 
 * @param array $atts Form attributes
 * @return string Form HTML
 */
if ( ! function_exists( 'your_prefix_render_contact_form' ) ) {
    function your_prefix_render_contact_form( $atts = array() ) {
        $forms_handler = new YOUR_PREFIX_Forms_Handler();
        return $forms_handler->render_contact_form( $atts );
    }
}

/**
 * Helper function to render subscribe form
 * 
 * @param array $atts Form attributes
 * @return string Form HTML
 */
if ( ! function_exists( 'your_prefix_render_subscribe_form' ) ) {
    function your_prefix_render_subscribe_form( $atts = array() ) {
        $forms_handler = new YOUR_PREFIX_Forms_Handler();
        return $forms_handler->render_subscribe_form( $atts );
    }
} 