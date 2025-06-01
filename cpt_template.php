<?php
/**
 * Custom Post Type Template
 * 
 * This template creates a custom post type following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * Replace 'your_post_type' with your actual post type name.
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/reference/functions/register_post_type/
 * - https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
 * - https://developer.wordpress.org/reference/functions/add_action/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Custom Post Type Class
 * 
 * Handles registration and management of custom post types
 */
if ( ! class_exists( 'YOUR_PREFIX_Custom_Post_Type' ) ) {
    
    class YOUR_PREFIX_Custom_Post_Type {
        
        /**
         * Post type name
         */
        private $post_type = 'your_post_type';
        
        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'init', array( $this, 'register_post_type' ) );
            add_action( 'init', array( $this, 'register_meta_fields' ) );
            add_filter( 'enter_title_here', array( $this, 'change_title_placeholder' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
        }
        
        /**
         * Register the custom post type
         */
        public function register_post_type() {
            
            $labels = array(
                'name'                  => _x( 'Your Post Types', 'Post type general name', 'your-textdomain' ),
                'singular_name'         => _x( 'Your Post Type', 'Post type singular name', 'your-textdomain' ),
                'menu_name'             => _x( 'Your Post Types', 'Admin Menu text', 'your-textdomain' ),
                'name_admin_bar'        => _x( 'Your Post Type', 'Add New on Toolbar', 'your-textdomain' ),
                'add_new'               => __( 'Add New', 'your-textdomain' ),
                'add_new_item'          => __( 'Add New Your Post Type', 'your-textdomain' ),
                'new_item'              => __( 'New Your Post Type', 'your-textdomain' ),
                'edit_item'             => __( 'Edit Your Post Type', 'your-textdomain' ),
                'view_item'             => __( 'View Your Post Type', 'your-textdomain' ),
                'all_items'             => __( 'All Your Post Types', 'your-textdomain' ),
                'search_items'          => __( 'Search Your Post Types', 'your-textdomain' ),
                'parent_item_colon'     => __( 'Parent Your Post Types:', 'your-textdomain' ),
                'not_found'             => __( 'No Your Post Types found.', 'your-textdomain' ),
                'not_found_in_trash'    => __( 'No Your Post Types found in Trash.', 'your-textdomain' ),
                'featured_image'        => _x( 'Your Post Type Cover Image', 'Overrides the "Featured Image" phrase', 'your-textdomain' ),
                'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'your-textdomain' ),
                'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'your-textdomain' ),
                'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'your-textdomain' ),
                'archives'              => _x( 'Your Post Type archives', 'The post type archive label', 'your-textdomain' ),
                'insert_into_item'      => _x( 'Insert into Your Post Type', 'Overrides the "Insert into post" phrase', 'your-textdomain' ),
                'uploaded_to_this_item' => _x( 'Uploaded to this Your Post Type', 'Overrides the "Uploaded to this post" phrase', 'your-textdomain' ),
                'filter_items_list'     => _x( 'Filter Your Post Types list', 'Screen reader text for the filter links', 'your-textdomain' ),
                'items_list_navigation' => _x( 'Your Post Types list navigation', 'Screen reader text for the pagination', 'your-textdomain' ),
                'items_list'            => _x( 'Your Post Types list', 'Screen reader text for the items list', 'your-textdomain' ),
            );
            
            $args = array(
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_nav_menus'  => true,
                'show_in_admin_bar'  => true,
                'show_in_rest'       => true, // Enable Gutenberg editor
                'query_var'          => true,
                'rewrite'            => array( 'slug' => 'your-post-type' ),
                'capability_type'    => 'post',
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => null,
                'menu_icon'          => 'dashicons-admin-post', // Change to desired dashicon
                'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
            );
            
            register_post_type( $this->post_type, $args );
        }
        
        /**
         * Register meta fields for REST API
         */
        public function register_meta_fields() {
            register_post_meta( $this->post_type, 'your_prefix_custom_field', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'description' => 'Custom field description',
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => function() {
                    return current_user_can( 'edit_posts' );
                }
            ));
        }
        
        /**
         * Change title placeholder text
         */
        public function change_title_placeholder( $title ) {
            $screen = get_current_screen();
            
            if ( $this->post_type === $screen->post_type ) {
                $title = __( 'Enter Your Post Type title here', 'your-textdomain' );
            }
            
            return $title;
        }
        
        /**
         * Add meta boxes
         */
        public function add_meta_boxes() {
            add_meta_box(
                'your-prefix-meta-box',
                __( 'Your Post Type Details', 'your-textdomain' ),
                array( $this, 'meta_box_callback' ),
                $this->post_type,
                'normal',
                'high'
            );
        }
        
        /**
         * Meta box callback function
         */
        public function meta_box_callback( $post ) {
            // Add nonce for security
            wp_nonce_field( 'your_prefix_save_meta_box_data', 'your_prefix_meta_box_nonce' );
            
            // Get existing value
            $value = get_post_meta( $post->ID, 'your_prefix_custom_field', true );
            
            echo '<table class="form-table">';
            echo '<tr>';
            echo '<th><label for="your_prefix_custom_field">' . __( 'Custom Field', 'your-textdomain' ) . '</label></th>';
            echo '<td><input type="text" id="your_prefix_custom_field" name="your_prefix_custom_field" value="' . esc_attr( $value ) . '" size="25" /></td>';
            echo '</tr>';
            echo '</table>';
        }
        
        /**
         * Save meta box data
         */
        public function save_meta_box_data( $post_id ) {
            
            // Check if nonce is valid
            if ( ! isset( $_POST['your_prefix_meta_box_nonce'] ) ) {
                return;
            }
            
            if ( ! wp_verify_nonce( $_POST['your_prefix_meta_box_nonce'], 'your_prefix_save_meta_box_data' ) ) {
                return;
            }
            
            // Check if user has permissions to save data
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
            
            // Check if not an autosave
            if ( wp_is_post_autosave( $post_id ) ) {
                return;
            }
            
            // Check if not a revision
            if ( wp_is_post_revision( $post_id ) ) {
                return;
            }
            
            // Sanitize and save the data
            if ( isset( $_POST['your_prefix_custom_field'] ) ) {
                $custom_field_value = sanitize_text_field( $_POST['your_prefix_custom_field'] );
                update_post_meta( $post_id, 'your_prefix_custom_field', $custom_field_value );
            }
        }
        
        /**
         * Get posts of this type
         * 
         * @param array $args Query arguments
         * @return WP_Query
         */
        public static function get_posts( $args = array() ) {
            $defaults = array(
                'post_type' => 'your_post_type',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            );
            
            $args = wp_parse_args( $args, $defaults );
            
            return new WP_Query( $args );
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Custom_Post_Type();
}

/**
 * Helper function to get custom post type posts
 * 
 * @param array $args Query arguments
 * @return WP_Query
 */
if ( ! function_exists( 'your_prefix_get_post_type_posts' ) ) {
    function your_prefix_get_post_type_posts( $args = array() ) {
        return YOUR_PREFIX_Custom_Post_Type::get_posts( $args );
    }
}