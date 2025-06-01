<?php
/**
 * Assets Template
 * 
 * This template handles loading CSS and JavaScript assets following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/plugins/javascript/
 * - https://developer.wordpress.org/reference/functions/wp_register_style/
 * - https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 * - https://developer.wordpress.org/reference/functions/wp_register_script/
 * - https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 * - https://developer.wordpress.org/themes/basics/including-css-javascript/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Assets Handler Class
 * 
 * Handles registration and loading of CSS and JavaScript assets
 */
if ( ! class_exists( 'YOUR_PREFIX_Assets' ) ) {
    
    class YOUR_PREFIX_Assets {
        
        /**
         * Asset handle prefix
         */
        private $prefix = 'your-prefix-';
        
        /**
         * Plugin version
         */
        private $version = '1.0.0';
        
        /**
         * Assets directory URL
         */
        private $assets_url;
        
        /**
         * Constructor
         */
        public function __construct() {
            // Set assets URL
            $this->assets_url = plugin_dir_url( __FILE__ ) . 'assets/';
            
            // Register assets
            add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ) );
            
            // Enqueue assets based on context
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
            
            // Block editor assets
            add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
            
            // Add async/defer attributes to scripts (optional)
            add_filter( 'script_loader_tag', array( $this, 'add_script_attributes' ), 10, 3 );
            
            // Print inline styles in head (optional)
            add_action( 'wp_head', array( $this, 'print_custom_css' ), 30 );
            
            // Print custom footer scripts (optional)
            add_action( 'wp_footer', array( $this, 'print_footer_scripts' ), 99 );
        }
        
        /**
         * Register frontend assets
         */
        public function register_frontend_assets() {
            // Register styles
            wp_register_style(
                $this->prefix . 'main',
                $this->assets_url . 'css/frontend.css',
                array(), // Dependencies
                $this->version
            );
            
            wp_register_style(
                $this->prefix . 'lightbox',
                $this->assets_url . 'css/lightbox.css',
                array(), // Dependencies
                $this->version
            );
            
            // Register scripts
            wp_register_script(
                $this->prefix . 'main',
                $this->assets_url . 'js/frontend.js',
                array( 'jquery' ), // Dependencies
                $this->version,
                true // Load in footer
            );
            
            wp_register_script(
                $this->prefix . 'lightbox',
                $this->assets_url . 'js/lightbox.js',
                array( 'jquery' ),
                $this->version,
                true
            );
            
            // Register responsive styles with media queries
            wp_register_style(
                $this->prefix . 'responsive',
                $this->assets_url . 'css/responsive.css',
                array( $this->prefix . 'main' ), // Depend on main stylesheet
                $this->version,
                'screen and (max-width: 782px)' // Media query
            );
            
            // RTL support
            wp_register_style(
                $this->prefix . 'rtl',
                $this->assets_url . 'css/rtl.css',
                array( $this->prefix . 'main' ),
                $this->version
            );
        }
        
        /**
         * Register admin assets
         */
        public function register_admin_assets() {
            // Register admin styles
            wp_register_style(
                $this->prefix . 'admin',
                $this->assets_url . 'css/admin.css',
                array( 'wp-color-picker' ), // WordPress color picker dependency
                $this->version
            );
            
            // Register admin scripts
            wp_register_script(
                $this->prefix . 'admin',
                $this->assets_url . 'js/admin.js',
                array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), // Dependencies
                $this->version,
                true
            );
        }
        
        /**
         * Enqueue frontend assets
         */
        public function enqueue_frontend_assets() {
            // Always load main stylesheet and script on the frontend
            wp_enqueue_style( $this->prefix . 'main' );
            wp_enqueue_script( $this->prefix . 'main' );
            
            // Load responsive styles
            wp_enqueue_style( $this->prefix . 'responsive' );
            
            // Load RTL stylesheet if needed
            if ( is_rtl() ) {
                wp_enqueue_style( $this->prefix . 'rtl' );
            }
            
            // Conditionally load lightbox assets
            global $post;
            if ( is_singular() && is_a( $post, 'WP_Post' ) && 
                ( has_shortcode( $post->post_content, 'gallery' ) || has_shortcode( $post->post_content, 'your_gallery_shortcode' ) ) ) {
                wp_enqueue_style( $this->prefix . 'lightbox' );
                wp_enqueue_script( $this->prefix . 'lightbox' );
            }
            
            // Pass data to scripts via localization
            $localization_data = array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'your_prefix_nonce' ),
                'home_url' => home_url(),
                'site_title' => get_bloginfo( 'name' ),
                'is_user_logged_in' => is_user_logged_in(),
                'i18n' => array(
                    'close' => __( 'Close', 'your-textdomain' ),
                    'next' => __( 'Next', 'your-textdomain' ),
                    'previous' => __( 'Previous', 'your-textdomain' ),
                    'loading' => __( 'Loading...', 'your-textdomain' ),
                ),
            );
            
            wp_localize_script( $this->prefix . 'main', 'your_prefix_vars', $localization_data );
        }
        
        /**
         * Enqueue admin assets
         * 
         * @param string $hook Current admin page hook
         */
        public function enqueue_admin_assets( $hook ) {
            // Only load on specific admin pages
            $admin_pages = array(
                'post.php',
                'post-new.php',
                'toplevel_page_your-prefix-admin',
                'your-prefix_page_your-prefix-settings',
            );
            
            if ( ! in_array( $hook, $admin_pages ) ) {
                return;
            }
            
            // Enqueue admin styles and scripts
            wp_enqueue_style( $this->prefix . 'admin' );
            wp_enqueue_script( $this->prefix . 'admin' );
            
            // Enqueue WordPress media uploader if needed
            wp_enqueue_media();
            
            // Localize admin script
            wp_localize_script( $this->prefix . 'admin', 'your_prefix_admin', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'your_prefix_admin_nonce' ),
                'post_id' => get_the_ID(),
                'i18n' => array(
                    'save_success' => __( 'Saved successfully!', 'your-textdomain' ),
                    'save_error' => __( 'Error saving data.', 'your-textdomain' ),
                    'confirm_delete' => __( 'Are you sure you want to delete this item? This cannot be undone.', 'your-textdomain' ),
                    'upload_image' => __( 'Choose Image', 'your-textdomain' ),
                    'use_image' => __( 'Use This Image', 'your-textdomain' ),
                ),
            ) );
        }
        
        /**
         * Enqueue block editor assets
         */
        public function enqueue_block_editor_assets() {
            // Register and enqueue block editor assets
            wp_enqueue_style(
                $this->prefix . 'blocks-editor',
                $this->assets_url . 'css/blocks-editor.css',
                array( 'wp-edit-blocks' ), // Dependencies
                $this->version
            );
            
            wp_enqueue_script(
                $this->prefix . 'blocks',
                $this->assets_url . 'js/blocks.js',
                array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ), // Dependencies
                $this->version,
                true
            );
            
            // Pass data to blocks script
            $localization_data = array(
                'plugin_url' => $this->assets_url,
                'plugin_name' => __( 'Your Plugin Name', 'your-textdomain' ),
                'block_defaults' => array(
                    'background' => '#ffffff',
                    'text_color' => '#333333',
                ),
            );
            
            wp_localize_script( $this->prefix . 'blocks', 'your_prefix_blocks', $localization_data );
        }
        
        /**
         * Add async/defer attributes to scripts
         * 
         * @param string $tag HTML script tag
         * @param string $handle Script handle
         * @param string $src Script source URL
         * @return string Modified script tag
         */
        public function add_script_attributes( $tag, $handle, $src ) {
            // Scripts to load asynchronously
            $async_scripts = array(
                $this->prefix . 'lightbox',
                // Add other script handles here
            );
            
            // Scripts to load with defer attribute
            $defer_scripts = array(
                // Add script handles here
            );
            
            // Add async attribute
            if ( in_array( $handle, $async_scripts ) ) {
                return str_replace( ' src', ' async="async" src', $tag );
            }
            
            // Add defer attribute
            if ( in_array( $handle, $defer_scripts ) ) {
                return str_replace( ' src', ' defer="defer" src', $tag );
            }
            
            return $tag;
        }
        
        /**
         * Print custom CSS in head
         */
        public function print_custom_css() {
            // Get custom CSS from plugin settings
            $custom_css = get_option( 'your_prefix_custom_css', '' );
            
            if ( empty( $custom_css ) ) {
                return;
            }
            
            echo '<style type="text/css" id="your-prefix-custom-css">' . "\n";
            echo esc_html( $custom_css ) . "\n";
            echo '</style>' . "\n";
        }
        
        /**
         * Print footer scripts
         */
        public function print_footer_scripts() {
            // Get custom footer scripts from plugin settings
            $custom_scripts = get_option( 'your_prefix_footer_scripts', '' );
            
            if ( empty( $custom_scripts ) ) {
                return;
            }
            
            echo '<script type="text/javascript" id="your-prefix-footer-scripts">' . "\n";
            echo '/* <![CDATA[ */' . "\n";
            echo $custom_scripts . "\n"; // Not escaped as it needs to be valid JS
            echo '/* ]]> */' . "\n";
            echo '</script>' . "\n";
        }
        
        /**
         * Generate inline styles
         * 
         * @param array $styles Array of CSS rules
         * @return string CSS string
         */
        public static function generate_inline_styles( $styles ) {
            $css = '';
            
            foreach ( $styles as $selector => $properties ) {
                $css .= $selector . ' {' . "\n";
                
                foreach ( $properties as $property => $value ) {
                    $css .= '    ' . $property . ': ' . $value . ';' . "\n";
                }
                
                $css .= '}' . "\n";
            }
            
            return $css;
        }
        
        /**
         * Get plugin asset URL
         * 
         * @param string $file Relative path to the asset file
         * @return string Full URL to the asset
         */
        public static function get_asset_url( $file ) {
            return plugin_dir_url( __FILE__ ) . 'assets/' . $file;
        }
        
        /**
         * Register a custom font
         * 
         * @param string $font_name Font name
         * @param string $font_url Font URL
         * @param array $deps Dependencies
         */
        public static function register_font( $font_name, $font_url, $deps = array() ) {
            $handle = 'your-prefix-font-' . sanitize_title( $font_name );
            
            wp_register_style(
                $handle,
                $font_url,
                $deps,
                '1.0.0'
            );
            
            return $handle;
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Assets();
}

/**
 * Helper function to get asset URL
 * 
 * @param string $file Relative path to the asset file
 * @return string Full URL to the asset
 */
if ( ! function_exists( 'your_prefix_get_asset_url' ) ) {
    function your_prefix_get_asset_url( $file ) {
        return YOUR_PREFIX_Assets::get_asset_url( $file );
    }
}

/**
 * Helper function to generate inline styles
 * 
 * @param array $styles Array of CSS rules
 * @return string CSS string
 */
if ( ! function_exists( 'your_prefix_generate_inline_styles' ) ) {
    function your_prefix_generate_inline_styles( $styles ) {
        return YOUR_PREFIX_Assets::generate_inline_styles( $styles );
    }
} 