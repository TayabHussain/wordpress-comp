<?php
/**
 * Shortcode Template
 * 
 * This template creates shortcodes following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * Replace 'your_shortcode' with your actual shortcode name.
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/reference/functions/add_shortcode/
 * - https://developer.wordpress.org/plugins/shortcodes/
 * - https://developer.wordpress.org/reference/functions/shortcode_atts/
 * - https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 * - https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Shortcode Handler Class
 * 
 * Handles registration and management of shortcodes
 */
if ( ! class_exists( 'YOUR_PREFIX_Shortcodes' ) ) {
    
    class YOUR_PREFIX_Shortcodes {
        
        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'init', array( $this, 'register_shortcodes' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_shortcode_assets' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
            
            // Add shortcode button to TinyMCE (optional)
            add_action( 'init', array( $this, 'add_shortcode_button' ) );
        }
        
        /**
         * Register all shortcodes
         */
        public function register_shortcodes() {
            add_shortcode( 'your_shortcode', array( $this, 'render_shortcode' ) );
            add_shortcode( 'your_list_shortcode', array( $this, 'render_list_shortcode' ) );
            add_shortcode( 'your_gallery_shortcode', array( $this, 'render_gallery_shortcode' ) );
        }
        
        /**
         * Main shortcode callback
         * 
         * Usage: [your_shortcode title="Hello World" count="5" style="modern"]
         * 
         * @param array $atts Shortcode attributes
         * @param string $content Shortcode content
         * @return string Shortcode output
         */
        public function render_shortcode( $atts, $content = null ) {
            
            // Define default attributes
            $defaults = array(
                'title'       => __( 'Default Title', 'your-textdomain' ),
                'count'       => 5,
                'style'       => 'default',
                'show_date'   => 'yes',
                'show_excerpt' => 'yes',
                'post_type'   => 'post',
                'category'    => '',
                'class'       => '',
                'id'          => '',
            );
            
            // Parse attributes
            $atts = shortcode_atts( $defaults, $atts, 'your_shortcode' );
            
            // Sanitize attributes
            $atts['title'] = sanitize_text_field( $atts['title'] );
            $atts['count'] = absint( $atts['count'] );
            $atts['style'] = sanitize_html_class( $atts['style'] );
            $atts['show_date'] = in_array( $atts['show_date'], array( 'yes', 'no' ) ) ? $atts['show_date'] : 'yes';
            $atts['show_excerpt'] = in_array( $atts['show_excerpt'], array( 'yes', 'no' ) ) ? $atts['show_excerpt'] : 'yes';
            $atts['post_type'] = sanitize_key( $atts['post_type'] );
            $atts['category'] = sanitize_text_field( $atts['category'] );
            $atts['class'] = sanitize_html_class( $atts['class'] );
            $atts['id'] = sanitize_html_class( $atts['id'] );
            
            // Process shortcode content if provided
            $content = do_shortcode( $content );
            
            // Start output buffering
            ob_start();
            
            // Query posts
            $query_args = array(
                'post_type' => $atts['post_type'],
                'posts_per_page' => $atts['count'],
                'post_status' => 'publish',
            );
            
            // Add category filter if specified
            if ( ! empty( $atts['category'] ) ) {
                $query_args['category_name'] = $atts['category'];
            }
            
            $posts = new WP_Query( $query_args );
            
            // Generate unique ID if not provided
            $unique_id = ! empty( $atts['id'] ) ? $atts['id'] : 'your-shortcode-' . uniqid();
            
            // Build CSS classes
            $css_classes = array( 'your-prefix-shortcode', 'style-' . $atts['style'] );
            if ( ! empty( $atts['class'] ) ) {
                $css_classes[] = $atts['class'];
            }
            $css_class = implode( ' ', $css_classes );
            
            ?>
            <div id="<?php echo esc_attr( $unique_id ); ?>" class="<?php echo esc_attr( $css_class ); ?>">
                
                <?php if ( ! empty( $atts['title'] ) ) : ?>
                    <h3 class="shortcode-title"><?php echo esc_html( $atts['title'] ); ?></h3>
                <?php endif; ?>
                
                <?php if ( ! empty( $content ) ) : ?>
                    <div class="shortcode-content"><?php echo wp_kses_post( $content ); ?></div>
                <?php endif; ?>
                
                <?php if ( $posts->have_posts() ) : ?>
                    <div class="shortcode-posts">
                        <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
                            <div class="shortcode-post-item">
                                
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail( 'thumbnail' ); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <h4 class="post-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h4>
                                    
                                    <?php if ( $atts['show_date'] === 'yes' ) : ?>
                                        <div class="post-date"><?php echo get_the_date(); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if ( $atts['show_excerpt'] === 'yes' ) : ?>
                                        <div class="post-excerpt"><?php the_excerpt(); ?></div>
                                    <?php endif; ?>
                                </div>
                                
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <p class="no-posts-found"><?php _e( 'No posts found.', 'your-textdomain' ); ?></p>
                <?php endif; ?>
                
            </div>
            <?php
            
            // Reset post data
            wp_reset_postdata();
            
            // Return output
            return ob_get_clean();
        }
        
        /**
         * List shortcode callback
         * 
         * Usage: [your_list_shortcode type="ul" items="Item 1,Item 2,Item 3"]
         * 
         * @param array $atts Shortcode attributes
         * @return string Shortcode output
         */
        public function render_list_shortcode( $atts ) {
            
            $defaults = array(
                'type'  => 'ul', // ul or ol
                'items' => '',
                'class' => '',
                'style' => 'default',
            );
            
            $atts = shortcode_atts( $defaults, $atts, 'your_list_shortcode' );
            
            // Sanitize attributes
            $atts['type'] = in_array( $atts['type'], array( 'ul', 'ol' ) ) ? $atts['type'] : 'ul';
            $atts['items'] = sanitize_text_field( $atts['items'] );
            $atts['class'] = sanitize_html_class( $atts['class'] );
            $atts['style'] = sanitize_html_class( $atts['style'] );
            
            if ( empty( $atts['items'] ) ) {
                return '<p>' . __( 'No items provided for list.', 'your-textdomain' ) . '</p>';
            }
            
            // Split items by comma
            $items = array_map( 'trim', explode( ',', $atts['items'] ) );
            
            // Build CSS classes
            $css_classes = array( 'your-prefix-list', 'list-style-' . $atts['style'] );
            if ( ! empty( $atts['class'] ) ) {
                $css_classes[] = $atts['class'];
            }
            $css_class = implode( ' ', $css_classes );
            
            // Generate output
            $output = '<' . $atts['type'] . ' class="' . esc_attr( $css_class ) . '">';
            foreach ( $items as $item ) {
                $output .= '<li>' . esc_html( $item ) . '</li>';
            }
            $output .= '</' . $atts['type'] . '>';
            
            return $output;
        }
        
        /**
         * Gallery shortcode callback
         * 
         * Usage: [your_gallery_shortcode ids="1,2,3" columns="3" size="medium"]
         * 
         * @param array $atts Shortcode attributes
         * @return string Shortcode output
         */
        public function render_gallery_shortcode( $atts ) {
            
            $defaults = array(
                'ids'     => '',
                'columns' => 3,
                'size'    => 'medium',
                'link'    => 'file', // file, attachment, none
                'class'   => '',
            );
            
            $atts = shortcode_atts( $defaults, $atts, 'your_gallery_shortcode' );
            
            // Sanitize attributes
            $atts['ids'] = sanitize_text_field( $atts['ids'] );
            $atts['columns'] = absint( $atts['columns'] );
            $atts['size'] = sanitize_key( $atts['size'] );
            $atts['link'] = in_array( $atts['link'], array( 'file', 'attachment', 'none' ) ) ? $atts['link'] : 'file';
            $atts['class'] = sanitize_html_class( $atts['class'] );
            
            if ( empty( $atts['ids'] ) ) {
                return '<p>' . __( 'No images provided for gallery.', 'your-textdomain' ) . '</p>';
            }
            
            // Parse image IDs
            $image_ids = array_map( 'absint', explode( ',', $atts['ids'] ) );
            $image_ids = array_filter( $image_ids );
            
            if ( empty( $image_ids ) ) {
                return '<p>' . __( 'Invalid image IDs provided.', 'your-textdomain' ) . '</p>';
            }
            
            // Build CSS classes
            $css_classes = array( 'your-prefix-gallery', 'columns-' . $atts['columns'] );
            if ( ! empty( $atts['class'] ) ) {
                $css_classes[] = $atts['class'];
            }
            $css_class = implode( ' ', $css_classes );
            
            // Start output
            ob_start();
            ?>
            <div class="<?php echo esc_attr( $css_class ); ?>">
                <?php foreach ( $image_ids as $image_id ) : 
                    $image = wp_get_attachment_image_src( $image_id, $atts['size'] );
                    if ( ! $image ) continue;
                    
                    $alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
                    $title = get_the_title( $image_id );
                ?>
                    <div class="gallery-item">
                        <?php if ( $atts['link'] === 'file' ) : 
                            $full_image = wp_get_attachment_image_src( $image_id, 'full' );
                        ?>
                            <a href="<?php echo esc_url( $full_image[0] ); ?>" data-lightbox="gallery">
                                <img src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" title="<?php echo esc_attr( $title ); ?>" />
                            </a>
                        <?php elseif ( $atts['link'] === 'attachment' ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $image_id ) ); ?>">
                                <img src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" title="<?php echo esc_attr( $title ); ?>" />
                            </a>
                        <?php else : ?>
                            <img src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" title="<?php echo esc_attr( $title ); ?>" />
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
            
            return ob_get_clean();
        }
        
        /**
         * Enqueue shortcode assets
         */
        public function enqueue_shortcode_assets() {
            // Only enqueue if shortcode is being used
            global $post;
            if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'your_shortcode' ) || 
                has_shortcode( $post->post_content, 'your_list_shortcode' ) || 
                has_shortcode( $post->post_content, 'your_gallery_shortcode' ) ) ) {
                
                wp_enqueue_style( 
                    'your-prefix-shortcode-style', 
                    plugin_dir_url( __FILE__ ) . 'css/shortcode-style.css', 
                    array(), 
                    '1.0.0' 
                );
                
                wp_enqueue_script( 
                    'your-prefix-shortcode-script', 
                    plugin_dir_url( __FILE__ ) . 'js/shortcode-script.js', 
                    array( 'jquery' ), 
                    '1.0.0', 
                    true 
                );
                
                // Localize script for AJAX
                wp_localize_script( 'your-prefix-shortcode-script', 'your_prefix_ajax', array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'your_prefix_nonce' ),
                ));
            }
        }
        
        /**
         * Enqueue admin assets
         */
        public function enqueue_admin_assets( $hook ) {
            // Only on post editor pages
            if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
                return;
            }
            
            wp_enqueue_style( 
                'your-prefix-shortcode-admin-style', 
                plugin_dir_url( __FILE__ ) . 'css/shortcode-admin.css', 
                array(), 
                '1.0.0' 
            );
            
            wp_enqueue_script( 
                'your-prefix-shortcode-admin-script', 
                plugin_dir_url( __FILE__ ) . 'js/shortcode-admin.js', 
                array( 'jquery' ), 
                '1.0.0', 
                true 
            );
        }
        
        /**
         * Add shortcode button to TinyMCE
         */
        public function add_shortcode_button() {
            if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
                return;
            }
            
            if ( get_user_option( 'rich_editing' ) !== 'true' ) {
                return;
            }
            
            add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
            add_filter( 'mce_buttons', array( $this, 'add_tinymce_button' ) );
        }
        
        /**
         * Add TinyMCE plugin
         */
        public function add_tinymce_plugin( $plugin_array ) {
            $plugin_array['your_prefix_shortcode'] = plugin_dir_url( __FILE__ ) . 'js/tinymce-plugin.js';
            return $plugin_array;
        }
        
        /**
         * Add TinyMCE button
         */
        public function add_tinymce_button( $buttons ) {
            array_push( $buttons, 'your_prefix_shortcode_button' );
            return $buttons;
        }
        
        /**
         * AJAX handler for shortcode preview
         */
        public function ajax_shortcode_preview() {
            
            // Check nonce
            if ( ! wp_verify_nonce( $_POST['nonce'], 'your_prefix_nonce' ) ) {
                wp_die( __( 'Security check failed.', 'your-textdomain' ) );
            }
            
            // Check permissions
            if ( ! current_user_can( 'edit_posts' ) ) {
                wp_die( __( 'Insufficient permissions.', 'your-textdomain' ) );
            }
            
            $shortcode = sanitize_text_field( $_POST['shortcode'] );
            
            if ( empty( $shortcode ) ) {
                wp_send_json_error( __( 'No shortcode provided.', 'your-textdomain' ) );
            }
            
            // Process shortcode
            $output = do_shortcode( $shortcode );
            
            wp_send_json_success( $output );
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Shortcodes();
    
    // Add AJAX handlers
    add_action( 'wp_ajax_your_prefix_shortcode_preview', array( 'YOUR_PREFIX_Shortcodes', 'ajax_shortcode_preview' ) );
}

/**
 * Helper functions for shortcodes
 */

/**
 * Check if shortcode exists in content
 * 
 * @param string $content Content to check
 * @param string $shortcode Shortcode tag
 * @return bool
 */
if ( ! function_exists( 'your_prefix_has_shortcode' ) ) {
    function your_prefix_has_shortcode( $content, $shortcode ) {
        return has_shortcode( $content, $shortcode );
    }
}

/**
 * Remove shortcode from content
 * 
 * @param string $content Content
 * @param string $shortcode Shortcode tag
 * @return string
 */
if ( ! function_exists( 'your_prefix_remove_shortcode' ) ) {
    function your_prefix_remove_shortcode( $content, $shortcode ) {
        return preg_replace( '/\[' . $shortcode . '[^\]]*\]/', '', $content );
    }
}

/**
 * Get shortcode attributes from content
 * 
 * @param string $content Content
 * @param string $shortcode Shortcode tag
 * @return array
 */
if ( ! function_exists( 'your_prefix_get_shortcode_atts' ) ) {
    function your_prefix_get_shortcode_atts( $content, $shortcode ) {
        $pattern = '/\[' . $shortcode . '([^\]]*)\]/';
        preg_match( $pattern, $content, $matches );
        
        if ( empty( $matches[1] ) ) {
            return array();
        }
        
        return shortcode_parse_atts( $matches[1] );
    }
}