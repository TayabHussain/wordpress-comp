<?php
/**
 * Admin Page Template
 * 
 * This template creates a custom admin page following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/plugins/administration-menus/
 * - https://developer.wordpress.org/reference/functions/add_menu_page/
 * - https://developer.wordpress.org/reference/functions/add_submenu_page/
 * - https://developer.wordpress.org/plugins/settings/custom-settings-page/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Admin Page Class
 * 
 * Handles registration and management of admin pages
 */
if ( ! class_exists( 'YOUR_PREFIX_Admin_Pages' ) ) {
    
    class YOUR_PREFIX_Admin_Pages {
        
        /**
         * Menu slug for the main menu
         */
        private $main_menu_slug = 'your-prefix-admin';
        
        /**
         * Options group name
         */
        private $option_group = 'your_prefix_options';
        
        /**
         * Option name in database
         */
        private $option_name = 'your_prefix_settings';
        
        /**
         * Stored options
         */
        private $options;
        
        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'admin_menu', array( $this, 'register_menus' ) );
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
            
            // Get stored options
            $this->options = get_option( $this->option_name, array() );
        }
        
        /**
         * Register admin menus
         */
        public function register_menus() {
            // Add main menu page
            add_menu_page(
                __( 'Your Plugin Name', 'your-textdomain' ),
                __( 'Your Plugin', 'your-textdomain' ),
                'manage_options',
                $this->main_menu_slug,
                array( $this, 'main_page_callback' ),
                'dashicons-admin-generic', // Change to desired dashicon
                30 // Menu position
            );
            
            // Add settings submenu page
            add_submenu_page(
                $this->main_menu_slug,
                __( 'Settings', 'your-textdomain' ),
                __( 'Settings', 'your-textdomain' ),
                'manage_options',
                $this->main_menu_slug . '-settings',
                array( $this, 'settings_page_callback' )
            );
            
            // Add another submenu page
            add_submenu_page(
                $this->main_menu_slug,
                __( 'Tools', 'your-textdomain' ),
                __( 'Tools', 'your-textdomain' ),
                'manage_options',
                $this->main_menu_slug . '-tools',
                array( $this, 'tools_page_callback' )
            );
            
            // If you want to add submenu under existing WP menu
            // Example: Add under Settings menu
            add_options_page(
                __( 'Your Plugin Settings', 'your-textdomain' ),
                __( 'Your Plugin', 'your-textdomain' ),
                'manage_options',
                'your-prefix-options',
                array( $this, 'options_page_callback' )
            );
        }
        
        /**
         * Register settings
         */
        public function register_settings() {
            // Register settings group
            register_setting(
                $this->option_group,
                $this->option_name,
                array( $this, 'sanitize_options' )
            );
            
            // General settings section
            add_settings_section(
                'your_prefix_general_section',
                __( 'General Settings', 'your-textdomain' ),
                array( $this, 'general_section_callback' ),
                $this->main_menu_slug . '-settings'
            );
            
            // Fields for general section
            add_settings_field(
                'your_prefix_enable_feature',
                __( 'Enable Feature', 'your-textdomain' ),
                array( $this, 'checkbox_field_callback' ),
                $this->main_menu_slug . '-settings',
                'your_prefix_general_section',
                array(
                    'id'    => 'enable_feature',
                    'label' => __( 'Enable this feature', 'your-textdomain' ),
                    'description' => __( 'Check to enable this feature.', 'your-textdomain' ),
                )
            );
            
            add_settings_field(
                'your_prefix_text_option',
                __( 'Text Option', 'your-textdomain' ),
                array( $this, 'text_field_callback' ),
                $this->main_menu_slug . '-settings',
                'your_prefix_general_section',
                array(
                    'id'    => 'text_option',
                    'label' => __( 'Text option', 'your-textdomain' ),
                    'description' => __( 'Enter your text option.', 'your-textdomain' ),
                    'placeholder' => __( 'Enter text here', 'your-textdomain' ),
                )
            );
            
            add_settings_field(
                'your_prefix_select_option',
                __( 'Select Option', 'your-textdomain' ),
                array( $this, 'select_field_callback' ),
                $this->main_menu_slug . '-settings',
                'your_prefix_general_section',
                array(
                    'id'    => 'select_option',
                    'label' => __( 'Select an option', 'your-textdomain' ),
                    'description' => __( 'Choose one of the available options.', 'your-textdomain' ),
                    'options' => array(
                        'option1' => __( 'Option 1', 'your-textdomain' ),
                        'option2' => __( 'Option 2', 'your-textdomain' ),
                        'option3' => __( 'Option 3', 'your-textdomain' ),
                    ),
                )
            );
            
            // Advanced settings section
            add_settings_section(
                'your_prefix_advanced_section',
                __( 'Advanced Settings', 'your-textdomain' ),
                array( $this, 'advanced_section_callback' ),
                $this->main_menu_slug . '-settings'
            );
            
            // Fields for advanced section
            add_settings_field(
                'your_prefix_textarea_option',
                __( 'Textarea Option', 'your-textdomain' ),
                array( $this, 'textarea_field_callback' ),
                $this->main_menu_slug . '-settings',
                'your_prefix_advanced_section',
                array(
                    'id'    => 'textarea_option',
                    'label' => __( 'Textarea option', 'your-textdomain' ),
                    'description' => __( 'Enter your textarea content.', 'your-textdomain' ),
                    'placeholder' => __( 'Enter text here', 'your-textdomain' ),
                    'rows' => 5,
                    'cols' => 50,
                )
            );
            
            add_settings_field(
                'your_prefix_radio_option',
                __( 'Radio Option', 'your-textdomain' ),
                array( $this, 'radio_field_callback' ),
                $this->main_menu_slug . '-settings',
                'your_prefix_advanced_section',
                array(
                    'id'    => 'radio_option',
                    'label' => __( 'Radio option', 'your-textdomain' ),
                    'description' => __( 'Choose one of the available options.', 'your-textdomain' ),
                    'options' => array(
                        'radio1' => __( 'Radio 1', 'your-textdomain' ),
                        'radio2' => __( 'Radio 2', 'your-textdomain' ),
                        'radio3' => __( 'Radio 3', 'your-textdomain' ),
                    ),
                )
            );
            
            add_settings_field(
                'your_prefix_color_option',
                __( 'Color Option', 'your-textdomain' ),
                array( $this, 'color_field_callback' ),
                $this->main_menu_slug . '-settings',
                'your_prefix_advanced_section',
                array(
                    'id'    => 'color_option',
                    'label' => __( 'Color option', 'your-textdomain' ),
                    'description' => __( 'Choose a color.', 'your-textdomain' ),
                    'default' => '#ffffff',
                )
            );
        }
        
        /**
         * Sanitize options
         */
        public function sanitize_options( $options ) {
            $sanitized = array();
            
            // Sanitize checkbox
            $sanitized['enable_feature'] = isset( $options['enable_feature'] ) ? 1 : 0;
            
            // Sanitize text field
            if ( isset( $options['text_option'] ) ) {
                $sanitized['text_option'] = sanitize_text_field( $options['text_option'] );
            }
            
            // Sanitize select field
            if ( isset( $options['select_option'] ) ) {
                $sanitized['select_option'] = sanitize_key( $options['select_option'] );
            }
            
            // Sanitize textarea
            if ( isset( $options['textarea_option'] ) ) {
                $sanitized['textarea_option'] = sanitize_textarea_field( $options['textarea_option'] );
            }
            
            // Sanitize radio
            if ( isset( $options['radio_option'] ) ) {
                $sanitized['radio_option'] = sanitize_key( $options['radio_option'] );
            }
            
            // Sanitize color
            if ( isset( $options['color_option'] ) ) {
                $sanitized['color_option'] = sanitize_hex_color( $options['color_option'] );
            }
            
            return $sanitized;
        }
        
        /**
         * General section callback
         */
        public function general_section_callback() {
            echo '<p>' . __( 'Configure the general settings for your plugin.', 'your-textdomain' ) . '</p>';
        }
        
        /**
         * Advanced section callback
         */
        public function advanced_section_callback() {
            echo '<p>' . __( 'Configure the advanced settings for your plugin.', 'your-textdomain' ) . '</p>';
        }
        
        /**
         * Main page callback
         */
        public function main_page_callback() {
            ?>
            <div class="wrap">
                <h1><?php echo get_admin_page_title(); ?></h1>
                
                <div class="welcome-panel">
                    <div class="welcome-panel-content">
                        <h2><?php _e( 'Welcome to Your Plugin!', 'your-textdomain' ); ?></h2>
                        <p class="about-description"><?php _e( 'This is your main admin page. You can display important information here.', 'your-textdomain' ); ?></p>
                        
                        <div class="welcome-panel-column-container">
                            <div class="welcome-panel-column">
                                <h3><?php _e( 'Get Started', 'your-textdomain' ); ?></h3>
                                <ul>
                                    <li><a href="<?php echo admin_url( 'admin.php?page=' . $this->main_menu_slug . '-settings' ); ?>" class="button button-primary"><?php _e( 'Configure Settings', 'your-textdomain' ); ?></a></li>
                                    <li><a href="https://example.com/docs" target="_blank"><?php _e( 'View Documentation', 'your-textdomain' ); ?></a></li>
                                </ul>
                            </div>
                            
                            <div class="welcome-panel-column">
                                <h3><?php _e( 'Next Steps', 'your-textdomain' ); ?></h3>
                                <ul>
                                    <li><?php _e( 'Configure your settings', 'your-textdomain' ); ?></li>
                                    <li><?php _e( 'Create your first item', 'your-textdomain' ); ?></li>
                                    <li><?php _e( 'Setup your templates', 'your-textdomain' ); ?></li>
                                </ul>
                            </div>
                            
                            <div class="welcome-panel-column">
                                <h3><?php _e( 'More Actions', 'your-textdomain' ); ?></h3>
                                <ul>
                                    <li><a href="<?php echo admin_url( 'admin.php?page=' . $this->main_menu_slug . '-tools' ); ?>"><?php _e( 'Use Tools', 'your-textdomain' ); ?></a></li>
                                    <li><a href="#"><?php _e( 'Get Support', 'your-textdomain' ); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h2><?php _e( 'Quick Stats', 'your-textdomain' ); ?></h2>
                    <p><?php _e( 'Display some quick statistics or important information here.', 'your-textdomain' ); ?></p>
                </div>
            </div>
            <?php
        }
        
        /**
         * Settings page callback
         */
        public function settings_page_callback() {
            ?>
            <div class="wrap">
                <h1><?php echo get_admin_page_title(); ?></h1>
                
                <form method="post" action="options.php">
                    <?php
                    settings_fields( $this->option_group );
                    do_settings_sections( $this->main_menu_slug . '-settings' );
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
        
        /**
         * Tools page callback
         */
        public function tools_page_callback() {
            ?>
            <div class="wrap">
                <h1><?php echo get_admin_page_title(); ?></h1>
                
                <div class="card">
                    <h2><?php _e( 'Import/Export', 'your-textdomain' ); ?></h2>
                    <p><?php _e( 'Import or export your plugin settings.', 'your-textdomain' ); ?></p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field( 'your_prefix_export_nonce', 'your_prefix_export_nonce' ); ?>
                        <p>
                            <input type="submit" name="your_prefix_export_settings" class="button button-secondary" value="<?php _e( 'Export Settings', 'your-textdomain' ); ?>">
                        </p>
                    </form>
                    
                    <form method="post" action="" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'your_prefix_import_nonce', 'your_prefix_import_nonce' ); ?>
                        <p>
                            <input type="file" name="your_prefix_import_file">
                            <input type="submit" name="your_prefix_import_settings" class="button button-secondary" value="<?php _e( 'Import Settings', 'your-textdomain' ); ?>">
                        </p>
                    </form>
                </div>
                
                <div class="card">
                    <h2><?php _e( 'System Information', 'your-textdomain' ); ?></h2>
                    <p><?php _e( 'Information about your WordPress environment.', 'your-textdomain' ); ?></p>
                    
                    <textarea readonly="readonly" class="large-text code" rows="10">
WordPress Version: <?php echo get_bloginfo( 'version' ); ?>
PHP Version: <?php echo phpversion(); ?>
Plugin Version: 1.0.0
Memory Limit: <?php echo WP_MEMORY_LIMIT; ?>
                    </textarea>
                </div>
            </div>
            <?php
        }
        
        /**
         * Options page callback
         */
        public function options_page_callback() {
            ?>
            <div class="wrap">
                <h1><?php echo get_admin_page_title(); ?></h1>
                
                <form method="post" action="options.php">
                    <?php
                    settings_fields( $this->option_group );
                    do_settings_sections( $this->main_menu_slug . '-settings' );
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
        
        /**
         * Checkbox field callback
         */
        public function checkbox_field_callback( $args ) {
            $id = $args['id'];
            $label = $args['label'];
            $description = isset( $args['description'] ) ? $args['description'] : '';
            
            $checked = isset( $this->options[$id] ) ? checked( 1, $this->options[$id], false ) : '';
            
            echo '<label for="' . esc_attr( $id ) . '">';
            echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $id . ']' ) . '" value="1" ' . $checked . '>';
            echo ' ' . esc_html( $label ) . '</label>';
            
            if ( ! empty( $description ) ) {
                echo '<p class="description">' . esc_html( $description ) . '</p>';
            }
        }
        
        /**
         * Text field callback
         */
        public function text_field_callback( $args ) {
            $id = $args['id'];
            $label = isset( $args['label'] ) ? $args['label'] : '';
            $description = isset( $args['description'] ) ? $args['description'] : '';
            $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
            
            $value = isset( $this->options[$id] ) ? $this->options[$id] : '';
            
            echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $id . ']' ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '" class="regular-text">';
            
            if ( ! empty( $description ) ) {
                echo '<p class="description">' . esc_html( $description ) . '</p>';
            }
        }
        
        /**
         * Select field callback
         */
        public function select_field_callback( $args ) {
            $id = $args['id'];
            $label = isset( $args['label'] ) ? $args['label'] : '';
            $description = isset( $args['description'] ) ? $args['description'] : '';
            $options = isset( $args['options'] ) ? $args['options'] : array();
            
            $value = isset( $this->options[$id] ) ? $this->options[$id] : '';
            
            echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $id . ']' ) . '">';
            
            foreach ( $options as $key => $option ) {
                echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $option ) . '</option>';
            }
            
            echo '</select>';
            
            if ( ! empty( $description ) ) {
                echo '<p class="description">' . esc_html( $description ) . '</p>';
            }
        }
        
        /**
         * Textarea field callback
         */
        public function textarea_field_callback( $args ) {
            $id = $args['id'];
            $label = isset( $args['label'] ) ? $args['label'] : '';
            $description = isset( $args['description'] ) ? $args['description'] : '';
            $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
            $rows = isset( $args['rows'] ) ? $args['rows'] : 5;
            $cols = isset( $args['cols'] ) ? $args['cols'] : 50;
            
            $value = isset( $this->options[$id] ) ? $this->options[$id] : '';
            
            echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $id . ']' ) . '" placeholder="' . esc_attr( $placeholder ) . '" rows="' . esc_attr( $rows ) . '" cols="' . esc_attr( $cols ) . '">' . esc_textarea( $value ) . '</textarea>';
            
            if ( ! empty( $description ) ) {
                echo '<p class="description">' . esc_html( $description ) . '</p>';
            }
        }
        
        /**
         * Radio field callback
         */
        public function radio_field_callback( $args ) {
            $id = $args['id'];
            $label = isset( $args['label'] ) ? $args['label'] : '';
            $description = isset( $args['description'] ) ? $args['description'] : '';
            $options = isset( $args['options'] ) ? $args['options'] : array();
            
            $value = isset( $this->options[$id] ) ? $this->options[$id] : '';
            
            foreach ( $options as $key => $option ) {
                echo '<label>';
                echo '<input type="radio" id="' . esc_attr( $id . '_' . $key ) . '" name="' . esc_attr( $this->option_name . '[' . $id . ']' ) . '" value="' . esc_attr( $key ) . '" ' . checked( $value, $key, false ) . '>';
                echo ' ' . esc_html( $option ) . '</label><br>';
            }
            
            if ( ! empty( $description ) ) {
                echo '<p class="description">' . esc_html( $description ) . '</p>';
            }
        }
        
        /**
         * Color field callback
         */
        public function color_field_callback( $args ) {
            $id = $args['id'];
            $label = isset( $args['label'] ) ? $args['label'] : '';
            $description = isset( $args['description'] ) ? $args['description'] : '';
            $default = isset( $args['default'] ) ? $args['default'] : '#ffffff';
            
            $value = isset( $this->options[$id] ) ? $this->options[$id] : $default;
            
            echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $id . ']' ) . '" value="' . esc_attr( $value ) . '" class="your-prefix-color-picker">';
            
            if ( ! empty( $description ) ) {
                echo '<p class="description">' . esc_html( $description ) . '</p>';
            }
        }
        
        /**
         * Enqueue admin assets
         */
        public function enqueue_admin_assets( $hook ) {
            // Check if we're on our settings page
            if ( strpos( $hook, $this->main_menu_slug ) === false ) {
                return;
            }
            
            // Enqueue WordPress color picker
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            
            // Enqueue custom admin assets
            wp_enqueue_style( 
                'your-prefix-admin-style',
                plugin_dir_url( __FILE__ ) . 'css/admin-style.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'your-prefix-admin-script',
                plugin_dir_url( __FILE__ ) . 'js/admin-script.js',
                array( 'jquery', 'wp-color-picker' ),
                '1.0.0',
                true
            );
            
            // Localize script
            wp_localize_script(
                'your-prefix-admin-script',
                'your_prefix_admin',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'your_prefix_admin_nonce' ),
                )
            );
        }
        
        /**
         * Get a specific option
         * 
         * @param string $option_name Option name
         * @param mixed $default Default value
         * @return mixed Option value or default
         */
        public static function get_option( $option_name, $default = false ) {
            $options = get_option( 'your_prefix_settings', array() );
            
            return isset( $options[$option_name] ) ? $options[$option_name] : $default;
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Admin_Pages();
}

/**
 * Helper function to get plugin option
 * 
 * @param string $option_name Option name
 * @param mixed $default Default value
 * @return mixed Option value or default
 */
if ( ! function_exists( 'your_prefix_get_option' ) ) {
    function your_prefix_get_option( $option_name, $default = false ) {
        return YOUR_PREFIX_Admin_Pages::get_option( $option_name, $default );
    }
} 