<?php
/*
Plugin Name: Vehicle Post Type
Plugin URI: https://github.com/orangeroomsoftware/WP-Vehicle-Post-Type
Version: 1.0
Author: <a href="http://www.orangeroomsoftware.com/">Orange Room Software</a>
Description: A post type for Vehicles
*/

# Set plugin paths
define('VEHICLE_PLUGIN_URL', '/wp-content/plugins/' . basename(dirname(__FILE__)) );
define('VEHICLE_PLUGIN_DIR', dirname(__FILE__));

# Setup upload paths
$uploads = wp_upload_dir();
if ( !defined('ORS_UPLOAD_DIR') ) define('ORS_UPLOAD_DIR', $uploads['path']);
if ( !defined('ORS_UPLOAD_URL') ) define('ORS_UPLOAD_URL', $uploads['url']);

# Post Thumbnails
add_theme_support( 'post-thumbnails' );

/*
 * Add shortcodes to the widgets and excerpt
*/
add_filter( 'get_the_excerpt', 'do_shortcode' );
add_filter( 'the_excerpt', 'do_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );

# Site Stylesheet
add_action('wp_print_styles', 'ors_vehicle_template_stylesheets', 6);
function ors_vehicle_template_stylesheets() {
  wp_enqueue_style('vehicle-template-style', VEHICLE_PLUGIN_URL . "/style.css", 'ors-vehicle', null, 'all');
}

# Admin Stylesheet
add_action('admin_print_styles', 'ors_vehicle_plugin_admin_stylesheets', 6);
function ors_vehicle_plugin_admin_stylesheets() {
  wp_enqueue_style('vehicle-vehicle-admin-style', VEHICLE_PLUGIN_URL . "/admin-style.css", 'ors-admin', null, 'all');
}

# Admin Javascript
add_action('admin_print_scripts', 'ors_vehicle_plugin_admin_script', 5);
function ors_vehicle_plugin_admin_script() {
  wp_register_script( 'ors_vehicle_plugin_admin_script', VEHICLE_PLUGIN_URL . "/admin-script.js", 'jquery', time() );
  wp_enqueue_script('ors_vehicle_plugin_admin_script');
}

/*
 * First time activation
*/
register_activation_hook( __FILE__, 'activate_vehicle_post_type' );
function activate_vehicle_post_type() {
  create_vehicle_post_type();
  flush_rewrite_rules();
  add_option( 'ors-vehicle-default-sort', 'price ASC', '', true );
  add_option( 'ors-vehicle-global-options',  'Air Conditioning|Climate Control|Power Steering|Power Disc Brakes|Power Windows|Power Door Locks|Tilt Wheel|Telescoping Wheel|Steering Wheel Audio Controls|Cruise Control|AM/FM Stereo|Cassette|Single Compact Disc|Multi Compact Disc|CD Auto Changer|Premium Sound|Integrated Phone|Navigation System|Parking Sensors|Dual Front Airbags|Side Front Airbags|Front and Rear Side Airbags|ABS 4-Wheel|Traction Control|Leather|Full Leather|Power Seat|Dual Power Seats|Flip-up Sun Roof|Sliding Sun Roof|Moon Roof|Alloy Wheels', '', true );
  add_option( 'ors-vehicle-types', 'Car|Truck|SUV|Van|Minivan|Wagon', '', true );
}

# Setup my query vers
add_filter('query_vars', 'ors_queryvars' );
function ors_queryvars( $qvars )
{
  $qvars[] = 'vehicle_category';
  return $qvars;
}

# Rewrite rules
add_filter( 'rewrite_rules_array', 'ors_insert_rewrite_rules' );
function ors_insert_rewrite_rules( $rules )
{
  $new_rules = array();
  $new_rules['vehicles/category/([^/]+)$'] = 'index.php?post_type=vehicle&vehicle_category=$matches[1]';
  $new_rules['vehicles/category/([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?post_type=vehicle&vehicle_category=$matches[1]&paged=$matches[2]';
  return $new_rules + $rules;
}

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
    'supports' => array('title', 'excerpt', 'gallery', 'thumbnail', 'editor', 'tags'),
    'menu_icon' => VEHICLE_PLUGIN_URL . '/icon.png',
    'rewrite' => array(
      'slug' => 'vehicles',
      'with_front' => false
    )
  );

  register_post_type( 'vehicle', $args );
}

add_action("manage_posts_custom_column",  "vehicle_custom_columns");
function vehicle_custom_columns($column){
  global $post;
  $custom = get_post_custom();
  if ( get_post_type() != 'vehicle' ) return;

  switch ($column) {
    case "thumbnail":
      if ( has_post_thumbnail( $post->ID ) ) {
        the_post_thumbnail(array(50,50));
      }
      break;
    case "sort_order":
      echo $custom["sort_order"][0];
      break;
    case "asking_price":
      echo '$' . $custom["asking_price"][0];
      break;
    case "vehicle_category":
      echo $custom["vehicle_category"][0];
      break;
    case "ymm":
      echo "{$custom["year"][0]} {$custom["make"][0]} {$custom["model"][0]}";
      break;
    case "color":
      echo "{$custom["exterior_color"][0]} {$custom["interior_color"][0]}";
      break;
    case "mileage":
      echo $custom["mileage"][0];
      break;
    case "vehicle_type":
      echo $custom["vehicle_type"][0];
      break;
  }
}

/**
 * Admin Includes
 */
require_once ( VEHICLE_PLUGIN_DIR . '/plugin-options.php' );
require_once ( VEHICLE_PLUGIN_DIR . '/post-meta-input.php' );
require_once ( VEHICLE_PLUGIN_DIR . '/plugin-import.php' );

/*
 * Custom Query for this post type to sort by price
 * Don't use this sort in Admin
*/
if ( !is_admin() ) add_filter( 'posts_clauses', 'ors_vehicle_query' );
function ors_vehicle_query($clauses) {
  global $wpdb, $ors_vehicle_cookies, $wp_query;

  if ( !strstr($clauses['where'], 'vehicle') or is_single() ) return $clauses;

  $clauses['where'] = " AND {$wpdb->posts}.post_type = 'vehicle' AND {$wpdb->posts}.post_status = 'publish' ";
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'sort_order' and {$wpdb->postmeta}.meta_value != '' order by meta_id desc limit 1) as decimal) as sort_order";
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'asking_price' order by meta_id desc limit 1) as decimal) as price";
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'mileage' order by meta_id desc limit 1) as decimal) as mileage";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'vehicle_category' order by meta_id desc limit 1) as vehicle_category";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'vehicle_type' order by meta_id desc limit 1) as vehicle_type";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'exterior_color' order by meta_id desc limit 1) as exterior_color";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'interior_color' order by meta_id desc limit 1) as interior_color";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'make' order by meta_id desc limit 1) as make";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'model' order by meta_id desc limit 1) as model";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'engine' order by meta_id desc limit 1) as engine";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'transmission' order by meta_id desc limit 1) as transmission";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'options' order by meta_id desc limit 1) as options";
  $clauses['having'] = array();
  $clauses['orderby'] = '';

  if ( isset($wp_query->query_vars['vehicle_category']) ) {
    $clauses['having'][] = "lower(vehicle_category) like '%" . str_replace('-', ' ', urldecode($wp_query->query_vars['vehicle_category'])) . "%'";
  }

  if ( isset($ors_vehicle_cookies['text_search']) and $ors_vehicle_cookies['text_search'] != '' ) {
    $clauses['having']['textsearch']  = "(make like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or post_title like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or post_content like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or model like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or engine like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or transmission like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or exterior_color like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or interior_color like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or options like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= ")";
  }

  $search_params = array('vehicle_type');
  foreach ($search_params as $param) {
    if ( isset($ors_vehicle_cookies[$param]) and $ors_vehicle_cookies[$param] != 'All' and $ors_vehicle_cookies[$param] != '' ) {
      $clauses['having'][] = "$param = '$ors_vehicle_cookies[$param]'";
    }
  }
  if ( !empty($clauses['having']) ) {
    $clauses['where'] .= ' HAVING ' . implode(' and ', $clauses['having']);
  } else {
    unset($clauses['having']);
  }

  $order_params = array('price' => 'price_near', 'mileage' => 'mileage_near');
  foreach ($order_params as $field => $param) {
    if ( isset($ors_vehicle_cookies[$param]) and $ors_vehicle_cookies[$param] != '' ) {
      $clauses['orderby'] .= "ABS({$ors_vehicle_cookies[$param]} - $field)";
    }
  }

  $default_sort = get_option('ors-vehicle-default-sort');
  if ( !$default_sort ) $default_sort = 'price ASC';
  if ( $clauses['orderby'] == '' ) $clauses['orderby'] = get_option('ors-vehicle-default-sort');

  // print "<pre>" . print_r($clauses, 1) . "</pre>";
  return $clauses;
}

/**
 * Cookies to save search params
 */
add_action( 'init', 'ors_vehicle_set_cookies');
function ors_vehicle_set_cookies() {
  global $ors_vehicle_cookies;
  $search_params = array('price_near', 'mileage_near', 'vehicle_type', 'exterior_color', 'text_search');

  foreach ($search_params as $param) {
    if ( isset($_POST[$param]) ) {
      if ( $_POST['clear'] == 'Clear' ) $_POST[$param] = '';
      $ors_vehicle_cookies[$param] = $_POST[$param];
      setcookie($param, $_POST[$param], time() + 3600, COOKIEPATH, COOKIE_DOMAIN, false);
    }

    elseif ( isset($_COOKIE[$param]) ) {
      $ors_vehicle_cookies[$param] = $_COOKIE[$param];
    }
  }
}

function explode_meta_data() {
  $custom = array();
  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }
  return $custom;
}

/**
 * Output Filters
 */

add_filter("manage_edit-vehicle_columns", "vehicle_edit_columns");
function vehicle_edit_columns($columns){
  if ( get_post_type() != 'vehicle' ) return $columns;
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "thumbnail" => "Photo",
    "title" => "Title",
    "ymm" => "Year Make Model",
    "asking_price" => "Price",
    "vehicle_category" => "Category",
    "sort_order" => "Sort",
    "author" => "Author",
    "date" => "Date Added"
  );

  return $columns;
}

/*
 * Search Box
*/
add_filter( 'loop_start', 'ors_vehicle_search_box' );
function ors_vehicle_search_box() {
  if ( get_post_type() != 'vehicle' ) return;

  if ( is_single() ) {
    print '<p><a class="back-button" href="javascript:history.go(-1)">◄ Back</a></p>';
    return;
  }

  global $ors_vehicle_cookies;
  $vehicle_types = explode('|', get_option('ors-vehicle-types'));
  ?>
  <div id='ors-vehicle-search-box'>
    <form method="POST">
      Type <select id="vehicle_type" type="text" name="vehicle_type">
        <option <?php echo $ors_vehicle_cookies['vehicle_type'] == 'All' ? 'selected' : ''; ?>>All</option>
        <?php foreach ( $vehicle_types as $type ) { ?>
        <option <?php echo $ors_vehicle_cookies['vehicle_type'] == $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
        <?php } ?>
      </select>
      Price Near <input id="price_near" type="text" name="price_near" size=6 value="<?php echo $ors_vehicle_cookies['price_near'] ?>">
      Mileage Near <input id="mileage_near" type="text" name="mileage_near" size=6 value="<?php echo $ors_vehicle_cookies['mileage_near'] ?>">
      Text <input id="text_search" type="text" name="text_search" size=30 value="<?php echo $ors_vehicle_cookies['text_search'] ?>">
      <input type="hidden" name="post_type" value="vehicle">
      <input type="submit" name="submit" value="Search">
      <input type="submit" name="clear" value="Clear">
    </form>
  </div>
  <?php
}

/*
 * Fix the content
*/
add_filter( 'the_title', 'vehicle_title_filter' );
function vehicle_title_filter($content) {
  if ( !in_the_loop() or get_post_type() != 'vehicle' ) return $content;

  $custom = explode_meta_data();

  if ( $custom['sold'] == 'yes' ) $sold = true; else $sold = false;

  global $the_real_title;
  $the_real_title = $content;

  $output = '';

  if ( ($custom['asking_price'] and $custom['asking_price'] != '0') or $sold )
    $output .= '<span class="price">' . ($sold ? 'Sold' : '$'.number_format($custom['asking_price'])) . '</span>';
  if ( $custom['year'] or $custom['make'] or $custom['model'] )
    $output .= '<span class="title">' . "{$custom['year']} {$custom['make']} {$custom['model']}" . '</span>';
  else
    $output .= '<span class="title">' . $content . '</span>';

  return $output;
}

add_filter('the_excerpt', 'vehicle_excerpt_filter');
function vehicle_excerpt_filter($content) {
  if ( get_post_type() != 'vehicle' ) return $content;

  $custom = explode_meta_data();

  $output  = '';

  if ( !has_post_thumbnail( $post->ID ) ) {
    $output .= '<a href="' . get_permalink() . '"><img width="150" height="150" src="' . VEHICLE_PLUGIN_URL . '/nophoto.jpg" class="attachment-thumbnail wp-post-image" alt="No Photo" title="' . $address . '"></a>';
  }

  global $the_real_title;
  $output .= "<h2>$the_real_title</h2>";

  $info = array();
  if ( $custom['doors'] )
    $info[] = "{$custom['doors']} Door";
  if ( $custom['exterior_color'] )
    $info[] = ucwords($custom['exterior_color']) . " Exterior";
  if ( $custom['interior_color'] )
    $info[] = ucwords($custom['interior_color']) . " Interior";
  if ( $custom['mileage'] )
    $info[] = number_format($custom['mileage']) . " Miles";

  if ( $info ) $output .= "<p class='meta'>" . implode(', ', $info) . "</p>";
  $output .= "<p class='excerpt'>$content</p>";

  return $output;
}

add_filter('the_content', 'vehicle_content_filter');
function vehicle_content_filter($content) {
  if ( !is_single() or get_post_type() != 'vehicle' ) return $content;

  $custom = explode_meta_data();

  $options = array_filter(explode('|', $custom['options']), 'strlen');

  $output  = get_option('ors-vehicle-gallery-shortcode') . '<br/>';
  $output .= $content;
  $output .= 'Vehicle Details:';
  $output .= "<ul class='meta'>";
  if ( $custom['engine'] )
    $output .= "  <li>Engine: " . $custom['engine'] . '</li>';
  if ( $custom['transmission'] )
    $output .= "  <li>Transmission: " . $custom['transmission'] . '</li>';
  if ( $custom['mileage'] )
    $output .= "  <li>Mileage: " . number_format($custom['mileage']) . ' Miles</li>';
  if ( $custom['doors'] )
    $output .= "  <li>Doors: " . $custom['doors'] . '</li>';
  if ( $custom['exterior_color'] )
    $output .= "  <li>Exterior Color: " . $custom['exterior_color'] . '</li>';
  if ( $custom['interior_color'] )
    $output .= "  <li>Interior Color: " . $custom['interior_color'] . '</li>';
  $output .= "</ul>";

  if ( is_array($options) and !empty($options) ) {
    $output .= "<div class='options'>";
    $output .= "Equipment:<br>";
    $output .= '<ul>';
    foreach ( $options as $value ) {
      $output .= '  <li>' . $value . '</li>';
    }
    $output .= '</ul></div>';
  }

  if ( $inquiry = get_option('ors-vehicle-inquiry-form') ) {
    $output .= '<div class="inquiry-form">';
    $output .= '<h2>Send Email Inquiry</h2>';
    $output .= $inquiry;
    $output .= '</div>';
  }

  if ( $tell_a_friend = get_option('ors-vehicle-tell-a-friend-form') ) {
    $output .= '<div class="inquiry-form">';
    $output .= '<h2>Tell-A-Friend</h2>';
    $output .= $tell_a_friend;
    $output .= '</div>';
  }

  return $output;
}
