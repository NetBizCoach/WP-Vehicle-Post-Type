<?php
#
# Import
#

add_action( 'admin_menu', 'ors_vehicle_import_add_page' );
function ors_vehicle_import_add_page() {
  add_submenu_page( "edit.php?post_type=vehicle", "Vehicle Import", "Imports", 'read', 'vehicle_import', 'ors_vehicle_import_do_page');
}

/**
 * Create the options page
 */
function ors_vehicle_import_do_page() {
  $updated = false;
  if ( $_POST ) {
    if ( $_POST['import-type'] == 'KBB' ) $updated = kbb_import($_FILES['kbb-file']['tmp_name']);
    if ( $_POST['import-type'] == 'Webentory' ) $updated = webentory_import($_FILES['webentory-file']['tmp_name']);
    if ( $_POST['import-type'] == 'DealerTrend' ) $updated = dt_import($_POST['dt-id']);
  }
  ?>
  <div class="wrap">
    <?php screen_icon(); echo "<h2>" . 'Vehicle Imports' . "</h2>"; ?>

    <?php if ( $updated ) : ?>
    <div class="updated fade"><p><strong><?php echo $updated; ?></strong></p></div>
    <?php endif; ?>

    <?php ors_vehicle_import_form(); ?>
  </div>
<?php
}

function ors_vehicle_import_form() {
  ?>
  <form method="post" enctype="multipart/form-data">
    <table class="form-table">
      <tr valign="top">
        <th scope="row">KBB Import File</th>
        <td><input type="file" name="kbb-file" size=80></td>
      </tr>
      <tr>
        <td></td>
        <td>
          <p>Export from KBB file type should be CSV with all fields selected.</p>
          <input type='hidden' name='import-type' value='KBB'>
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <input type="submit" class="button-primary" value="Upload KBB File" />
        </td>
      </tr>
    </table>
  </form>

  <br/>

  <form method="post" enctype="multipart/form-data">
    <table class="form-table">
      <tr valign="top">
        <th scope="row">DealerTrend Company ID</th>
        <td><input type="text" name="dt-id" size=4></td>
      </tr>
      <tr>
        <td></td>
        <td>
          <p>Use the ID of your DealerTrend account.</p>
          <input type='hidden' name='import-type' value='DealerTrend'>
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <input type="submit" class="button-primary" value="Set DealerTrend ID" />
        </td>
      </tr>
    </table>
  </form>

  <br/>

  <form method="post" enctype="multipart/form-data">
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Webentory Import File</th>
        <td><input type="file" name="webentory-file" size=80></td>
      </tr>
      <tr>
        <td></td>
        <td>
          <p>Webentory CSV file</p>
          <input type='hidden' name='import-type' value='Webentory'>
        </td>
      </tr>
      <tr>
        <th scope="row">Image Source Location</th>
        <td><input type="text" name="image_url_prefix" size=50></td>
      </tr>
      <tr>
        <td></td>
        <td>
          <input type="submit" class="button-primary" value="Upload Webentory File" />
        </td>
      </tr>
    </table>
  </form>
  <?php
}

function kbb_import($data = false) {
  set_time_limit(86400);
  if ( $data == false ) return false;

  $row = 0;
  $added = 0;

  foreach ($data as $line) {
    if ($row++ == 0) continue;
    $csv_data = str_getcsv($line);
  }
  return "Added $added Vehicles";
}

function image_exists($path, $filename) {
  if ( file_exists(ORS_UPLOAD_DIR . $filename) ) return false;
  if ( strstr($path, 'http') ) {
    if ( `curl -sI "${path}/${filename}" | grep 'Content-Type: image/jpeg'` != '' ) return true;
  } else {
    if ( file_exists("${path}/${filename}") ) return true;
  }
  return false;
}

function webentory_import($data = false) {
  set_time_limit(86400);
  if ( $data == false ) return 'No data file.';

  $row = -1;
  $added = 0;

  # id,confirmation_code,customer_id,type,source,date_added,date_sold,status,headline,description,asking_price,sale_price,sale_expire,features,sort_order_override,date_modified,contact_name,contact_email,class,stock,vin,year,make,model,trim,body,doors,engine,trans,mileage,e_color,i_color,comments,contact,equipment,options,kbb_wholesale_base,kbb_wholesale_eng,kbb_wholesale_trans,kbb_wholesale_equip,kbb_full_wholesale,kbb_mileage_adjust,subclass,vehicle_type,kbb_equipment,sites,mileage_units,cylinders,quick_notes,contact_phone,contact_address,contact_url
  # 0  1                 2           3    4      5          6         7      8        9           10           11         12          13       14                  15            16           17            18    19    20  21   22   23    24   25   26    27     28    29      30      31      32       33      34        35      36                 37                38                  39                  40                 41                 42       43           44            45    46            47        48          49            50              51

  if ( ($handle = fopen($data, "r")) !== FALSE ) {
    while ( ($csv_data = fgetcsv($handle, 10000, ",")) !== FALSE ) {
      $row++;
      if ($row == 0) continue; # Skip first line

      $image_url_prefix = $_POST['image_url_prefix'] . "/${csv_data[1]}";
      $image_urls = array();
      $i = 1;

      while ( image_exists("${image_url_prefix}", "${i}.jpg") ) {
        $image_urls[] = "${image_url_prefix}/${i}.jpg";
        $i++;
      }

      print "Images ($image_url_prefix):<br/>" . implode('|', $image_urls) . '<br/>';

      $options = '';

      $csv_data[34] = str_getcsv( str_replace( array('{','}'), '', $csv_data[34] ) );
      $csv_data[42] = str_getcsv( str_replace( array('{','}'), '', $csv_data[42] ) );

      $result = add_vehicle_post( array(
        'created_on' => $csv_data[5],
        'sort_order' => $csv_data[14],
        'vehicle_category' => $csv_data[42][0],
        'vehicle_type' => $csv_data[43],
        'vin' => $csv_data[20],
        'stock' => $csv_data[19],
        'year' => $csv_data[21],
        'make' => $csv_data[22],
        'model' => $csv_data[23] . ' ' . $csv_data[24] . ' ' . $csv_data[25],
        'doors' => $csv_data[26],
        'asking_price' => $csv_data[10],
        'sale_price' => $csv_data[11],
        'mileage' => $csv_data[29],
        'exterior_color' => $csv_data[30],
        'interior_color' => $csv_data[31],
        'engine' => $csv_data[27],
        'transmission' => $csv_data[28],
        'options' => implode( '|', $csv_data[34] ),
        'title' => stripslashes($csv_data[8]),
        'post_content' =>  stripslashes($csv_data[32]),
        'images' => implode('|', $image_urls)
      ) );

      if ( $result ) $added++;
    }
    fclose($handle);
  }

  return "Added $added Vehicles";
}

function dt_import($id = false) {
  set_time_limit(86400);
  if ( $id == false ) return false;

  $dt_url = sprintf("http://api.dealertrend.com/%d/inventory/vehicles.csv", $id);
  $dt_file = file($dt_url);
  $added = 0;

  foreach ($dt_file as $row => $line) {
    if ($row == 0) continue; # Skip first line

    $csv_data = str_getcsv($line);

    $result = add_vehicle_post( array(
      'vin' => $csv_data[3],
      'stock' => $csv_data[4],
      'vehicle_type' => $csv_data[1],
      'year' => $csv_data[5],
      'make' => $csv_data[6],
      'model' => $csv_data[7] . ' ' . $csv_data[8],
      'asking_price' => $csv_data[16],
      'sale_price' => $csv_data[17],
      'mileage' => $csv_data[9],
      'exterior_color' => $csv_data[14],
      'interior_color' => $csv_data[15],
      'engine' => $csv_data[11],
      'transmission' => $csv_data[10],
      'options' => $csv_data[20],
      'post_content' => $csv_data[19],
      'images' => $csv_data[21]
    ) );

    if ( $result ) $added++;
  }

  return "Added $added Vehicles";
}

# Return post ID if exists
function in_vehicle_inventory($stock = false) {
  if ( $stock == false ) return false;
  global $wpdb;
  $querystr = "SELECT post_id, count(post_id) FROM {$wpdb->postmeta} WHERE (meta_key = 'stock' AND meta_value = '$stock');";
  if ( $post_id = $wpdb->get_var($wpdb->prepare($querystr)) ) return $post_id;
  else return false;
}

function add_vehicle_post($data = false) {
  if ( $data == false ) return false;

  # Flush the buffers so the web server stays running.
  @ob_flush();
  @flush();
  @ob_end_flush();

  if ( in_vehicle_inventory($data['stock']) ) return false;

  $wp_upload_dir = wp_upload_dir();
  $first_attach_id = '';

  $the_post = Array();
  $the_post['post_type']     = 'vehicle';
  $the_post['post_status']   = 'publish';

  if ( $update_post_id != false )
    $the_post['ID'] = $update_post_id;

  if ( $data['created_on'] )
    $the_post['post_date'] = $data['created_on'];

  if ( $data['updated_on'] )
    $the_post['post_modified'] = $data['updated_on'];

  if ( $data['stock'] and $data['year'] and $data['make'] and $data['model'] ) {
    $the_post['post_title'] = $data['title'];
    $the_post['post_name'] = sprintf("%s %s %s %s", $data['stock'], $data['year'], $data['make'], $data['model']);
  }

  if ( $data['post_content'] )
    $the_post['post_content'] = $data['post_content'];

  $meta_fields = array(
    'sort_order',
    'vehicle_category',
    'vehicle_type',
    'stock',
    'vin',
    'asking_price',
    'sale_price',
    'sale_expire',
    'year',
    'make',
    'model',
    'doors',
    'mileage',
    'exterior_color',
    'interior_color',
    'engine',
    'transmission',
    'options'
  );

  if ( $data['year'] == '' ) return false;
  if ( $data['make'] == '' ) return false;
  if ( $data['model'] == '' ) return false;

  if ( $post_id = wp_insert_post( $the_post ) ) {

    foreach ( $meta_fields as $key ) {
      if ( $data[$key] ) add_post_meta( $post_id, $key, $data[$key] );
    }

    if ( $data['images'] ) {
      $counter = 1;
      foreach ( explode('|', $data['images']) as $src_image ) {
        $filename = sprintf("%s-%d.jpg", $data['stock'], $counter);
        $dest_image = $wp_upload_dir['path'] . $filename;

        if ( !file_exists($dest_image) and !file_exists(ORS_UPLOAD_DIR . '/' . $filename) ) {
          echo "Adding $dest_image<br/>";
          file_put_contents($dest_image, file_get_contents($src_image));

          $wp_filetype = wp_check_filetype(basename($dest_image), null );

          $attachment = array(
             'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $dest_image ),
             'post_mime_type' => $wp_filetype['type'],
             'post_title' => sprintf("Photo #%d for Stock #%s", $counter, $data['stock']),
             'post_content' => sprintf("%s %s %s", $data['year'], $data['make'], $data['model']),
             'post_status' => 'inherit'
          );

          $attach_id = wp_insert_attachment( $attachment, $dest_image, $post_id );
          $attach_data = wp_generate_attachment_metadata( $attach_id, $dest_image );
          wp_update_attachment_metadata( $attach_id, $attach_data );

          $counter++;

          if ( $first_attach_id == '' ) $first_attach_id = $attach_id;
          flush();
        }
      }
    }

    if ( $first_attach_id ) add_post_meta($post_id, '_thumbnail_id', $first_attach_id, true);
  }

  return true;
}