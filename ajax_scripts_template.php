<?php
/**
 * AJAX & Scripts Template
 * 
 * This template handles AJAX requests and script loading following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/plugins/javascript/
 * - https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 * - https://developer.wordpress.org/reference/functions/wp_localize_script/
 * - https://developer.wordpress.org/reference/hooks/wp_ajax_action/
 * - https://developer.wordpress.org/reference/functions/check_ajax_referer/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * AJAX Handler Class
 * 
 * Handles AJAX requests and script enqueueing
 */
if ( ! class_exists( 'YOUR_PREFIX_Ajax_Handler' ) ) {
    
    class YOUR_PREFIX_Ajax_Handler {
        
        /**
         * AJAX action prefix
         */
        private $action_prefix = 'your_prefix_';
        
        /**
         * Script handle prefix
         */
        private $script_prefix = 'your-prefix-';
        
        /**
         * Constructor
         */
        public function __construct() {
            // Register scripts
            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
            
            // Register AJAX handlers for logged-in users
            add_action( 'wp_ajax_' . $this->action_prefix . 'get_data', array( $this, 'ajax_get_data' ) );
            add_action( 'wp_ajax_' . $this->action_prefix . 'save_data', array( $this, 'ajax_save_data' ) );
            add_action( 'wp_ajax_' . $this->action_prefix . 'delete_item', array( $this, 'ajax_delete_item' ) );
            
            // Register AJAX handlers for non-logged-in users (public)
            add_action( 'wp_ajax_nopriv_' . $this->action_prefix . 'get_public_data', array( $this, 'ajax_get_public_data' ) );
            
            // Custom script loading based on conditions
            add_action( 'wp', array( $this, 'maybe_enqueue_scripts' ) );
            
            // Inline script for critical rendering path (optional)
            add_action( 'wp_head', array( $this, 'add_inline_script' ), 20 );
        }
        
        /**
         * Register scripts for frontend
         */
        public function register_scripts() {
            // Main plugin script
            wp_register_script(
                $this->script_prefix . 'main',
                plugin_dir_url( __FILE__ ) . 'js/frontend.js',
                array( 'jquery' ),
                '1.0.0',
                true // Load in footer
            );
            
            // Localize script with data and AJAX variables
            wp_localize_script(
                $this->script_prefix . 'main',
                'your_prefix_vars',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( $this->action_prefix . 'nonce' ),
                    'i18n' => array(
                        'success' => __( 'Success!', 'your-textdomain' ),
                        'error' => __( 'An error occurred.', 'your-textdomain' ),
                        'confirm' => __( 'Are you sure you want to do this?', 'your-textdomain' ),
                    ),
                    'home_url' => home_url(),
                    'user_logged_in' => is_user_logged_in(),
                )
            );
            
            // Additional scripts
            wp_register_script(
                $this->script_prefix . 'lightbox',
                plugin_dir_url( __FILE__ ) . 'js/lightbox.js',
                array( 'jquery' ),
                '1.0.0',
                true
            );
            
            // Conditional script - only load when needed
            wp_register_script(
                $this->script_prefix . 'slider',
                plugin_dir_url( __FILE__ ) . 'js/slider.js',
                array( 'jquery' ),
                '1.0.0',
                true
            );
        }
        
        /**
         * Register admin scripts
         */
        public function register_admin_scripts( $hook ) {
            // Only load on specific admin pages
            if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'toplevel_page_your-prefix-admin' ) ) ) {
                return;
            }
            
            // Admin script
            wp_enqueue_script(
                $this->script_prefix . 'admin',
                plugin_dir_url( __FILE__ ) . 'js/admin.js',
                array( 'jquery', 'wp-util', 'jquery-ui-sortable' ),
                '1.0.0',
                true
            );
            
            // Localize admin script
            wp_localize_script(
                $this->script_prefix . 'admin',
                'your_prefix_admin',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( $this->action_prefix . 'admin_nonce' ),
                    'i18n' => array(
                        'save_success' => __( 'Saved successfully!', 'your-textdomain' ),
                        'save_error' => __( 'Error saving data.', 'your-textdomain' ),
                        'confirm_delete' => __( 'Are you sure you want to delete this item?', 'your-textdomain' ),
                    ),
                    'post_id' => get_the_ID(),
                )
            );
            
            // Enqueue WordPress media uploader if needed
            wp_enqueue_media();
        }
        
        /**
         * Conditionally enqueue scripts based on page or post
         */
        public function maybe_enqueue_scripts() {
            // Only load on single posts or specific pages
            if ( is_singular( 'post' ) ) {
                wp_enqueue_script( $this->script_prefix . 'main' );
            }
            
            // Load lightbox on posts with galleries
            global $post;
            if ( is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'gallery' ) ) {
                wp_enqueue_script( $this->script_prefix . 'lightbox' );
            }
            
            // Load slider on homepage
            if ( is_front_page() ) {
                wp_enqueue_script( $this->script_prefix . 'slider' );
            }
        }
        
        /**
         * Add critical inline script
         */
        public function add_inline_script() {
            if ( ! is_singular() ) {
                return;
            }
            
            // Critical path CSS or JS (keep this small)
            ?>
            <script type="text/javascript">
                // Critical path code that needs to run before page load
                document.documentElement.className = document.documentElement.className.replace('no-js', 'js');
            </script>
            <?php
        }
        
        /**
         * AJAX handler for getting data (logged-in users)
         */
        public function ajax_get_data() {
            // Verify nonce
            check_ajax_referer( $this->action_prefix . 'nonce', 'nonce' );
            
            // Check user permissions
            if ( ! current_user_can( 'read' ) ) {
                wp_send_json_error( array(
                    'message' => __( 'You do not have permission to perform this action.', 'your-textdomain' ),
                ) );
            }
            
            // Get data
            $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
            
            if ( $post_id <= 0 ) {
                wp_send_json_error( array(
                    'message' => __( 'Invalid post ID.', 'your-textdomain' ),
                ) );
            }
            
            $post = get_post( $post_id );
            
            if ( ! $post ) {
                wp_send_json_error( array(
                    'message' => __( 'Post not found.', 'your-textdomain' ),
                ) );
            }
            
            // Return success response
            wp_send_json_success( array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_date' => $post->post_date,
            ) );
        }
        
        /**
         * AJAX handler for saving data (logged-in users)
         */
        public function ajax_save_data() {
            // Verify nonce
            check_ajax_referer( $this->action_prefix . 'nonce', 'nonce' );
            
            // Check user permissions
            if ( ! current_user_can( 'edit_posts' ) ) {
                wp_send_json_error( array(
                    'message' => __( 'You do not have permission to perform this action.', 'your-textdomain' ),
                ) );
            }
            
            // Sanitize and validate input
            $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
            $title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
            $content = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';
            
            if ( empty( $title ) || empty( $content ) ) {
                wp_send_json_error( array(
                    'message' => __( 'Title and content are required.', 'your-textdomain' ),
                ) );
            }
            
            // Update post
            $post_data = array(
                'ID' => $post_id,
                'post_title' => $title,
                'post_content' => $content,
            );
            
            $result = wp_update_post( $post_data, true );
            
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( array(
                    'message' => $result->get_error_message(),
                ) );
            }
            
            // Return success response
            wp_send_json_success( array(
                'message' => __( 'Post updated successfully.', 'your-textdomain' ),
                'post_id' => $post_id,
            ) );
        }
        
        /**
         * AJAX handler for deleting an item (logged-in users)
         */
        public function ajax_delete_item() {
            // Verify nonce
            check_ajax_referer( $this->action_prefix . 'nonce', 'nonce' );
            
            // Check user permissions
            if ( ! current_user_can( 'delete_posts' ) ) {
                wp_send_json_error( array(
                    'message' => __( 'You do not have permission to perform this action.', 'your-textdomain' ),
                ) );
            }
            
            // Validate input
            $item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
            
            if ( $item_id <= 0 ) {
                wp_send_json_error( array(
                    'message' => __( 'Invalid item ID.', 'your-textdomain' ),
                ) );
            }
            
            // Delete post
            $result = wp_delete_post( $item_id, true );
            
            if ( ! $result ) {
                wp_send_json_error( array(
                    'message' => __( 'Failed to delete item.', 'your-textdomain' ),
                ) );
            }
            
            // Return success response
            wp_send_json_success( array(
                'message' => __( 'Item deleted successfully.', 'your-textdomain' ),
                'item_id' => $item_id,
            ) );
        }
        
        /**
         * AJAX handler for getting public data (non-logged-in users)
         */
        public function ajax_get_public_data() {
            // Verify nonce for public requests
            check_ajax_referer( $this->action_prefix . 'nonce', 'nonce' );
            
            // Get data
            $query_args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 5,
            );
            
            $posts = get_posts( $query_args );
            
            if ( empty( $posts ) ) {
                wp_send_json_error( array(
                    'message' => __( 'No posts found.', 'your-textdomain' ),
                ) );
            }
            
            $response_data = array();
            
            foreach ( $posts as $post ) {
                $response_data[] = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'excerpt' => get_the_excerpt( $post ),
                    'url' => get_permalink( $post ),
                );
            }
            
            // Return success response
            wp_send_json_success( array(
                'posts' => $response_data,
            ) );
        }
        
        /**
         * Example JavaScript for AJAX calls
         * 
         * This method doesn't output anything, it's just a reference for frontend JS
         */
        private function example_javascript() {
            // This is just an example, not actually used
            $js = <<<JAVASCRIPT
// Example AJAX request
jQuery(document).ready(function($) {
    // Get data
    $('#your-prefix-get-data').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: your_prefix_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'your_prefix_get_data',
                nonce: your_prefix_vars.nonce,
                post_id: 123
            },
            success: function(response) {
                if (response.success) {
                    console.log(response.data);
                } else {
                    alert(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert(your_prefix_vars.i18n.error);
            }
        });
    });
    
    // Save data
    $('#your-prefix-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: your_prefix_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'your_prefix_save_data',
                nonce: your_prefix_vars.nonce,
                post_id: 123,
                title: $('#title').val(),
                content: $('#content').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert(your_prefix_vars.i18n.error);
            }
        });
    });
});
JAVASCRIPT;
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Ajax_Handler();
}

/**
 * Helper function to execute an AJAX request programmatically
 * 
 * @param string $action AJAX action name
 * @param array $data Data to send
 * @param bool $admin Whether to use admin-ajax or not
 * @return array Response
 */
if ( ! function_exists( 'your_prefix_do_ajax_request' ) ) {
    function your_prefix_do_ajax_request( $action, $data = array(), $admin = true ) {
        // Save original request variables
        $original_request = $_REQUEST;
        
        // Set up request for ajax handler
        $_REQUEST = array_merge( $data, array(
            'action' => $action,
            'nonce' => wp_create_nonce( 'your_prefix_nonce' ),
        ) );
        
        // Prevent direct output
        ob_start();
        
        // Call ajax handler
        if ( $admin ) {
            do_action( 'wp_ajax_' . $action );
        } else {
            do_action( 'wp_ajax_nopriv_' . $action );
        }
        
        // Get response
        $response = ob_get_clean();
        
        // Restore original request variables
        $_REQUEST = $original_request;
        
        // Decode JSON response
        return json_decode( $response, true );
    }
} 