<?php
/**
 * REST API Template
 * 
 * This template creates custom REST API endpoints following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/rest-api/extending-the-rest-api/
 * - https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
 * - https://developer.wordpress.org/rest-api/using-the-rest-api/global-parameters/
 * - https://developer.wordpress.org/reference/functions/register_rest_route/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * REST API Handler Class
 * 
 * Handles registration and management of custom REST API endpoints
 */
if ( ! class_exists( 'YOUR_PREFIX_REST_API' ) ) {
    
    class YOUR_PREFIX_REST_API {
        
        /**
         * API namespace
         */
        private $namespace = 'your-prefix/v1';
        
        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'rest_api_init', array( $this, 'register_routes' ) );
        }
        
        /**
         * Register custom REST API routes
         */
        public function register_routes() {
            
            // Example GET endpoint for items
            register_rest_route(
                $this->namespace,
                '/items',
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_items_args(),
                )
            );
            
            // Example GET endpoint for a single item
            register_rest_route(
                $this->namespace,
                '/items/(?P<id>\d+)',
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_item' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => $this->get_item_args(),
                )
            );
            
            // Example POST endpoint to create an item
            register_rest_route(
                $this->namespace,
                '/items',
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_item' ),
                    'permission_callback' => array( $this, 'create_item_permissions_check' ),
                    'args'                => $this->create_item_args(),
                )
            );
            
            // Example PUT/POST endpoint to update an item
            register_rest_route(
                $this->namespace,
                '/items/(?P<id>\d+)',
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_item' ),
                    'permission_callback' => array( $this, 'update_item_permissions_check' ),
                    'args'                => $this->update_item_args(),
                )
            );
            
            // Example DELETE endpoint to delete an item
            register_rest_route(
                $this->namespace,
                '/items/(?P<id>\d+)',
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_item' ),
                    'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                    'args'                => $this->delete_item_args(),
                )
            );
            
            // Example custom action endpoint
            register_rest_route(
                $this->namespace,
                '/items/(?P<id>\d+)/custom-action',
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'custom_action' ),
                    'permission_callback' => array( $this, 'custom_action_permissions_check' ),
                    'args'                => $this->custom_action_args(),
                )
            );
        }
        
        /**
         * Get items arguments schema
         */
        public function get_items_args() {
            return array(
                'page' => array(
                    'description'       => __( 'Current page of the collection.', 'your-textdomain' ),
                    'type'              => 'integer',
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'per_page' => array(
                    'description'       => __( 'Maximum number of items to be returned in result set.', 'your-textdomain' ),
                    'type'              => 'integer',
                    'default'           => 10,
                    'minimum'           => 1,
                    'maximum'           => 100,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'search' => array(
                    'description'       => __( 'Limit results to those matching a string.', 'your-textdomain' ),
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'orderby' => array(
                    'description'       => __( 'Sort collection by object attribute.', 'your-textdomain' ),
                    'type'              => 'string',
                    'default'           => 'date',
                    'enum'              => array( 'date', 'title', 'id' ),
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'order' => array(
                    'description'       => __( 'Order sort attribute ascending or descending.', 'your-textdomain' ),
                    'type'              => 'string',
                    'default'           => 'desc',
                    'enum'              => array( 'asc', 'desc' ),
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            );
        }
        
        /**
         * Get item arguments schema
         */
        public function get_item_args() {
            return array(
                'id' => array(
                    'description'       => __( 'Unique identifier for the object.', 'your-textdomain' ),
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            );
        }
        
        /**
         * Create item arguments schema
         */
        public function create_item_args() {
            return array(
                'title' => array(
                    'description'       => __( 'The title for the object.', 'your-textdomain' ),
                    'type'              => 'string',
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'content' => array(
                    'description'       => __( 'The content for the object.', 'your-textdomain' ),
                    'type'              => 'string',
                    'required'          => true,
                    'sanitize_callback' => 'wp_kses_post',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'status' => array(
                    'description'       => __( 'A named status for the object.', 'your-textdomain' ),
                    'type'              => 'string',
                    'enum'              => array( 'draft', 'publish', 'pending', 'private' ),
                    'default'           => 'draft',
                    'sanitize_callback' => 'sanitize_key',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            );
        }
        
        /**
         * Update item arguments schema
         */
        public function update_item_args() {
            return array_merge(
                $this->get_item_args(),
                array(
                    'title' => array(
                        'description'       => __( 'The title for the object.', 'your-textdomain' ),
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => 'rest_validate_request_arg',
                    ),
                    'content' => array(
                        'description'       => __( 'The content for the object.', 'your-textdomain' ),
                        'type'              => 'string',
                        'sanitize_callback' => 'wp_kses_post',
                        'validate_callback' => 'rest_validate_request_arg',
                    ),
                    'status' => array(
                        'description'       => __( 'A named status for the object.', 'your-textdomain' ),
                        'type'              => 'string',
                        'enum'              => array( 'draft', 'publish', 'pending', 'private' ),
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => 'rest_validate_request_arg',
                    ),
                )
            );
        }
        
        /**
         * Delete item arguments schema
         */
        public function delete_item_args() {
            return array(
                'id' => array(
                    'description'       => __( 'Unique identifier for the object.', 'your-textdomain' ),
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'force' => array(
                    'description'       => __( 'Whether to bypass trash and force deletion.', 'your-textdomain' ),
                    'type'              => 'boolean',
                    'default'           => false,
                ),
            );
        }
        
        /**
         * Custom action arguments schema
         */
        public function custom_action_args() {
            return array(
                'id' => array(
                    'description'       => __( 'Unique identifier for the object.', 'your-textdomain' ),
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'action_param' => array(
                    'description'       => __( 'Parameter for the custom action.', 'your-textdomain' ),
                    'type'              => 'string',
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            );
        }
        
        /**
         * Check permissions for listing items
         */
        public function get_items_permissions_check( $request ) {
            // For example, allow public access to this endpoint
            return true;
            
            // Or restrict by capability
            // return current_user_can( 'read' );
        }
        
        /**
         * Check permissions for getting a single item
         */
        public function get_item_permissions_check( $request ) {
            $id = $request['id'];
            
            // Check if the item exists
            $item = $this->get_item_by_id( $id );
            if ( is_wp_error( $item ) ) {
                return $item;
            }
            
            // For example, allow public access to this endpoint
            return true;
            
            // Or restrict by capability and check if private
            // if ( $item->status === 'private' && ! current_user_can( 'read_private_posts' ) ) {
            //     return new WP_Error(
            //         'rest_forbidden',
            //         __( 'You cannot view this item.', 'your-textdomain' ),
            //         array( 'status' => rest_authorization_required_code() )
            //     );
            // }
            // return true;
        }
        
        /**
         * Check permissions for creating an item
         */
        public function create_item_permissions_check( $request ) {
            // Check if user has permission to create
            return current_user_can( 'edit_posts' );
        }
        
        /**
         * Check permissions for updating an item
         */
        public function update_item_permissions_check( $request ) {
            $id = $request['id'];
            
            // Check if the item exists
            $item = $this->get_item_by_id( $id );
            if ( is_wp_error( $item ) ) {
                return $item;
            }
            
            // Check if user has permission to edit
            return current_user_can( 'edit_posts' );
            
            // For more granular control
            // if ( $item->author_id !== get_current_user_id() && ! current_user_can( 'edit_others_posts' ) ) {
            //     return new WP_Error(
            //         'rest_cannot_edit',
            //         __( 'You cannot edit this item.', 'your-textdomain' ),
            //         array( 'status' => rest_authorization_required_code() )
            //     );
            // }
            // return true;
        }
        
        /**
         * Check permissions for deleting an item
         */
        public function delete_item_permissions_check( $request ) {
            $id = $request['id'];
            
            // Check if the item exists
            $item = $this->get_item_by_id( $id );
            if ( is_wp_error( $item ) ) {
                return $item;
            }
            
            // Check if user has permission to delete
            return current_user_can( 'delete_posts' );
            
            // For more granular control
            // if ( $item->author_id !== get_current_user_id() && ! current_user_can( 'delete_others_posts' ) ) {
            //     return new WP_Error(
            //         'rest_cannot_delete',
            //         __( 'You cannot delete this item.', 'your-textdomain' ),
            //         array( 'status' => rest_authorization_required_code() )
            //     );
            // }
            // return true;
        }
        
        /**
         * Check permissions for custom action
         */
        public function custom_action_permissions_check( $request ) {
            $id = $request['id'];
            
            // Check if the item exists
            $item = $this->get_item_by_id( $id );
            if ( is_wp_error( $item ) ) {
                return $item;
            }
            
            // Check if user has permission
            return current_user_can( 'edit_posts' );
        }
        
        /**
         * Get a collection of items
         */
        public function get_items( $request ) {
            // Prepare query arguments
            $args = array(
                'posts_per_page' => $request['per_page'],
                'post_type'      => 'your_post_type', // Your custom post type
                'paged'          => $request['page'],
                'orderby'        => $request['orderby'],
                'order'          => $request['order'],
            );
            
            // Add search if provided
            if ( ! empty( $request['search'] ) ) {
                $args['s'] = $request['search'];
            }
            
            // Execute the query
            $query = new WP_Query( $args );
            
            // Get the posts
            $posts = $query->posts;
            
            $items = array();
            foreach ( $posts as $post ) {
                $response = $this->prepare_item_for_response( $post, $request );
                $items[] = $this->prepare_response_for_collection( $response );
            }
            
            // Add pagination headers
            $total_posts = $query->found_posts;
            $max_pages = ceil( $total_posts / $request['per_page'] );
            
            $response = rest_ensure_response( $items );
            
            $response->header( 'X-WP-Total', $total_posts );
            $response->header( 'X-WP-TotalPages', $max_pages );
            
            return $response;
        }
        
        /**
         * Get a single item
         */
        public function get_item( $request ) {
            $id = $request['id'];
            
            // Get the post
            $post = get_post( $id );
            
            if ( empty( $post ) || $post->post_type !== 'your_post_type' ) {
                return new WP_Error(
                    'rest_item_not_found',
                    __( 'Item not found.', 'your-textdomain' ),
                    array( 'status' => 404 )
                );
            }
            
            $response = $this->prepare_item_for_response( $post, $request );
            
            return rest_ensure_response( $response );
        }
        
        /**
         * Create an item
         */
        public function create_item( $request ) {
            // Create post object
            $post_data = array(
                'post_title'   => $request['title'],
                'post_content' => $request['content'],
                'post_status'  => $request['status'],
                'post_type'    => 'your_post_type',
            );
            
            // Insert the post into the database
            $post_id = wp_insert_post( $post_data );
            
            if ( is_wp_error( $post_id ) ) {
                return $post_id;
            }
            
            // Save custom meta fields if needed
            if ( ! empty( $request['custom_field'] ) ) {
                update_post_meta( $post_id, 'your_prefix_custom_field', $request['custom_field'] );
            }
            
            $post = get_post( $post_id );
            $response = $this->prepare_item_for_response( $post, $request );
            
            $response->set_status( 201 );
            $response->header( 'Location', rest_url( sprintf( '%s/items/%d', $this->namespace, $post_id ) ) );
            
            return rest_ensure_response( $response );
        }
        
        /**
         * Update an item
         */
        public function update_item( $request ) {
            $id = $request['id'];
            $post = get_post( $id );
            
            if ( empty( $post ) || $post->post_type !== 'your_post_type' ) {
                return new WP_Error(
                    'rest_item_not_found',
                    __( 'Item not found.', 'your-textdomain' ),
                    array( 'status' => 404 )
                );
            }
            
            // Update post data
            $post_data = array(
                'ID' => $id
            );
            
            if ( isset( $request['title'] ) ) {
                $post_data['post_title'] = $request['title'];
            }
            
            if ( isset( $request['content'] ) ) {
                $post_data['post_content'] = $request['content'];
            }
            
            if ( isset( $request['status'] ) ) {
                $post_data['post_status'] = $request['status'];
            }
            
            // Update the post
            $post_id = wp_update_post( $post_data, true );
            
            if ( is_wp_error( $post_id ) ) {
                return $post_id;
            }
            
            // Update custom meta fields if needed
            if ( isset( $request['custom_field'] ) ) {
                update_post_meta( $post_id, 'your_prefix_custom_field', $request['custom_field'] );
            }
            
            $post = get_post( $post_id );
            $response = $this->prepare_item_for_response( $post, $request );
            
            return rest_ensure_response( $response );
        }
        
        /**
         * Delete an item
         */
        public function delete_item( $request ) {
            $id = $request['id'];
            $force = (bool) $request['force'];
            
            $post = get_post( $id );
            
            if ( empty( $post ) || $post->post_type !== 'your_post_type' ) {
                return new WP_Error(
                    'rest_item_not_found',
                    __( 'Item not found.', 'your-textdomain' ),
                    array( 'status' => 404 )
                );
            }
            
            // Get the previous item first for the response
            $previous = $this->prepare_item_for_response( $post, $request );
            
            // If we're forcing, permanently delete the post
            if ( $force ) {
                $result = wp_delete_post( $id, true );
            } else {
                // Otherwise, just move to trash
                $result = wp_trash_post( $id );
            }
            
            if ( ! $result ) {
                return new WP_Error(
                    'rest_cannot_delete',
                    __( 'The item cannot be deleted.', 'your-textdomain' ),
                    array( 'status' => 500 )
                );
            }
            
            $response = new WP_REST_Response();
            $response->set_data( array(
                'deleted'  => true,
                'previous' => $previous,
            ) );
            
            return $response;
        }
        
        /**
         * Perform custom action
         */
        public function custom_action( $request ) {
            $id = $request['id'];
            $action_param = $request['action_param'];
            
            $post = get_post( $id );
            
            if ( empty( $post ) || $post->post_type !== 'your_post_type' ) {
                return new WP_Error(
                    'rest_item_not_found',
                    __( 'Item not found.', 'your-textdomain' ),
                    array( 'status' => 404 )
                );
            }
            
            // Perform your custom action here
            // Example: Toggle a custom status
            $current_status = get_post_meta( $id, 'your_prefix_custom_status', true );
            $new_status = $current_status === 'active' ? 'inactive' : 'active';
            update_post_meta( $id, 'your_prefix_custom_status', $new_status );
            
            // You could also do something with the action_param
            
            // Prepare the response with the updated item
            $post = get_post( $id );
            $response = $this->prepare_item_for_response( $post, $request );
            
            // Add custom data to the response
            $response->data['custom_action_result'] = array(
                'action' => 'custom_action',
                'param' => $action_param,
                'previous_status' => $current_status,
                'new_status' => $new_status,
            );
            
            return rest_ensure_response( $response );
        }
        
        /**
         * Prepare the item for the REST response
         */
        public function prepare_item_for_response( $post, $request ) {
            // Get post data
            $data = array(
                'id'           => $post->ID,
                'title'        => array(
                    'raw'      => $post->post_title,
                    'rendered' => get_the_title( $post->ID ),
                ),
                'content'      => array(
                    'raw'      => $post->post_content,
                    'rendered' => apply_filters( 'the_content', $post->post_content ),
                ),
                'date'         => mysql_to_rfc3339( $post->post_date ),
                'date_gmt'     => mysql_to_rfc3339( $post->post_date_gmt ),
                'modified'     => mysql_to_rfc3339( $post->post_modified ),
                'modified_gmt' => mysql_to_rfc3339( $post->post_modified_gmt ),
                'author'       => (int) $post->post_author,
                'status'       => $post->post_status,
                'link'         => get_permalink( $post->ID ),
            );
            
            // Add featured image if available
            if ( has_post_thumbnail( $post->ID ) ) {
                $data['featured_media'] = get_post_thumbnail_id( $post->ID );
                $data['featured_image_url'] = get_the_post_thumbnail_url( $post->ID, 'full' );
            }
            
            // Add custom meta fields
            $data['custom_field'] = get_post_meta( $post->ID, 'your_prefix_custom_field', true );
            $data['custom_status'] = get_post_meta( $post->ID, 'your_prefix_custom_status', true );
            
            // Add terms if available
            $taxonomies = get_object_taxonomies( 'your_post_type', 'objects' );
            foreach ( $taxonomies as $taxonomy ) {
                $data[ $taxonomy->name ] = wp_get_post_terms( $post->ID, $taxonomy->name, array( 'fields' => 'ids' ) );
            }
            
            // Get post meta
            $data = apply_filters( 'your_prefix_rest_prepare_item', $data, $post, $request );
            
            return $data;
        }
        
        /**
         * Prepare a response for collection
         */
        public function prepare_response_for_collection( $response ) {
            if ( ! ( $response instanceof WP_REST_Response ) ) {
                return $response;
            }
            
            $data = (array) $response->get_data();
            
            return $data;
        }
        
        /**
         * Get item by ID
         */
        protected function get_item_by_id( $id ) {
            $post = get_post( $id );
            
            if ( empty( $post ) || $post->post_type !== 'your_post_type' ) {
                return new WP_Error(
                    'rest_item_not_found',
                    __( 'Item not found.', 'your-textdomain' ),
                    array( 'status' => 404 )
                );
            }
            
            return $post;
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_REST_API();
}

/**
 * Helper function to get REST API URL
 * 
 * @param string $endpoint Endpoint path
 * @return string REST API URL
 */
if ( ! function_exists( 'your_prefix_get_rest_url' ) ) {
    function your_prefix_get_rest_url( $endpoint = '' ) {
        return rest_url( 'your-prefix/v1/' . $endpoint );
    }
} 