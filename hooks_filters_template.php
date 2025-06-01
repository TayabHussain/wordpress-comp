<?php
/**
 * Hooks & Filters Template
 * 
 * This template creates custom hooks and filters following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/plugins/hooks/
 * - https://developer.wordpress.org/reference/functions/add_action/
 * - https://developer.wordpress.org/reference/functions/add_filter/
 * - https://developer.wordpress.org/plugins/hooks/custom-hooks/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Hooks and Filters Handler Class
 * 
 * Handles registration and management of custom hooks and filters
 */
if ( ! class_exists( 'YOUR_PREFIX_Hooks_Filters' ) ) {
    
    class YOUR_PREFIX_Hooks_Filters {
        
        /**
         * Hook prefix
         */
        private $hook_prefix = 'your_prefix_';
        
        /**
         * Constructor
         */
        public function __construct() {
            // Register WordPress core hooks
            $this->register_core_hooks();
            
            // Register custom hooks
            $this->register_custom_hooks();
        }
        
        /**
         * Register WordPress core hooks
         */
        private function register_core_hooks() {
            // WordPress initialization
            add_action( 'init', array( $this, 'init_callback' ) );
            
            // Admin initialization
            add_action( 'admin_init', array( $this, 'admin_init_callback' ) );
            
            // Enqueue scripts and styles
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            
            // Plugin activation/deactivation
            register_activation_hook( YOUR_PREFIX_PLUGIN_FILE, array( $this, 'activation_callback' ) );
            register_deactivation_hook( YOUR_PREFIX_PLUGIN_FILE, array( $this, 'deactivation_callback' ) );
            
            // Content filters
            add_filter( 'the_content', array( $this, 'filter_content' ) );
            add_filter( 'the_title', array( $this, 'filter_title' ) );
            add_filter( 'the_excerpt', array( $this, 'filter_excerpt' ) );
            
            // AJAX handlers
            add_action( 'wp_ajax_your_prefix_ajax_action', array( $this, 'ajax_handler' ) );
            add_action( 'wp_ajax_nopriv_your_prefix_ajax_action', array( $this, 'ajax_handler' ) );
            
            // REST API filters
            add_filter( 'rest_prepare_post', array( $this, 'filter_rest_response' ), 10, 3 );
            
            // Post type and taxonomy hooks
            add_action( 'save_post', array( $this, 'save_post_callback' ), 10, 3 );
            add_action( 'pre_get_posts', array( $this, 'modify_query' ) );
            add_filter( 'manage_your_post_type_posts_columns', array( $this, 'add_custom_columns' ) );
            add_action( 'manage_your_post_type_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
            
            // Widget hooks
            add_action( 'widgets_init', array( $this, 'register_widgets' ) );
            
            // Shortcode hooks
            add_shortcode( 'your_shortcode', array( $this, 'shortcode_callback' ) );
            
            // User and authentication hooks
            add_action( 'user_register', array( $this, 'user_register_callback' ) );
            add_action( 'wp_login', array( $this, 'user_login_callback' ), 10, 2 );
            
            // Email hooks
            add_filter( 'wp_mail_from', array( $this, 'custom_mail_from' ) );
            add_filter( 'wp_mail_from_name', array( $this, 'custom_mail_from_name' ) );
            add_filter( 'wp_mail_content_type', array( $this, 'custom_mail_content_type' ) );
            
            // Cron hooks
            add_action( 'your_prefix_cron_hook', array( $this, 'cron_job_callback' ) );
            
            // Plugin integration hooks
            add_action( 'woocommerce_init', array( $this, 'woocommerce_integration' ) );
            add_filter( 'gform_pre_submission', array( $this, 'gravity_forms_integration' ) );
        }
        
        /**
         * Register custom hooks for this plugin
         */
        private function register_custom_hooks() {
            // Example of registering a custom hook that other plugins can use
            add_action( $this->hook_prefix . 'before_process', array( $this, 'before_process_callback' ) );
            add_action( $this->hook_prefix . 'after_process', array( $this, 'after_process_callback' ) );
            
            // Example of a custom filter
            add_filter( $this->hook_prefix . 'modify_data', array( $this, 'modify_data_callback' ) );
        }
        
        /**
         * WordPress init callback
         */
        public function init_callback() {
            // Code to run during WordPress initialization
            
            // Register custom post types, taxonomies, etc.
            
            // Load plugin text domain for internationalization
            load_plugin_textdomain( 'your-textdomain', false, dirname( plugin_basename( YOUR_PREFIX_PLUGIN_FILE ) ) . '/languages/' );
        }
        
        /**
         * WordPress admin init callback
         */
        public function admin_init_callback() {
            // Code to run during admin initialization
            
            // Register settings, etc.
        }
        
        /**
         * Enqueue frontend scripts and styles
         */
        public function enqueue_scripts() {
            // Enqueue frontend styles
            wp_enqueue_style(
                'your-prefix-style',
                plugin_dir_url( YOUR_PREFIX_PLUGIN_FILE ) . 'assets/css/frontend.css',
                array(),
                YOUR_PREFIX_VERSION
            );
            
            // Enqueue frontend scripts
            wp_enqueue_script(
                'your-prefix-script',
                plugin_dir_url( YOUR_PREFIX_PLUGIN_FILE ) . 'assets/js/frontend.js',
                array( 'jquery' ),
                YOUR_PREFIX_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script(
                'your-prefix-script',
                'your_prefix_vars',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'your_prefix_nonce' ),
                    'i18n'     => array(
                        'confirm' => __( 'Are you sure?', 'your-textdomain' ),
                        'error'   => __( 'An error occurred.', 'your-textdomain' ),
                    ),
                )
            );
        }
        
        /**
         * Enqueue admin scripts and styles
         */
        public function admin_enqueue_scripts( $hook ) {
            // Only load on specific admin pages if needed
            if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ) ) ) {
                return;
            }
            
            // Enqueue admin styles
            wp_enqueue_style(
                'your-prefix-admin-style',
                plugin_dir_url( YOUR_PREFIX_PLUGIN_FILE ) . 'assets/css/admin.css',
                array(),
                YOUR_PREFIX_VERSION
            );
            
            // Enqueue admin scripts
            wp_enqueue_script(
                'your-prefix-admin-script',
                plugin_dir_url( YOUR_PREFIX_PLUGIN_FILE ) . 'assets/js/admin.js',
                array( 'jquery' ),
                YOUR_PREFIX_VERSION,
                true
            );
            
            // Localize admin script
            wp_localize_script(
                'your-prefix-admin-script',
                'your_prefix_admin_vars',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'your_prefix_admin_nonce' ),
                    'i18n'     => array(
                        'confirm' => __( 'Are you sure?', 'your-textdomain' ),
                        'error'   => __( 'An error occurred.', 'your-textdomain' ),
                    ),
                )
            );
        }
        
        /**
         * Plugin activation callback
         */
        public function activation_callback() {
            // Code to run on plugin activation
            
            // Create database tables
            $this->create_tables();
            
            // Set default options
            $this->set_default_options();
            
            // Register custom post types and flush rewrite rules
            flush_rewrite_rules();
            
            // Schedule cron jobs
            if ( ! wp_next_scheduled( 'your_prefix_cron_hook' ) ) {
                wp_schedule_event( time(), 'daily', 'your_prefix_cron_hook' );
            }
        }
        
        /**
         * Plugin deactivation callback
         */
        public function deactivation_callback() {
            // Code to run on plugin deactivation
            
            // Clean up
            
            // Clear scheduled cron jobs
            wp_clear_scheduled_hook( 'your_prefix_cron_hook' );
            
            // Flush rewrite rules
            flush_rewrite_rules();
        }
        
        /**
         * Create custom database tables
         */
        private function create_tables() {
            global $wpdb;
            
            $charset_collate = $wpdb->get_charset_collate();
            
            $table_name = $wpdb->prefix . 'your_prefix_table';
            
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                name tinytext NOT NULL,
                text text NOT NULL,
                url varchar(55) DEFAULT '' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
        
        /**
         * Set default plugin options
         */
        private function set_default_options() {
            $default_options = array(
                'option_1' => 'default_value',
                'option_2' => true,
                'option_3' => 10,
            );
            
            foreach ( $default_options as $option => $value ) {
                if ( get_option( 'your_prefix_' . $option ) === false ) {
                    update_option( 'your_prefix_' . $option, $value );
                }
            }
        }
        
        /**
         * Filter content hook
         */
        public function filter_content( $content ) {
            // Only modify content for specific post types if needed
            if ( ! is_singular( 'your_post_type' ) ) {
                return $content;
            }
            
            // Modify content
            $content = '<div class="your-prefix-content-wrapper">' . $content . '</div>';
            
            // Allow further modification through custom filter
            return apply_filters( $this->hook_prefix . 'filtered_content', $content );
        }
        
        /**
         * Filter title hook
         */
        public function filter_title( $title ) {
            // Only modify title for specific post types if needed
            if ( ! is_singular( 'your_post_type' ) ) {
                return $title;
            }
            
            // Modify title
            // Example: Add a prefix to the title
            $title = __( 'Custom: ', 'your-textdomain' ) . $title;
            
            // Allow further modification through custom filter
            return apply_filters( $this->hook_prefix . 'filtered_title', $title );
        }
        
        /**
         * Filter excerpt hook
         */
        public function filter_excerpt( $excerpt ) {
            // Only modify excerpt for specific post types if needed
            if ( ! is_singular( 'your_post_type' ) ) {
                return $excerpt;
            }
            
            // Modify excerpt
            $excerpt = '<div class="your-prefix-excerpt">' . $excerpt . '</div>';
            
            // Allow further modification through custom filter
            return apply_filters( $this->hook_prefix . 'filtered_excerpt', $excerpt );
        }
        
        /**
         * AJAX handler
         */
        public function ajax_handler() {
            // Check nonce for security
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'your_prefix_nonce' ) ) {
                wp_send_json_error( array( 'message' => __( 'Security check failed.', 'your-textdomain' ) ) );
            }
            
            // Check required fields
            if ( ! isset( $_POST['action_type'] ) ) {
                wp_send_json_error( array( 'message' => __( 'Missing required fields.', 'your-textdomain' ) ) );
            }
            
            $action_type = sanitize_text_field( $_POST['action_type'] );
            
            // Handle different action types
            switch ( $action_type ) {
                case 'get_data':
                    // Handle get data
                    $data = $this->get_ajax_data();
                    wp_send_json_success( $data );
                    break;
                    
                case 'save_data':
                    // Handle save data
                    $result = $this->save_ajax_data( $_POST );
                    if ( $result ) {
                        wp_send_json_success( array( 'message' => __( 'Data saved successfully.', 'your-textdomain' ) ) );
                    } else {
                        wp_send_json_error( array( 'message' => __( 'Failed to save data.', 'your-textdomain' ) ) );
                    }
                    break;
                    
                default:
                    wp_send_json_error( array( 'message' => __( 'Invalid action type.', 'your-textdomain' ) ) );
            }
            
            // Always exit after handling AJAX
            wp_die();
        }
        
        /**
         * Get AJAX data
         */
        private function get_ajax_data() {
            // Example data
            $data = array(
                'items' => array(
                    array( 'id' => 1, 'name' => 'Item 1' ),
                    array( 'id' => 2, 'name' => 'Item 2' ),
                ),
                'count' => 2,
            );
            
            // Allow modification through filter
            return apply_filters( $this->hook_prefix . 'ajax_data', $data );
        }
        
        /**
         * Save AJAX data
         */
        private function save_ajax_data( $posted_data ) {
            // Process and save data
            $item_name = isset( $posted_data['item_name'] ) ? sanitize_text_field( $posted_data['item_name'] ) : '';
            
            // Do something with the data
            // ...
            
            // Trigger custom action after saving
            do_action( $this->hook_prefix . 'after_save_ajax_data', $item_name, $posted_data );
            
            return true;
        }
        
        /**
         * Filter REST API response
         */
        public function filter_rest_response( $response, $post, $request ) {
            // Only modify specific post types
            if ( $post->post_type !== 'your_post_type' ) {
                return $response;
            }
            
            // Add custom fields to response
            $data = $response->get_data();
            
            // Add custom field value
            $custom_field = get_post_meta( $post->ID, 'your_prefix_custom_field', true );
            $data['custom_field'] = $custom_field;
            
            // Add more fields as needed
            
            // Update the response
            $response->set_data( $data );
            
            return $response;
        }
        
        /**
         * Save post callback
         */
        public function save_post_callback( $post_id, $post, $update ) {
            // Check if we're doing an autosave
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }
            
            // Check if this is the correct post type
            if ( $post->post_type !== 'your_post_type' ) {
                return;
            }
            
            // Check permissions
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
            
            // Check if our nonce is set
            if ( ! isset( $_POST['your_prefix_nonce'] ) ) {
                return;
            }
            
            // Verify the nonce
            if ( ! wp_verify_nonce( $_POST['your_prefix_nonce'], 'your_prefix_save_post' ) ) {
                return;
            }
            
            // Save custom fields
            if ( isset( $_POST['your_prefix_custom_field'] ) ) {
                $custom_field = sanitize_text_field( $_POST['your_prefix_custom_field'] );
                update_post_meta( $post_id, 'your_prefix_custom_field', $custom_field );
            }
            
            // Trigger action after saving
            do_action( $this->hook_prefix . 'after_save_post', $post_id, $post, $update );
        }
        
        /**
         * Modify query hook
         */
        public function modify_query( $query ) {
            // Only modify main query on frontend
            if ( ! is_admin() && $query->is_main_query() ) {
                
                // Modify archives for a specific post type
                if ( $query->is_post_type_archive( 'your_post_type' ) ) {
                    $query->set( 'posts_per_page', 20 );
                    $query->set( 'orderby', 'title' );
                    $query->set( 'order', 'ASC' );
                }
                
                // Modify taxonomy archives
                if ( $query->is_tax( 'your_taxonomy' ) ) {
                    $query->set( 'posts_per_page', 10 );
                }
            }
            
            return $query;
        }
        
        /**
         * Add custom admin columns
         */
        public function add_custom_columns( $columns ) {
            $new_columns = array();
            
            // Add columns at specific positions
            foreach ( $columns as $key => $value ) {
                $new_columns[$key] = $value;
                
                // Add custom column after title
                if ( $key === 'title' ) {
                    $new_columns['your_prefix_custom_column'] = __( 'Custom Field', 'your-textdomain' );
                }
            }
            
            // Add column at the end
            $new_columns['your_prefix_another_column'] = __( 'Another Custom Field', 'your-textdomain' );
            
            return $new_columns;
        }
        
        /**
         * Custom column content
         */
        public function custom_column_content( $column, $post_id ) {
            switch ( $column ) {
                case 'your_prefix_custom_column':
                    $value = get_post_meta( $post_id, 'your_prefix_custom_field', true );
                    echo esc_html( $value );
                    break;
                    
                case 'your_prefix_another_column':
                    $value = get_post_meta( $post_id, 'your_prefix_another_field', true );
                    echo esc_html( $value );
                    break;
            }
        }
        
        /**
         * Register widgets
         */
        public function register_widgets() {
            register_widget( 'YOUR_PREFIX_Widget' );
        }
        
        /**
         * Shortcode callback
         */
        public function shortcode_callback( $atts, $content = null ) {
            // Parse shortcode attributes
            $atts = shortcode_atts(
                array(
                    'title' => '',
                    'count' => 5,
                ),
                $atts,
                'your_shortcode'
            );
            
            // Process the shortcode
            ob_start();
            
            echo '<div class="your-prefix-shortcode">';
            
            if ( ! empty( $atts['title'] ) ) {
                echo '<h3>' . esc_html( $atts['title'] ) . '</h3>';
            }
            
            // Add content
            echo '<div class="your-prefix-shortcode-content">';
            echo do_shortcode( $content );
            echo '</div>';
            
            echo '</div>';
            
            return ob_get_clean();
        }
        
        /**
         * User register callback
         */
        public function user_register_callback( $user_id ) {
            // Do something when a new user registers
            
            // Example: Add custom user meta
            update_user_meta( $user_id, 'your_prefix_user_registration_date', current_time( 'mysql' ) );
            
            // Trigger custom action
            do_action( $this->hook_prefix . 'after_user_register', $user_id );
        }
        
        /**
         * User login callback
         */
        public function user_login_callback( $user_login, $user ) {
            // Do something when a user logs in
            
            // Example: Update last login time
            update_user_meta( $user->ID, 'your_prefix_last_login', current_time( 'mysql' ) );
            
            // Trigger custom action
            do_action( $this->hook_prefix . 'after_user_login', $user->ID, $user );
        }
        
        /**
         * Custom mail from filter
         */
        public function custom_mail_from( $from_email ) {
            // Change the from email for specific emails
            if ( isset( $GLOBALS['your_prefix_sending_email'] ) && $GLOBALS['your_prefix_sending_email'] ) {
                return get_option( 'your_prefix_email_from', get_option( 'admin_email' ) );
            }
            
            return $from_email;
        }
        
        /**
         * Custom mail from name filter
         */
        public function custom_mail_from_name( $from_name ) {
            // Change the from name for specific emails
            if ( isset( $GLOBALS['your_prefix_sending_email'] ) && $GLOBALS['your_prefix_sending_email'] ) {
                return get_option( 'your_prefix_email_from_name', get_bloginfo( 'name' ) );
            }
            
            return $from_name;
        }
        
        /**
         * Custom mail content type filter
         */
        public function custom_mail_content_type( $content_type ) {
            // Change the content type for specific emails
            if ( isset( $GLOBALS['your_prefix_sending_email'] ) && $GLOBALS['your_prefix_sending_email'] ) {
                return 'text/html';
            }
            
            return $content_type;
        }
        
        /**
         * Cron job callback
         */
        public function cron_job_callback() {
            // Do something on scheduled intervals
            
            // Example: Clean up old data
            $this->cleanup_old_data();
            
            // Trigger custom action
            do_action( $this->hook_prefix . 'after_cron_job' );
        }
        
        /**
         * Cleanup old data
         */
        private function cleanup_old_data() {
            global $wpdb;
            
            // Example: Delete old items from custom table
            $table_name = $wpdb->prefix . 'your_prefix_table';
            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
            
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $table_name WHERE time < %s",
                    $cutoff_date
                )
            );
        }
        
        /**
         * WooCommerce integration
         */
        public function woocommerce_integration() {
            // Add WooCommerce specific hooks
            add_action( 'woocommerce_before_single_product', array( $this, 'before_product_callback' ) );
            add_action( 'woocommerce_after_single_product', array( $this, 'after_product_callback' ) );
            add_filter( 'woocommerce_product_tabs', array( $this, 'add_product_tab' ) );
        }
        
        /**
         * Before product callback
         */
        public function before_product_callback() {
            // Do something before the product
            echo '<div class="your-prefix-product-wrapper">';
        }
        
        /**
         * After product callback
         */
        public function after_product_callback() {
            // Do something after the product
            echo '</div>';
        }
        
        /**
         * Add product tab
         */
        public function add_product_tab( $tabs ) {
            // Add a new tab
            $tabs['your_prefix_tab'] = array(
                'title'    => __( 'Custom Tab', 'your-textdomain' ),
                'priority' => 50,
                'callback' => array( $this, 'product_tab_content' ),
            );
            
            return $tabs;
        }
        
        /**
         * Product tab content
         */
        public function product_tab_content() {
            // Tab content
            echo '<h2>' . __( 'Custom Tab Content', 'your-textdomain' ) . '</h2>';
            echo '<p>' . __( 'This is the custom tab content.', 'your-textdomain' ) . '</p>';
        }
        
        /**
         * Gravity Forms integration
         */
        public function gravity_forms_integration( $form ) {
            // Do something with the form submission
            
            return $form;
        }
        
        /**
         * Custom hook: Before process callback
         */
        public function before_process_callback() {
            // This method is called when the custom hook is triggered
            // do_action( 'your_prefix_before_process' );
        }
        
        /**
         * Custom hook: After process callback
         */
        public function after_process_callback() {
            // This method is called when the custom hook is triggered
            // do_action( 'your_prefix_after_process' );
        }
        
        /**
         * Custom filter: Modify data callback
         */
        public function modify_data_callback( $data ) {
            // This method is called when the custom filter is used
            // $modified_data = apply_filters( 'your_prefix_modify_data', $data );
            
            // Modify the data
            if ( is_array( $data ) ) {
                $data['modified'] = true;
                $data['timestamp'] = current_time( 'timestamp' );
            }
            
            return $data;
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Hooks_Filters();
}

/**
 * Helper functions for custom hooks and filters
 */

/**
 * Execute custom action
 * 
 * @param mixed $data Optional data to pass to the hooks
 */
if ( ! function_exists( 'your_prefix_do_process' ) ) {
    function your_prefix_do_process( $data = null ) {
        // Fire before process action
        do_action( 'your_prefix_before_process', $data );
        
        // Process data
        $result = true; // Example result
        
        // Fire after process action with result
        do_action( 'your_prefix_after_process', $data, $result );
        
        return $result;
    }
}

/**
 * Apply custom filter to data
 * 
 * @param mixed $data Data to filter
 * @return mixed Filtered data
 */
if ( ! function_exists( 'your_prefix_filter_data' ) ) {
    function your_prefix_filter_data( $data ) {
        return apply_filters( 'your_prefix_modify_data', $data );
    }
}

/**
 * Get filtered content
 * 
 * @param string $content Original content
 * @return string Filtered content
 */
if ( ! function_exists( 'your_prefix_get_filtered_content' ) ) {
    function your_prefix_get_filtered_content( $content ) {
        return apply_filters( 'your_prefix_filtered_content', $content );
    }
} 