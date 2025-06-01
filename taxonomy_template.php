<?php
/**
 * Custom Taxonomy Template
 * 
 * This template creates a custom taxonomy following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * Replace 'your_taxonomy' with your actual taxonomy name.
 * Replace 'your_post_type' with the post type this taxonomy should be attached to.
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/reference/functions/register_taxonomy/
 * - https://developer.wordpress.org/plugins/taxonomies/working-with-custom-taxonomies/
 * - https://developer.wordpress.org/reference/functions/add_action/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Custom Taxonomy Class
 * 
 * Handles registration and management of custom taxonomies
 */
if ( ! class_exists( 'YOUR_PREFIX_Custom_Taxonomy' ) ) {
    
    class YOUR_PREFIX_Custom_Taxonomy {
        
        /**
         * Taxonomy name
         */
        private $taxonomy = 'your_taxonomy';
        
        /**
         * Post types this taxonomy applies to
         */
        private $post_types = array( 'your_post_type', 'post' );
        
        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'init', array( $this, 'register_taxonomy' ) );
            add_action( 'init', array( $this, 'register_meta_fields' ) );
            add_action( $this->taxonomy . '_add_form_fields', array( $this, 'add_taxonomy_fields' ) );
            add_action( $this->taxonomy . '_edit_form_fields', array( $this, 'edit_taxonomy_fields' ) );
            add_action( 'edited_' . $this->taxonomy, array( $this, 'save_taxonomy_fields' ) );
            add_action( 'create_' . $this->taxonomy, array( $this, 'save_taxonomy_fields' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        }
        
        /**
         * Register the custom taxonomy
         */
        public function register_taxonomy() {
            
            $labels = array(
                'name'                       => _x( 'Your Taxonomies', 'Taxonomy General Name', 'your-textdomain' ),
                'singular_name'              => _x( 'Your Taxonomy', 'Taxonomy Singular Name', 'your-textdomain' ),
                'menu_name'                  => __( 'Your Taxonomies', 'your-textdomain' ),
                'all_items'                  => __( 'All Your Taxonomies', 'your-textdomain' ),
                'parent_item'                => __( 'Parent Your Taxonomy', 'your-textdomain' ),
                'parent_item_colon'          => __( 'Parent Your Taxonomy:', 'your-textdomain' ),
                'new_item_name'              => __( 'New Your Taxonomy Name', 'your-textdomain' ),
                'add_new_item'               => __( 'Add New Your Taxonomy', 'your-textdomain' ),
                'edit_item'                  => __( 'Edit Your Taxonomy', 'your-textdomain' ),
                'update_item'                => __( 'Update Your Taxonomy', 'your-textdomain' ),
                'view_item'                  => __( 'View Your Taxonomy', 'your-textdomain' ),
                'separate_items_with_commas' => __( 'Separate Your Taxonomies with commas', 'your-textdomain' ),
                'add_or_remove_items'        => __( 'Add or remove Your Taxonomies', 'your-textdomain' ),
                'choose_from_most_used'      => __( 'Choose from the most used', 'your-textdomain' ),
                'popular_items'              => __( 'Popular Your Taxonomies', 'your-textdomain' ),
                'search_items'               => __( 'Search Your Taxonomies', 'your-textdomain' ),
                'not_found'                  => __( 'Not Found', 'your-textdomain' ),
                'no_terms'                   => __( 'No Your Taxonomies', 'your-textdomain' ),
                'items_list'                 => __( 'Your Taxonomies list', 'your-textdomain' ),
                'items_list_navigation'      => __( 'Your Taxonomies list navigation', 'your-textdomain' ),
            );
            
            $args = array(
                'labels'                     => $labels,
                'hierarchical'               => true, // Set to false for tag-like behavior
                'public'                     => true,
                'show_ui'                    => true,
                'show_admin_column'          => true,
                'show_in_nav_menus'          => true,
                'show_tagcloud'              => true,
                'show_in_rest'               => true, // Enable for Gutenberg and REST API
                'query_var'                  => true,
                'rewrite'                    => array( 'slug' => 'your-taxonomy' ),
                'capabilities'               => array(
                    'manage_terms' => 'manage_categories',
                    'edit_terms'   => 'manage_categories',
                    'delete_terms' => 'manage_categories',
                    'assign_terms' => 'edit_posts',
                ),
            );
            
            register_taxonomy( $this->taxonomy, $this->post_types, $args );
        }
        
        /**
         * Register meta fields for REST API
         */
        public function register_meta_fields() {
            register_term_meta( $this->taxonomy, 'your_prefix_taxonomy_meta', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'description' => 'Custom taxonomy meta field',
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => function() {
                    return current_user_can( 'manage_categories' );
                }
            ));
            
            register_term_meta( $this->taxonomy, 'your_prefix_taxonomy_image', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'integer',
                'description' => 'Taxonomy image attachment ID',
                'sanitize_callback' => 'absint',
                'auth_callback' => function() {
                    return current_user_can( 'manage_categories' );
                }
            ));
        }
        
        /**
         * Add custom fields to taxonomy add form
         */
        public function add_taxonomy_fields() {
            ?>
            <div class="form-field term-your-prefix-taxonomy-meta-wrap">
                <label for="your-prefix-taxonomy-meta"><?php _e( 'Custom Meta Field', 'your-textdomain' ); ?></label>
                <input type="text" name="your_prefix_taxonomy_meta" id="your-prefix-taxonomy-meta" value="" size="40" />
                <p class="description"><?php _e( 'Enter a custom meta value for this taxonomy term.', 'your-textdomain' ); ?></p>
            </div>
            
            <div class="form-field term-your-prefix-taxonomy-image-wrap">
                <label for="your-prefix-taxonomy-image"><?php _e( 'Taxonomy Image', 'your-textdomain' ); ?></label>
                <div class="your-prefix-taxonomy-image-container">
                    <input type="hidden" name="your_prefix_taxonomy_image" id="your-prefix-taxonomy-image" value="" />
                    <div class="your-prefix-taxonomy-image-preview" style="margin-bottom: 10px;"></div>
                    <button type="button" class="button your-prefix-taxonomy-image-upload"><?php _e( 'Upload Image', 'your-textdomain' ); ?></button>
                    <button type="button" class="button your-prefix-taxonomy-image-remove" style="display:none;"><?php _e( 'Remove Image', 'your-textdomain' ); ?></button>
                </div>
                <p class="description"><?php _e( 'Upload an image for this taxonomy term.', 'your-textdomain' ); ?></p>
            </div>
            <?php
        }
        
        /**
         * Add custom fields to taxonomy edit form
         */
        public function edit_taxonomy_fields( $term ) {
            $meta_value = get_term_meta( $term->term_id, 'your_prefix_taxonomy_meta', true );
            $image_id = get_term_meta( $term->term_id, 'your_prefix_taxonomy_image', true );
            $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
            ?>
            <tr class="form-field term-your-prefix-taxonomy-meta-wrap">
                <th scope="row">
                    <label for="your-prefix-taxonomy-meta"><?php _e( 'Custom Meta Field', 'your-textdomain' ); ?></label>
                </th>
                <td>
                    <input type="text" name="your_prefix_taxonomy_meta" id="your-prefix-taxonomy-meta" value="<?php echo esc_attr( $meta_value ); ?>" size="40" />
                    <p class="description"><?php _e( 'Enter a custom meta value for this taxonomy term.', 'your-textdomain' ); ?></p>
                </td>
            </tr>
            
            <tr class="form-field term-your-prefix-taxonomy-image-wrap">
                <th scope="row">
                    <label for="your-prefix-taxonomy-image"><?php _e( 'Taxonomy Image', 'your-textdomain' ); ?></label>
                </th>
                <td>
                    <div class="your-prefix-taxonomy-image-container">
                        <input type="hidden" name="your_prefix_taxonomy_image" id="your-prefix-taxonomy-image" value="<?php echo esc_attr( $image_id ); ?>" />
                        <div class="your-prefix-taxonomy-image-preview" style="margin-bottom: 10px;">
                            <?php if ( $image_url ) : ?>
                                <img src="<?php echo esc_url( $image_url ); ?>" style="max-width: 150px; height: auto;" />
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button your-prefix-taxonomy-image-upload"><?php _e( 'Upload Image', 'your-textdomain' ); ?></button>
                        <button type="button" class="button your-prefix-taxonomy-image-remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php _e( 'Remove Image', 'your-textdomain' ); ?></button>
                    </div>
                    <p class="description"><?php _e( 'Upload an image for this taxonomy term.', 'your-textdomain' ); ?></p>
                </td>
            </tr>
            <?php
        }
        
        /**
         * Save custom taxonomy fields
         */
        public function save_taxonomy_fields( $term_id ) {
            
            // Save meta field
            if ( isset( $_POST['your_prefix_taxonomy_meta'] ) ) {
                $meta_value = sanitize_text_field( $_POST['your_prefix_taxonomy_meta'] );
                update_term_meta( $term_id, 'your_prefix_taxonomy_meta', $meta_value );
            }
            
            // Save image field
            if ( isset( $_POST['your_prefix_taxonomy_image'] ) ) {
                $image_id = absint( $_POST['your_prefix_taxonomy_image'] );
                if ( $image_id ) {
                    update_term_meta( $term_id, 'your_prefix_taxonomy_image', $image_id );
                } else {
                    delete_term_meta( $term_id, 'your_prefix_taxonomy_image' );
                }
            }
        }
        
        /**
         * Enqueue admin scripts
         */
        public function enqueue_admin_scripts( $hook ) {
            if ( 'edit-tags.php' !== $hook && 'term.php' !== $hook ) {
                return;
            }
            
            global $current_screen;
            if ( $current_screen->taxonomy !== $this->taxonomy ) {
                return;
            }
            
            wp_enqueue_media();
            wp_enqueue_script( 'your-prefix-taxonomy-admin', plugin_dir_url( __FILE__ ) . 'js/taxonomy-admin.js', array( 'jquery' ), '1.0.0', true );
        }
        
        /**
         * Get taxonomy terms
         * 
         * @param array $args Query arguments
         * @return array|WP_Error
         */
        public static function get_terms( $args = array() ) {
            $defaults = array(
                'taxonomy' => 'your_taxonomy',
                'hide_empty' => false,
            );
            
            $args = wp_parse_args( $args, $defaults );
            
            return get_terms( $args );
        }
        
        /**
         * Get posts by taxonomy term
         * 
         * @param int|string $term_id Term ID or slug
         * @param array $args Query arguments
         * @return WP_Query
         */
        public static function get_posts_by_term( $term_id, $args = array() ) {
            $defaults = array(
                'post_type' => 'your_post_type',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'your_taxonomy',
                        'field'    => is_numeric( $term_id ) ? 'term_id' : 'slug',
                        'terms'    => $term_id,
                    ),
                ),
            );
            
            $args = wp_parse_args( $args, $defaults );
            
            return new WP_Query( $args );
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Custom_Taxonomy();
}

/**
 * Helper functions
 */

/**
 * Get taxonomy terms
 * 
 * @param array $args Query arguments
 * @return array|WP_Error
 */
if ( ! function_exists( 'your_prefix_get_taxonomy_terms' ) ) {
    function your_prefix_get_taxonomy_terms( $args = array() ) {
        return YOUR_PREFIX_Custom_Taxonomy::get_terms( $args );
    }
}

/**
 * Get posts by taxonomy term
 * 
 * @param int|string $term_id Term ID or slug
 * @param array $args Query arguments
 * @return WP_Query
 */
if ( ! function_exists( 'your_prefix_get_posts_by_term' ) ) {
    function your_prefix_get_posts_by_term( $term_id, $args = array() ) {
        return YOUR_PREFIX_Custom_Taxonomy::get_posts_by_term( $term_id, $args );
    }
}