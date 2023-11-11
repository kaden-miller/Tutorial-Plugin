<?php

/**
 * Plugin Name:       Website Tutorials
 * Description:       Video tutorials on how to use important featues on your website.
 * Version:           1.31
 * Author:            Kaden Miller Web Design
 * Author URI:        https://www.kadenmillerwebdesign.com/
 * License:           GPL License
 * License URI:       https://spdx.org/licenses/GPL-3.0-or-later.html
 */


 require_once 'tutorial_page.php';

// Include our updater file
include_once( plugin_dir_path( __FILE__ ) . 'update.php');

$updater = new Pizza_Updater( __FILE__ ); // instantiate our class
$updater->set_username( 'kaden-miller' ); // set username
$updater->set_repository( 'Tutorial-Plugin' ); // set repo
$updater->initialize(); // initialize the updater

function register_tutorials_post_type() {
    $labels = array(
        'name'                  => __( 'Tutorials', 'tutorial-plugin' ),
        'singular_name'         => __( 'Tutorial', 'tutorial-plugin' ),
        'menu_name'             => __( 'Tutorials', 'tutorial-plugin' ),
        'all_items'             => __( 'All Tutorials', 'tutorial-plugin' ),
        'add_new'               => __( 'Add New', 'tutorial-plugin' ),
        'add_new_item'          => __( 'Add New Tutorial', 'tutorial-plugin' ),
        'edit_item'             => __( 'Edit Tutorial', 'tutorial-plugin' ),
        'new_item'              => __( 'New Tutorial', 'tutorial-plugin' ),
        'view_item'             => __( 'View Tutorial', 'tutorial-plugin' ),
        'search_items'          => __( 'Search Tutorials', 'tutorial-plugin' ),
        'not_found'             => __( 'No tutorials found', 'tutorial-plugin' ),
        'not_found_in_trash'    => __( 'No tutorials found in Trash', 'tutorial-plugin' ),
        'parent_item_colon'     => __( 'Parent Tutorial:', 'tutorial-plugin' ),
        'featured_image'        => __( 'Featured Image', 'tutorial-plugin' ),
        'set_featured_image'    => __( 'Set featured image', 'tutorial-plugin' ),
        'remove_featured_image' => __( 'Remove featured image', 'tutorial-plugin' ),
        'use_featured_image'    => __( 'Use as featured image', 'tutorial-plugin' ),
        'archives'              => __( 'Tutorial archives', 'tutorial-plugin' ),
        'insert_into_item'      => __( 'Insert into tutorial', 'tutorial-plugin' ),
        'uploaded_to_this_item' => __( 'Uploaded to this tutorial', 'tutorial-plugin' ),
        'filter_items_list'     => __( 'Filter tutorials list', 'tutorial-plugin' ),
        'items_list_navigation' => __( 'Tutorials list navigation', 'tutorial-plugin' ),
        'items_list'            => __( 'Tutorials list', 'tutorial-plugin' ),
        'attributes'            => __( 'Tutorial attributes', 'tutorial-plugin' ),
        'name_admin_bar'        => __( 'Tutorial', 'tutorial-plugin' ),
        'item_published'        => __( 'Tutorial published', 'tutorial-plugin' ),
        'item_published_privately' => __( 'Tutorial published privately', 'tutorial-plugin' ),
        'item_reverted_to_draft' => __( 'Tutorial reverted to draft', 'tutorial-plugin' ),
        'item_scheduled'        => __( 'Tutorial scheduled', 'tutorial-plugin' ),
        'item_updated'          => __( 'Tutorial updated', 'tutorial-plugin' ),
    );

    $args = array(
        'label'                 => __( 'Tutorials', 'tutorial-plugin' ),
        'description'           => '',
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_nav_menus'     => true,
        'show_in_rest'          => true,
        'rest_base'             => '',
        'rest_controller_class' => '',
        'rest_namespace'        => '',
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'capability_type'       => 'post',
        'menu_icon' => 'dashicons-editor-help',
        'rewrite' => array( 'slug' => '', 'with_front' => true ),
        'query_var' => true,
        'menu_position' => null,
        'can_export' => false,
        'delete_with_user' => false,
        'show_in_menu_string' => '',
        'register_meta_box_cb' => null,
        'custom_supports' => '',
        'enter_title_here' => ''
        );
        register_post_type( 'tutorials', $args );
    }
    add_action( 'init', 'register_tutorials_post_type' );
    

    function create_tutorial_type_taxonomy() {
        $labels = array(
            'name' => _x('Tutorial Types', 'taxonomy general name'),
            'singular_name' => _x('Tutorial Type', 'taxonomy singular name'),
            'search_items' => __('Search Tutorial Types'),
            'all_items' => __('All Tutorial Types'),
            'edit_item' => __('Edit Tutorial Type'),
            'update_item' => __('Update Tutorial Type'),
            'add_new_item' => __('Add New Tutorial Type'),
            'new_item_name' => __('New Tutorial Type Name'),
            'menu_name' => __('Tutorial Types'),
        );
    
        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => false,
            'query_var' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => ''),
        );
    
        register_taxonomy('tutorial_type', array('tutorials'), $args);
    }
    add_action('init', 'create_tutorial_type_taxonomy', 0);
    
    function enqueue_tutorials_page_assets() {
        // Check if we're on the 'Tutorials' page
            // Enqueue CSS file
            wp_enqueue_style(
                'tutorial_page_style',
                plugin_dir_url(__FILE__) . 'css/tutorial-page.css',
                array(),
                '1.0.33',
                'all'
            );
    }
    add_action('wp_enqueue_scripts', 'enqueue_tutorials_page_assets');

function enqueue_my_scripts() {
    wp_register_script('tutorial_page_script', plugin_dir_url(__FILE__) . 'js/tutorial-page.js', array('jquery'), '1.112', true);
    wp_localize_script('tutorial_page_script', 'tutorial_page_script_vars', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        )
    );
    wp_enqueue_script('tutorial_page_script');
}
add_action('wp_enqueue_scripts', 'enqueue_my_scripts');
    


// Add custom meta boxes to 'tutorials' post type
function add_tutorials_meta_boxes() {
    add_meta_box(
        'video_link_field',
        'Video Link',
        'render_video_link_field',
        'tutorials',
        'normal',
        'default'
    );

    add_meta_box(
        'notes_field',
        'Notes',
        'render_notes_field',
        'tutorials',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'add_tutorials_meta_boxes' );

// Render the 'Video Link' field
function render_video_link_field($post) {
    $video_link = get_post_meta($post->ID, 'video_link', true);

    echo '<label for="video_link_field">Video Link:</label>';
    echo '<input type="url" id="video_link_field" name="video_link_field" value="' . esc_attr($video_link) . '" />';
}

// Render the 'Notes' field
function render_notes_field( $post ) {
    $notes = get_post_meta( $post->ID, 'notes', true );

    echo '<label for="notes_field">Notes:</label>';
    echo '<textarea id="notes_field" name="notes_field">' . esc_textarea( $notes ) . '</textarea>';
}

// Save the custom field values
function save_tutorials_meta( $post_id ) {
    if ( isset( $_POST['video_link_field'] ) ) {
        $video_link = sanitize_text_field( $_POST['video_link_field'] );
        update_post_meta( $post_id, 'video_link', $video_link );
    }

    if ( isset( $_POST['notes_field'] ) ) {
        $notes = sanitize_textarea_field( $_POST['notes_field'] );
        update_post_meta( $post_id, 'notes', $notes );
    }
}
add_action( 'save_post_tutorials', 'save_tutorials_meta' );

// Allows users to updates notes field on the tutorials page
add_action('wp_ajax_update_notes', 'update_notes');

function update_notes() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

    if ($post_id !== 0) {
        if (empty($notes)) {
            delete_post_meta($post_id, 'notes');  // add post id before 'notes'
            wp_send_json_success();
        } else {
            update_post_meta($post_id, 'notes', $notes);  // correct order of parameters
            wp_send_json_success();
        }
    }

    wp_send_json_error();
    wp_die();
}
