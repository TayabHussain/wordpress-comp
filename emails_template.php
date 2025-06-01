<?php
/**
 * Emails Template
 * 
 * This template creates email functionality following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/reference/functions/wp_mail/
 * - https://developer.wordpress.org/plugins/users/emails/
 * - https://developer.wordpress.org/reference/hooks/wp_mail_content_type/
 * - https://codex.wordpress.org/Plugin_API/Filter_Reference/wp_mail_from
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Emails Handler Class
 * 
 * Handles creation and sending of emails
 */
if ( ! class_exists( 'YOUR_PREFIX_Emails_Handler' ) ) {
    
    class YOUR_PREFIX_Emails_Handler {
        
        /**
         * Email templates directory
         */
        private $templates_dir;
        
        /**
         * Email from name
         */
        private $from_name;
        
        /**
         * Email from address
         */
        private $from_email;
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->templates_dir = plugin_dir_path( __FILE__ ) . 'templates/emails/';
            $this->from_name = get_option( 'your_prefix_email_from_name', get_bloginfo( 'name' ) );
            $this->from_email = get_option( 'your_prefix_email_from_email', get_option( 'admin_email' ) );
            
            // Add filters for email customization
            add_filter( 'wp_mail_from', array( $this, 'custom_mail_from' ) );
            add_filter( 'wp_mail_from_name', array( $this, 'custom_mail_from_name' ) );
            add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
            
            // Register settings
            add_action( 'admin_init', array( $this, 'register_settings' ) );
        }
        
        /**
         * Register email settings
         */
        public function register_settings() {
            register_setting( 'your_prefix_options', 'your_prefix_email_from_name', 'sanitize_text_field' );
            register_setting( 'your_prefix_options', 'your_prefix_email_from_email', 'sanitize_email' );
        }
        
        /**
         * Set custom from email
         * 
         * @param string $email Original from email
         * @return string Modified from email
         */
        public function custom_mail_from( $email ) {
            // Only change for plugin emails
            if ( isset( $GLOBALS['your_prefix_sending_email'] ) && $GLOBALS['your_prefix_sending_email'] ) {
                return $this->from_email;
            }
            
            return $email;
        }
        
        /**
         * Set custom from name
         * 
         * @param string $name Original from name
         * @return string Modified from name
         */
        public function custom_mail_from_name( $name ) {
            // Only change for plugin emails
            if ( isset( $GLOBALS['your_prefix_sending_email'] ) && $GLOBALS['your_prefix_sending_email'] ) {
                return $this->from_name;
            }
            
            return $name;
        }
        
        /**
         * Set HTML content type
         * 
         * @param string $content_type Original content type
         * @return string HTML content type
         */
        public function set_html_content_type( $content_type ) {
            // Only change for plugin emails
            if ( isset( $GLOBALS['your_prefix_sending_email'] ) && $GLOBALS['your_prefix_sending_email'] ) {
                return 'text/html';
            }
            
            return $content_type;
        }
        
        /**
         * Get email template
         * 
         * @param string $template Template name
         * @param array $args Template arguments
         * @return string Email HTML content
         */
        public function get_template( $template, $args = array() ) {
            $template_path = $this->templates_dir . $template . '.php';
            
            if ( ! file_exists( $template_path ) ) {
                return '';
            }
            
            // Extract args to make them available in the template
            if ( ! empty( $args ) && is_array( $args ) ) {
                extract( $args );
            }
            
            // Start output buffering
            ob_start();
            
            // Include template file
            include $template_path;
            
            // Get contents
            $content = ob_get_clean();
            
            return $content;
        }
        
        /**
         * Send email
         * 
         * @param string $to Recipient email address
         * @param string $subject Email subject
         * @param string $template Email template name
         * @param array $args Template arguments
         * @param array $attachments Email attachments
         * @param array $headers Additional headers
         * @return bool Whether the email was sent successfully
         */
        public function send_email( $to, $subject, $template, $args = array(), $attachments = array(), $headers = array() ) {
            // Set global flag to modify email headers
            $GLOBALS['your_prefix_sending_email'] = true;
            
            // Get email content from template
            $content = $this->get_template( $template, $args );
            
            if ( empty( $content ) ) {
                return false;
            }
            
            // Apply email wrapper
            $email_content = $this->apply_email_wrapper( $content, $subject );
            
            // Set default headers if none provided
            if ( empty( $headers ) ) {
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8'
                );
            }
            
            // Send email
            $result = wp_mail( $to, $subject, $email_content, $headers, $attachments );
            
            // Reset global flag
            unset( $GLOBALS['your_prefix_sending_email'] );
            
            return $result;
        }
        
        /**
         * Apply email wrapper (header/footer)
         * 
         * @param string $content Email content
         * @param string $subject Email subject
         * @return string Wrapped email content
         */
        private function apply_email_wrapper( $content, $subject ) {
            // Get header and footer
            $header = $this->get_template( 'header', array( 'subject' => $subject ) );
            $footer = $this->get_template( 'footer' );
            
            // Combine email parts
            $email_content = $header . $content . $footer;
            
            return $email_content;
        }
        
        /**
         * Send welcome email
         * 
         * @param string $to Recipient email address
         * @param string $name Recipient name
         * @return bool Whether the email was sent successfully
         */
        public function send_welcome_email( $to, $name ) {
            $subject = sprintf( __( 'Welcome to %s', 'your-textdomain' ), get_bloginfo( 'name' ) );
            
            $args = array(
                'name' => $name,
                'site_name' => get_bloginfo( 'name' ),
                'site_url' => get_bloginfo( 'url' ),
                'login_url' => wp_login_url(),
                'current_year' => date( 'Y' )
            );
            
            return $this->send_email( $to, $subject, 'welcome', $args );
        }
        
        /**
         * Send notification email
         * 
         * @param string $to Recipient email address
         * @param string $subject Email subject
         * @param string $message Email message
         * @return bool Whether the email was sent successfully
         */
        public function send_notification( $to, $subject, $message ) {
            $args = array(
                'message' => $message,
                'site_name' => get_bloginfo( 'name' ),
                'site_url' => get_bloginfo( 'url' ),
                'current_year' => date( 'Y' )
            );
            
            return $this->send_email( $to, $subject, 'notification', $args );
        }
        
        /**
         * Send password reset email
         * 
         * @param string $to Recipient email address
         * @param string $name Recipient name
         * @param string $reset_url Password reset URL
         * @return bool Whether the email was sent successfully
         */
        public function send_password_reset( $to, $name, $reset_url ) {
            $subject = sprintf( __( 'Password Reset for %s', 'your-textdomain' ), get_bloginfo( 'name' ) );
            
            $args = array(
                'name' => $name,
                'site_name' => get_bloginfo( 'name' ),
                'site_url' => get_bloginfo( 'url' ),
                'reset_url' => $reset_url,
                'expiration_time' => '24 hours',
                'current_year' => date( 'Y' )
            );
            
            return $this->send_email( $to, $subject, 'password-reset', $args );
        }
        
        /**
         * Create email template files
         * 
         * This method is called on plugin activation to create default email templates
         */
        public static function create_email_templates() {
            $templates_dir = plugin_dir_path( __FILE__ ) . 'templates/emails/';
            
            // Create templates directory if it doesn't exist
            if ( ! file_exists( $templates_dir ) ) {
                wp_mkdir_p( $templates_dir );
            }
            
            // Create default header template
            $header_template = $templates_dir . 'header.php';
            if ( ! file_exists( $header_template ) ) {
                $header_content = '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo esc_html( $subject ); ?></title>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e0e0e0;
        }
        .email-header {
            background-color: #f8f8f8;
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        .email-content {
            padding: 20px;
        }
        .email-footer {
            background-color: #f8f8f8;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #777777;
            border-top: 1px solid #e0e0e0;
        }
        h1, h2, h3, h4 {
            color: #333333;
            margin-top: 0;
        }
        a {
            color: #0073aa;
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #0073aa;
            color: #ffffff !important;
            font-weight: normal;
            font-size: 14px;
            line-height: 1.4;
            text-decoration: none;
            border-radius: 3px;
            padding: 10px 15px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h2><?php echo esc_html( get_bloginfo( \'name\' ) ); ?></h2>
        </div>
        <div class="email-content">';
                
                file_put_contents( $header_template, $header_content );
            }
            
            // Create default footer template
            $footer_template = $templates_dir . 'footer.php';
            if ( ! file_exists( $footer_template ) ) {
                $footer_content = '        </div>
        <div class="email-footer">
            <p>&copy; <?php echo date( \'Y\' ); ?> <?php echo esc_html( get_bloginfo( \'name\' ) ); ?>. All rights reserved.</p>
            <p><a href="<?php echo esc_url( get_bloginfo( \'url\' ) ); ?>"><?php echo esc_html( get_bloginfo( \'name\' ) ); ?></a></p>
        </div>
    </div>
</body>
</html>';
                
                file_put_contents( $footer_template, $footer_content );
            }
            
            // Create welcome email template
            $welcome_template = $templates_dir . 'welcome.php';
            if ( ! file_exists( $welcome_template ) ) {
                $welcome_content = '<h2><?php _e( \'Welcome to our website!\', \'your-textdomain\' ); ?></h2>

<p><?php printf( __( \'Hello %s,\', \'your-textdomain\' ), esc_html( $name ) ); ?></p>

<p><?php printf( __( \'Thank you for registering at %s. We\'re excited to have you as a member!\', \'your-textdomain\' ), esc_html( $site_name ) ); ?></p>

<p><?php _e( \'You can log in with your credentials at the following URL:\', \'your-textdomain\' ); ?></p>

<p><a href="<?php echo esc_url( $login_url ); ?>" class="button"><?php _e( \'Log In\', \'your-textdomain\' ); ?></a></p>

<p><?php _e( \'If you have any questions, please don\'t hesitate to contact us.\', \'your-textdomain\' ); ?></p>

<p><?php _e( \'Thanks,\', \'your-textdomain\' ); ?><br>
<?php echo esc_html( $site_name ); ?></p>';
                
                file_put_contents( $welcome_template, $welcome_content );
            }
            
            // Create notification email template
            $notification_template = $templates_dir . 'notification.php';
            if ( ! file_exists( $notification_template ) ) {
                $notification_content = '<h2><?php _e( \'Notification\', \'your-textdomain\' ); ?></h2>

<p><?php echo wp_kses_post( $message ); ?></p>

<p><?php _e( \'Thanks,\', \'your-textdomain\' ); ?><br>
<?php echo esc_html( $site_name ); ?></p>';
                
                file_put_contents( $notification_template, $notification_content );
            }
            
            // Create password reset email template
            $password_reset_template = $templates_dir . 'password-reset.php';
            if ( ! file_exists( $password_reset_template ) ) {
                $password_reset_content = '<h2><?php _e( \'Password Reset\', \'your-textdomain\' ); ?></h2>

<p><?php printf( __( \'Hello %s,\', \'your-textdomain\' ), esc_html( $name ) ); ?></p>

<p><?php printf( __( \'Someone has requested a password reset for your account on %s.\', \'your-textdomain\' ), esc_html( $site_name ) ); ?></p>

<p><?php printf( __( \'If this was a mistake, just ignore this email and nothing will happen. To reset your password, click the button below and follow the instructions.\', \'your-textdomain\' ) ); ?></p>

<p><?php printf( __( \'Please note that this link will expire in %s.\', \'your-textdomain\' ), esc_html( $expiration_time ) ); ?></p>

<p style="text-align: center;">
    <a href="<?php echo esc_url( $reset_url ); ?>" class="button"><?php _e( \'Reset Password\', \'your-textdomain\' ); ?></a>
</p>

<p><?php _e( \'If you didn\'t request this, please ignore this email.\', \'your-textdomain\' ); ?></p>

<p><?php _e( \'Thanks,\', \'your-textdomain\' ); ?><br>
<?php echo esc_html( $site_name ); ?></p>';
                
                file_put_contents( $password_reset_template, $password_reset_content );
            }
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Emails_Handler();
}

/**
 * Helper function to send welcome email
 * 
 * @param string $to Recipient email address
 * @param string $name Recipient name
 * @return bool Whether the email was sent successfully
 */
if ( ! function_exists( 'your_prefix_send_welcome_email' ) ) {
    function your_prefix_send_welcome_email( $to, $name ) {
        $emails_handler = new YOUR_PREFIX_Emails_Handler();
        return $emails_handler->send_welcome_email( $to, $name );
    }
}

/**
 * Helper function to send notification email
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message
 * @return bool Whether the email was sent successfully
 */
if ( ! function_exists( 'your_prefix_send_notification' ) ) {
    function your_prefix_send_notification( $to, $subject, $message ) {
        $emails_handler = new YOUR_PREFIX_Emails_Handler();
        return $emails_handler->send_notification( $to, $subject, $message );
    }
}

/**
 * Helper function to send password reset email
 * 
 * @param string $to Recipient email address
 * @param string $name Recipient name
 * @param string $reset_url Password reset URL
 * @return bool Whether the email was sent successfully
 */
if ( ! function_exists( 'your_prefix_send_password_reset' ) ) {
    function your_prefix_send_password_reset( $to, $name, $reset_url ) {
        $emails_handler = new YOUR_PREFIX_Emails_Handler();
        return $emails_handler->send_password_reset( $to, $name, $reset_url );
    }
} 