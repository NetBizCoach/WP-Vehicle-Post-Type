<?php
/*
Plugin Name: Vehicle Template
Plugin URI: http://www.orangeroomsoftware.com/website-plugin/
Version: 1.0
Author: <a href="http://www.orangeroomsoftware.com/">Orange Room Software</a>
Description: A template for Vehicles
*/

# Post Thumbnails
add_theme_support( ‘post-thumbnails’ );

##
# Add my template button to the editor
##
add_action('admin_init', 'vehicle_template_addbutton', 1);

##
# Add shortcodes to the widgets and excerpt
##
add_filter( 'the_excerpt', 'do_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );

##
# My TinyMCE button
##
function vehicle_template_addbutton() {
  if ( is_admin() &&
       !current_user_can('edit_posts') &&
       !current_user_can('edit_pages') &&
       !get_user_option('rich_editing')) return;

  add_filter("mce_external_plugins", "add_vehicle_template_tinymce_plugin");
  add_filter('mce_buttons', 'register_vehicle_template_tinymce_button');
}

# Register the ORS button for the tinymce editor
function register_vehicle_template_tinymce_button($buttons) {
  array_push($buttons, "separator", "vehicle_template");
  return $buttons;
}

# Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function add_vehicle_template_tinymce_plugin($plugin_array) {
  $plugin_array['vehicle_template'] = '/wp-content/plugins/'.basename(dirname(__FILE__)).'/editor_plugin.js';
  return $plugin_array;
}

# Vehicle Stylesheet
function ors_vehicle_template_stylesheets() {
  wp_enqueue_style('vehicle-template-style', '/wp-content/plugins/'.basename(dirname(__FILE__)).'/style.css', 'ors-vehicle', null, 'all');
}
add_action('wp_print_styles', 'ors_vehicle_template_stylesheets', 5);

# Custom post type
add_action( 'init', 'create_vehicle_post_type' );
function create_vehicle_post_type() {
  $labels = array(
    'name' => _x('Vehicles', 'post type general name'),
    'singular_name' => _x('Vehicle', 'post type singular name'),
    'add_new' => _x('Add New', 'vehicle'),
    'add_new_item' => __('Add New Vehicle'),
    'edit_item' => __('Edit Vehicle'),
    'new_item' => __('New Vehicle'),
    'view_item' => __('View Vehicle'),
    'search_items' => __('Search Vehicles'),
    'not_found' =>  __('No vehicles found'),
    'not_found_in_trash' => __('No vehicles found in Trash'),
    'parent_item_colon' => '',
    'menu_name' => 'Vehicles'

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 6,
    'supports' => array('title','editor','author','thumbnail','excerpt'),
    'menu_icon' => '/wp-content/plugins/'.basename(dirname(__FILE__)).'/icon.png',
    'rewrite' => array(
      'slug' => 'inventory',
      'with_front' => false
    )
  );

  register_post_type( 'vehicle', $args );
}

function do_permalink( $atts ) {
  extract( shortcode_atts( array('text' => "" ), $atts) );
  if ( $text ) {
    $url = get_permalink();
    return "<a href='$url'>$text</a>";
  } else {
    return get_permalink();
  }
}
add_shortcode( 'permalink', 'do_permalink' );

// add_action("manage_vehicle_custom_column", "vehicle_column");
// function vehicle_column($column)
// {
//  global $post;
//  if ("ID" == $column) echo $post->ID;
//  elseif ("stock_num" == $column) echo '';
//  elseif ("year" == $column) echo '';
//  elseif ("make" == $column) echo '';
//  elseif ("model" == $column) echo '';
//  elseif ("title" == $column) echo $post->post_title;
//  elseif ("description" == $column) echo $post->post_content;
//  elseif ("thumbnail" == $column ) the_post_thumbnail();
// }

// add_filter("manage_edit-vehicle_columns", "vehicle_columns");
// function vehicle_columns($columns)
// {
//  $columns = array(
//    "cb" => "<input type=\"checkbox\" />",
//    "thumbnail" => "Thumbnail",
//    "title" => "Title",
//    "stock_num" => "Stock Number",
//    "year" => "Year",
//    "make" => "Make",
//    "model" => "Model",
//    "comments" => 'Comments'
//  );
//  return $columns;
// }
