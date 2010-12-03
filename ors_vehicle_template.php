<?php
/*
Plugin Name: Vehicle Template
Plugin URI: http://www.orangeroomsoftware.com/website-plugin/
Version: 1.0
Author: <a href="http://www.orangeroomsoftware.com/">Orange Room Software</a>
Description: A template for Vehicles
*/

##
# Add my template button to the editor
##
add_action('admin_init', 'vehicle_template_addbutton', 1);

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
