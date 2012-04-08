<?php

/**
 * Meta Box for Editor
 */
add_action( 'add_meta_boxes', 'add_custom_vehicle_meta_boxes' );
function add_custom_vehicle_meta_boxes() {
  add_meta_box("vehicle_meta", 'Vehicle Information', "custom_vehicle_meta_boxes", "vehicle", "normal", "high");
}

function custom_vehicle_meta_boxes() {
  global $post;
  $custom_data = get_post_custom($post->ID);

  $options = array_filter(explode('|', $custom_data['options'][0]), 'strlen');
  sort($options);

  $global_options = explode('|', get_option('ors-vehicle-global-options'));
  $vehicle_types = explode('|', get_option('ors-vehicle-types'));
  $vehicle_categories = explode('|', get_option('ors-vehicle-categories'));

  ?>
  <div class="group">
    <p>
      <label>Sort Order:</label><br>
      <input type="text" name="vehicle_meta[sort_order]" value="<?php echo $custom_data['sort_order'][0]; ?>" size="4">
    </p>
    <p>
      Categories:<br>
      <select name="vehicle_meta[vehicle_category]">
        <option value="">None</option>
        <?php foreach ( $vehicle_categories as $category ) { ?>
        <option value="<?php echo $category; ?>" <?php echo ($custom_data['vehicle_category'][0] == $category) ? 'selected' : ''; ?>><?php echo $category; ?></option>
        <?php } ?>
        <?php if ( !in_array($custom_data['vehicle_category'][0], $vehicle_categories) ) { ?>
        <option value="<?php echo $custom_data['vehicle_category'][0]; ?>" selected><?php echo $custom_data['vehicle_category'][0]; ?></option>
        <?php } ?>
      </select>
    </p>
    <p>
      Vehicle Type:<br>
      <select name="vehicle_meta[vehicle_type]">
        <option value="">None</option>
        <?php foreach ( $vehicle_types as $type ) { ?>
        <option value="<?php echo $type; ?>" <?php echo ($custom_data['vehicle_type'][0] == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
        <?php } ?>
        <?php if ( !in_array($custom_data['vehicle_type'][0], $vehicle_types) ) { ?>
        <option value="<?php echo $custom_data['vehicle_type'][0]; ?>" selected><?php echo $custom_data['vehicle_type'][0]; ?></option>
        <?php } ?>
      </select>
    </p>

    <p>
      <label>Stock:</label><br>
      <input type="text" name="vehicle_meta[stock]" value="<?php echo $custom_data['stock'][0]; ?>" size="10">
    </p>
    <p>
      <label>VIN:</label><br>
      <input type="text" name="vehicle_meta[vin]" value="<?php echo $custom_data['vin'][0]; ?>" size="17">
    </p>
  </div>

  <div class="group">
    <p>
      Asking Price:<br>
      $<input type="text" name="vehicle_meta[asking_price]" value="<?php echo $custom_data['asking_price'][0]; ?>" size="10">
    </p>
    <p>
      Sale Price:<br>
      $<input type="text" name="vehicle_meta[sale_price]" value="<?php echo $custom_data['sale_price'][0]; ?>" size="10">
    </p>
    <p>
      Sale Expire:<br>
      <input type="text" name="vehicle_meta[sale_expire]" value="<?php echo $custom_data['sale_expire'][0]; ?>" size="10">
    </p>
  </div>

  <div class="group">
    <p>
      Year:<br>
      <input type="text" name="vehicle_meta[year]" value="<?php echo $custom_data['year'][0]; ?>" size="4">
    </p>
    <p>
      Make:<br>
      <input type="text" name="vehicle_meta[make]" value="<?php echo $custom_data['make'][0]; ?>" size="15">
    </p>
    <p>
      Model:<br>
      <input type="text" name="vehicle_meta[model]" value="<?php echo $custom_data['model'][0]; ?>" size="40">
    </p>
  </div>

  <div class="group">
    <p>
      Doors:<br>
      <input type="text" name="vehicle_meta[doors]" value="<?php echo $custom_data['doors'][0]; ?>" size="2" class="numeric">
    </p>
    <p>
      Mileage:<br>
      <input type="text" name="vehicle_meta[mileage]" value="<?php echo $custom_data['mileage'][0]; ?>" size="6" class="numeric">
    </p>
    <p>
      <label>Exterior Color:</label><br>
      <input type="text" name="vehicle_meta[exterior_color]" value="<?php echo $custom_data['exterior_color'][0]; ?>" size="20">
    </p>
    <p>
      <label>Interior Color:</label><br>
      <input type="text" name="vehicle_meta[interior_color]" value="<?php echo $custom_data['interior_color'][0]; ?>" size="20">
    </p>
  </div>

  <div class="group">
    <p>
      Engine:<br>
      <input type="text" name="vehicle_meta[engine]" value="<?php echo $custom_data['engine'][0]; ?>" size="30">
    </p>
    <p>
      Transmission:<br>
      <input type="text" name="vehicle_meta[transmission]" value="<?php echo $custom_data['transmission'][0]; ?>" size="30">
    </p>
  </div>

  <p>
    Equipment:<br>
    <input type="hidden" id="options-data" name="vehicle_meta[options]" value="<?php echo $custom_data['options'][0]; ?>">
    <ul id="options" class="bundle">
      <?php foreach ( $global_options as $value ) { if (empty($value)) continue; ?>
      <li><input type="checkbox" value="<?php echo $value; ?>" <?php echo in_array($value, $options) ? 'checked="checked"' : ''; ?>> <?php echo $value; ?></li>
      <?php } ?>
    </ul>
    <input type="text" id="add-option-text" name="add-option" value="" size="20">
    <input type="button" id="add-option-button" value="Add">
  </p>

  <?php
}

add_action( 'save_post', 'save_vehicle_postdata' );
function save_vehicle_postdata( $post_id ) {
  if ( get_post_type() != 'vehicle' ) return;

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return;
  }

  // Page Meta
  $custom_data = $_POST['vehicle_meta'];
  foreach ( $custom_data as $key => $value ) {
    update_post_meta( $post_id, $key, $value );
  }

  // Global Features and Options
  $options = explode('|', $custom_data['options']); sort($options);
  $global_options = explode('|', get_option('ors-vehicle-global-options'));
  $global_options = array_filter(array_unique(array_merge($global_options, $options)), 'strlen');
  sort($global_options);
  update_option('ors-vehicle-global-options', implode('|', $global_options));
}
