<?php
/**
 * Plugin Name: MasterStudy Custom Layout
 * Description: A custom plugin to override MasterStudy LMS Course Player layout.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Enable/disable template override (set to false to disable temporarily)
define( 'MASTERSTUDY_CUSTOM_LAYOUT_ENABLED', true );

// Plugin is working - admin notice removed

/**
 * Override MasterStudy LMS templates with custom files.
 * This function intercepts template loading and provides custom templates.
 */
function masterstudy_custom_layout_override_templates( $template, $template_name, $args ) {
    // Define the custom template path
    $custom_template_path = plugin_dir_path( __FILE__ ) . 'templates/' . $template_name;
    
    // Check if custom template exists
    if ( file_exists( $custom_template_path ) ) {
        return $custom_template_path;
    }
    
    return $template;
}

// Hook into WordPress template loading system with better detection
add_filter( 'template_include', 'masterstudy_custom_layout_template_include', 99 );

// Also hook into MasterStudy's specific course player loading
add_action( 'template_redirect', 'masterstudy_custom_layout_check_course_player' );

function masterstudy_custom_layout_template_include( $template ) {
    // Check if template override is enabled
    if ( ! defined( 'MASTERSTUDY_CUSTOM_LAYOUT_ENABLED' ) || ! MASTERSTUDY_CUSTOM_LAYOUT_ENABLED ) {
        return $template;
    }
    
    // More aggressive detection - try to catch any MasterStudy related page
    $post_type = get_post_type();
    $is_masterstudy_page = false;
    
    // Check various conditions that might indicate a MasterStudy page
    if ( is_singular( array( 'stm-courses', 'stm-lessons', 'stm-quizzes', 'stm-assignments' ) ) ||
         strpos( $post_type, 'stm-' ) === 0 ||
         get_query_var( 'course_id' ) ||
         get_query_var( 'lesson_id' ) ||
         isset( $_GET['course_id'] ) ||
         isset( $_GET['lesson_id'] ) ) {
        $is_masterstudy_page = true;
    }
    
    if ( $is_masterstudy_page ) {
        // Use your full course player template
        $custom_template = plugin_dir_path( __FILE__ ) . 'templates/course-player.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    
    return $template;
}

// Alternative method: Hook into MasterStudy's template system if it exists
add_action( 'init', 'masterstudy_custom_layout_init_template_override' );

function masterstudy_custom_layout_init_template_override() {
    // Try multiple possible filter hooks that MasterStudy might use
    $possible_hooks = array(
        'stm_lms_load_template',
        'stm_lms_get_template',
        'masterstudy_lms_template',
        'stm_lms_template_path'
    );
    
    foreach ( $possible_hooks as $hook ) {
        add_filter( $hook, 'masterstudy_custom_layout_override_templates', 10, 3 );
    }
}

/**
 * Enqueue custom styles for the course player page.
 */
function masterstudy_custom_layout_enqueue_styles() {
    // More comprehensive check for MasterStudy pages
    if ( is_singular( array( 'stm-courses', 'stm-lessons', 'stm-quizzes', 'stm-assignments' ) ) || 
         is_page() && get_query_var( 'course_id' ) ) {
        
        wp_enqueue_style( 
            'masterstudy-custom-layout-style', 
            plugin_dir_url( __FILE__ ) . 'assets/css/custom-style.css',
            array(),
            '1.0.0'
        );
        
        // Also enqueue custom JS if needed
        wp_enqueue_script(
            'masterstudy-custom-layout-script',
            plugin_dir_url( __FILE__ ) . 'assets/js/custom-script.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'masterstudy_custom_layout_enqueue_styles' );

/**
 * Debug function to help troubleshoot template overrides
 */
function masterstudy_custom_layout_debug() {
    if ( current_user_can( 'administrator' ) && isset( $_GET['debug_templates'] ) ) {
        echo '<div style="background: #000; color: #0f0; padding: 10px; position: fixed; top: 0; left: 0; z-index: 9999; font-family: monospace; max-height: 300px; overflow: auto;">';
        echo 'Template Debug Info:<br>';
        echo 'Current Post Type: ' . get_post_type() . '<br>';
        echo 'Is Singular: ' . ( is_singular() ? 'Yes' : 'No' ) . '<br>';
        echo 'Plugin Active: ' . ( MASTERSTUDY_CUSTOM_LAYOUT_ENABLED ? 'Yes' : 'No' ) . '<br>';
        echo 'Template File: ' . get_page_template() . '<br>';
        echo 'Query Vars: <pre>' . print_r( $GLOBALS['wp_query']->query_vars, true ) . '</pre>';
        echo '</div>';
    }
}
add_action( 'wp_footer', 'masterstudy_custom_layout_debug' );

// Debug function removed - plugin is working

/**
 * Alternative method using STM_LMS_Templates class if available
 */
function masterstudy_custom_layout_stm_template_override() {
    if ( class_exists( 'STM_LMS_Templates' ) ) {
        // Hook into STM_LMS_Templates methods
        add_filter( 'stm_lms_template_path', 'masterstudy_custom_layout_stm_template_path', 10, 2 );
    }
}
add_action( 'plugins_loaded', 'masterstudy_custom_layout_stm_template_override', 20 );

function masterstudy_custom_layout_stm_template_path( $template_path, $template_name ) {
    $custom_template = plugin_dir_path( __FILE__ ) . 'templates/' . $template_name;
    
    if ( file_exists( $custom_template ) ) {
        return $custom_template;
    }
    
    return $template_path;
}

/**
 * Check if we're in a course player context and set up proper template loading
 */
function masterstudy_custom_layout_check_course_player() {
    // Check if this is a MasterStudy course player page
    if ( function_exists( 'STM_LMS_Course' ) && 
         ( is_singular( array( 'stm-lessons', 'stm-quizzes', 'stm-assignments' ) ) ||
           ( get_query_var( 'course_id' ) && get_query_var( 'lesson_id' ) ) ) ) {
        
        // This is likely a course player page, allow our template override
        add_filter( 'template_include', function( $template ) {
            $custom_template = plugin_dir_path( __FILE__ ) . 'templates/course-player.php';
            if ( file_exists( $custom_template ) ) {
                return $custom_template;
            }
            return $template;
        }, 999 );
    }
}
