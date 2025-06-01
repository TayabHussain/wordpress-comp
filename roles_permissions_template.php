<?php
/**
 * Roles & Permissions Template
 * 
 * This template creates custom roles and capabilities following WordPress best practices.
 * Replace 'YOUR_PREFIX' with your unique plugin prefix (minimum 4 characters).
 * 
 * Documentation needed:
 * - https://developer.wordpress.org/plugins/users/roles-and-capabilities/
 * - https://developer.wordpress.org/reference/functions/add_role/
 * - https://developer.wordpress.org/reference/functions/get_role/
 * - https://developer.wordpress.org/reference/functions/current_user_can/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Roles and Permissions Class
 * 
 * Handles creation and management of custom roles and capabilities
 */
if ( ! class_exists( 'YOUR_PREFIX_Roles_Permissions' ) ) {
    
    class YOUR_PREFIX_Roles_Permissions {
        
        /**
         * Custom capability prefix
         */
        private $cap_prefix = 'your_prefix_';
        
        /**
         * Custom role names
         */
        private $roles = array(
            'your_prefix_manager' => 'Your Plugin Manager',
            'your_prefix_editor' => 'Your Plugin Editor',
            'your_prefix_viewer' => 'Your Plugin Viewer'
        );
        
        /**
         * Constructor
         */
        public function __construct() {
            // Register activation hook for role creation
            register_activation_hook( YOUR_PREFIX_PLUGIN_FILE, array( $this, 'add_custom_roles' ) );
            
            // Register deactivation hook for role cleanup (optional)
            register_deactivation_hook( YOUR_PREFIX_PLUGIN_FILE, array( $this, 'remove_custom_roles' ) );
            
            // Add custom capabilities to existing roles
            add_action( 'admin_init', array( $this, 'add_custom_capabilities' ) );
            
            // Filter for meta capabilities
            add_filter( 'map_meta_cap', array( $this, 'map_meta_capabilities' ), 10, 4 );
        }
        
        /**
         * Add custom roles
         */
        public function add_custom_roles() {
            // Manager role
            add_role(
                'your_prefix_manager',
                __( 'Your Plugin Manager', 'your-textdomain' ),
                array(
                    'read' => true,
                    $this->cap_prefix . 'manage_settings' => true,
                    $this->cap_prefix . 'create_items' => true,
                    $this->cap_prefix . 'edit_items' => true,
                    $this->cap_prefix . 'edit_others_items' => true,
                    $this->cap_prefix . 'delete_items' => true,
                    $this->cap_prefix . 'delete_others_items' => true,
                    $this->cap_prefix . 'read_private_items' => true,
                    $this->cap_prefix . 'import_items' => true,
                    $this->cap_prefix . 'export_items' => true,
                )
            );
            
            // Editor role
            add_role(
                'your_prefix_editor',
                __( 'Your Plugin Editor', 'your-textdomain' ),
                array(
                    'read' => true,
                    $this->cap_prefix . 'create_items' => true,
                    $this->cap_prefix . 'edit_items' => true,
                    $this->cap_prefix . 'delete_items' => true,
                    $this->cap_prefix . 'read_private_items' => true,
                )
            );
            
            // Viewer role
            add_role(
                'your_prefix_viewer',
                __( 'Your Plugin Viewer', 'your-textdomain' ),
                array(
                    'read' => true,
                    $this->cap_prefix . 'read_items' => true,
                )
            );
        }
        
        /**
         * Remove custom roles
         */
        public function remove_custom_roles() {
            // Only remove roles if plugin is being deleted (not just deactivated)
            if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                return;
            }
            
            foreach ( array_keys( $this->roles ) as $role ) {
                remove_role( $role );
            }
        }
        
        /**
         * Add custom capabilities to existing roles
         */
        public function add_custom_capabilities() {
            // Add capabilities to administrator
            $admin = get_role( 'administrator' );
            
            if ( $admin ) {
                $admin->add_cap( $this->cap_prefix . 'manage_settings' );
                $admin->add_cap( $this->cap_prefix . 'create_items' );
                $admin->add_cap( $this->cap_prefix . 'edit_items' );
                $admin->add_cap( $this->cap_prefix . 'edit_others_items' );
                $admin->add_cap( $this->cap_prefix . 'delete_items' );
                $admin->add_cap( $this->cap_prefix . 'delete_others_items' );
                $admin->add_cap( $this->cap_prefix . 'read_private_items' );
                $admin->add_cap( $this->cap_prefix . 'read_items' );
                $admin->add_cap( $this->cap_prefix . 'import_items' );
                $admin->add_cap( $this->cap_prefix . 'export_items' );
            }
            
            // Add capabilities to editor
            $editor = get_role( 'editor' );
            
            if ( $editor ) {
                $editor->add_cap( $this->cap_prefix . 'create_items' );
                $editor->add_cap( $this->cap_prefix . 'edit_items' );
                $editor->add_cap( $this->cap_prefix . 'delete_items' );
                $editor->add_cap( $this->cap_prefix . 'read_items' );
            }
        }
        
        /**
         * Map meta capabilities to primitive capabilities
         *
         * @param array $caps The user's capabilities
         * @param string $cap Capability name
         * @param int $user_id User ID
         * @param array $args Additional arguments
         * @return array Modified capabilities
         */
        public function map_meta_capabilities( $caps, $cap, $user_id, $args ) {
            // Item-specific capabilities
            if ( $cap === $this->cap_prefix . 'edit_item' && isset( $args[0] ) ) {
                $post = get_post( $args[0] );
                
                if ( ! $post ) {
                    return $caps;
                }
                
                // If not the owner, require edit_others capability
                if ( $post->post_author != $user_id ) {
                    $caps[] = $this->cap_prefix . 'edit_others_items';
                } else {
                    $caps[] = $this->cap_prefix . 'edit_items';
                }
                
                return $caps;
            }
            
            // Handle delete_item meta capability
            if ( $cap === $this->cap_prefix . 'delete_item' && isset( $args[0] ) ) {
                $post = get_post( $args[0] );
                
                if ( ! $post ) {
                    return $caps;
                }
                
                // If not the owner, require delete_others capability
                if ( $post->post_author != $user_id ) {
                    $caps[] = $this->cap_prefix . 'delete_others_items';
                } else {
                    $caps[] = $this->cap_prefix . 'delete_items';
                }
                
                return $caps;
            }
            
            // Handle read_private_item meta capability
            if ( $cap === $this->cap_prefix . 'read_item' && isset( $args[0] ) ) {
                $post = get_post( $args[0] );
                
                if ( ! $post ) {
                    return $caps;
                }
                
                if ( 'private' != $post->post_status ) {
                    $caps[] = 'read';
                } else {
                    $caps[] = $this->cap_prefix . 'read_private_items';
                }
                
                return $caps;
            }
            
            return $caps;
        }
        
        /**
         * Check if current user has a specific capability
         *
         * @param string $capability Capability name
         * @param int $object_id Optional. Object ID for meta capabilities
         * @return bool True if user has the capability
         */
        public static function current_user_can( $capability, $object_id = null ) {
            if ( $object_id ) {
                return current_user_can( $capability, $object_id );
            } else {
                return current_user_can( $capability );
            }
        }
        
        /**
         * Check if a user has a specific capability
         *
         * @param int $user_id User ID
         * @param string $capability Capability name
         * @param int $object_id Optional. Object ID for meta capabilities
         * @return bool True if user has the capability
         */
        public static function user_can( $user_id, $capability, $object_id = null ) {
            if ( $object_id ) {
                return user_can( $user_id, $capability, $object_id );
            } else {
                return user_can( $user_id, $capability );
            }
        }
        
        /**
         * Get all users with a specific capability
         *
         * @param string $capability Capability name
         * @return array Array of user objects
         */
        public static function get_users_with_capability( $capability ) {
            return get_users( array(
                'role__in' => array( 'administrator', 'your_prefix_manager', 'your_prefix_editor', 'your_prefix_viewer' ),
                'fields' => 'all',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'wp_capabilities',
                        'value' => '"' . $capability . '"',
                        'compare' => 'LIKE'
                    )
                )
            ) );
        }
        
        /**
         * Get all custom roles defined by this plugin
         *
         * @return array Array of role slugs and names
         */
        public static function get_custom_roles() {
            $instance = new self();
            return $instance->roles;
        }
    }
    
    // Initialize the class
    new YOUR_PREFIX_Roles_Permissions();
}

/**
 * Helper function to check if current user has a specific capability
 *
 * @param string $capability Capability name
 * @param int $object_id Optional. Object ID for meta capabilities
 * @return bool True if user has the capability
 */
if ( ! function_exists( 'your_prefix_current_user_can' ) ) {
    function your_prefix_current_user_can( $capability, $object_id = null ) {
        return YOUR_PREFIX_Roles_Permissions::current_user_can( $capability, $object_id );
    }
}

/**
 * Helper function to check if a user has a specific capability
 *
 * @param int $user_id User ID
 * @param string $capability Capability name
 * @param int $object_id Optional. Object ID for meta capabilities
 * @return bool True if user has the capability
 */
if ( ! function_exists( 'your_prefix_user_can' ) ) {
    function your_prefix_user_can( $user_id, $capability, $object_id = null ) {
        return YOUR_PREFIX_Roles_Permissions::user_can( $user_id, $capability, $object_id );
    }
} 