<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <?php
  $path  = '';
  if ( !defined('WP_LOAD_PATH') ) {
  	$classic_root = dirname(dirname(dirname(dirname(__FILE__)))) . '/';
  	if (file_exists( $classic_root . 'wp-load.php') ) define( 'WP_LOAD_PATH', $classic_root);
  	else if (file_exists( $path . 'wp-load.php') ) define( 'WP_LOAD_PATH', $path);
  	else exit("Could not find wp-load.php");
  }
  require_once( WP_LOAD_PATH . 'wp-load.php');
  ?>
  <head>
  	<title>Vehicle Editor</title>
  	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/jquery/jquery.js"></script>
  	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
  	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
  	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
  </head>

  <body id="editor_popup">
    <h1>Vehicle Template</h1>
    <br/>
    <table width="100%" cellpadding="5" cellspacing="0" border="0">
      <tr><td align="right" width="90">Price $</td><td><input id="price_input" name="price" size="10"/></td></tr>
      <tr><td align="right">Stock #</td><td><strong><input id="stock_input" name="stock" size="10"/></strong></td></tr>
      <tr><td align="right">Color</td><td><strong><input id="color_input" name="color" size="10"/></strong></td></tr>
      <tr><td align="right">Engine</td><td><strong><input id="engine_input" name="engine" size="10"/></strong></td></tr>
      <tr><td align="right">Transmission</td><td><strong><input id="transmission_input" name="transmission" size="10"/></strong></td></tr>
      <tr><td align="right">Mileage</td><td><strong><input id="mileage_input" name="mileage" size="10"/></strong></td></tr>
      <tr><td colspan="2">Description<br/><textarea id="description_input" name="description" cols="60" rows="5"></textarea></td></tr>
      <tr><td colspan="2">Equipment<br/><textarea id="equipment_input" name="equipment" cols="60" rows="5"></textarea></td></tr>
      <tr><td colspan="2">Contact<br/><textarea id="contact_input" name="contact" cols="60" rows="5"></textarea></td></tr>
    </table>
    <p style="text-align:right">
      <button type="button" onclick="add_template();">Update Vehicle</button>
    </p>

    <script type="text/javascript" charset="utf-8">
      var vehicle = '';
      var excerpt = '';
    	
    	function load_form() {
        if (window.tinyMCE) {
      	  if (tinyMCE.activeEditor.dom.get('price'))
        	  document.getElementById('price_input').value = tinyMCE.activeEditor.dom.get('price').innerHTML;
        	if (tinyMCE.activeEditor.dom.get('stock'))
            document.getElementById('stock_input').value = tinyMCE.activeEditor.dom.get('stock').innerHTML;
          if (tinyMCE.activeEditor.dom.get('color'))
            document.getElementById('color_input').value = tinyMCE.activeEditor.dom.get('color').innerHTML;
          if (tinyMCE.activeEditor.dom.get('engine'))
            document.getElementById('engine_input').value = tinyMCE.activeEditor.dom.get('engine').innerHTML;
          if (tinyMCE.activeEditor.dom.get('transmission'))
            document.getElementById('transmission_input').value = tinyMCE.activeEditor.dom.get('transmission').innerHTML;
          if (tinyMCE.activeEditor.dom.get('mileage'))
            document.getElementById('mileage_input').value = tinyMCE.activeEditor.dom.get('mileage').innerHTML;
          if (tinyMCE.activeEditor.dom.get('description'))
            document.getElementById('description_input').value = tinyMCE.activeEditor.dom.get('description').innerHTML;
          if (tinyMCE.activeEditor.dom.get('equipment'))
            document.getElementById('equipment_input').value = tinyMCE.activeEditor.dom.get('equipment').innerHTML;
          if (tinyMCE.activeEditor.dom.get('contact'))
            document.getElementById('contact_input').value = tinyMCE.activeEditor.dom.get('contact').innerHTML;
        }
    	}
      
      function build_vehicle() {
        vehicle  = '<p>[gallery link="file" columns="5"]</p>';
        vehicle += '<p id="vehicle">';
        vehicle += '<label>Price:</label>$<span id="price">' + document.getElementById('price_input').value + '</span><br/>';
        vehicle += '<label>Stock:</label>#<span id="stock">' + document.getElementById('stock_input').value + '</span><br/>';
        vehicle += '<label>Color:</label><span id="color">' + document.getElementById('color_input').value + '</span><br/>';
        if (document.getElementById('engine_input').value)
          vehicle += '<label>Engine:</label><span id="engine">' + document.getElementById('engine_input').value + '</span><br/>';
        if (document.getElementById('transmission_input').value)
          vehicle += '<label>Transmission:</label><span id="transmission">' + document.getElementById('transmission_input').value + '</span><br/>';
        if (document.getElementById('mileage_input').value)
          vehicle += '<label>Mileage:</label><span id="mileage">' + document.getElementById('mileage_input').value + '</span><br/>';
        vehicle += '</p>';
        vehicle += '<p>Description:<br/><span id="description">' + document.getElementById('description_input').value + '</span></p>';
        vehicle += '<p>Equipment:<br/><span id="equipment">' + document.getElementById('equipment_input').value + '</span></p>';
        if (document.getElementById('contact_input').value)
          vehicle += '<p>Contact:<br/><span id="contact">' + document.getElementById('contact_input').value + '</span></p>';
        return vehicle;
      }
      
      function build_excerpt() {
        excerpt += '<p class="vehicle">';
        excerpt += '<span class="excerpt-price">$' + document.getElementById('price_input').value + '</span><br/>';
        excerpt += '<label>Stock:</label>#<span class="excerpt-stock">' + document.getElementById('stock_input').value + '</span>, ';
        excerpt += '<label>Color:</label><span class="excerpt-color">' + document.getElementById('color_input').value + '</span>, ';
        if (document.getElementById('engine_input').value)
          excerpt += '<label>Engine:</label><span class="excerpt-engine">' + document.getElementById('engine_input').value + '</span>, ';
        if (document.getElementById('transmission_input').value)
          excerpt += '<label>Transmission:</label><span class="excerpt-transmission">' + document.getElementById('transmission_input').value + '</span>, ';
        excerpt += '<label>Mileage:</label><span class="excerpt-mileage">' + document.getElementById('mileage_input').value + '</span>';
        excerpt += '</p>';
        return excerpt;
      }
      
      function add_template() {
      	// Add vehicle to Excerpt
      	if (parent.document.getElementById('excerpt')) {
      	  var new_excerpt = document.createTextNode(build_excerpt());
        	parent.document.getElementById('excerpt').innerHTML = '';
        	parent.document.getElementById('excerpt').appendChild(new_excerpt);
      	}
      	
        // Add vehicle to editor
        if (window.tinyMCE) {
      		tinyMCE.execCommand('mceSetContent', false, build_vehicle());
      		tinyMCEPopup.editor.execCommand('mceRepaint');
      		tinyMCEPopup.close();
      	}
    	}
    	
    	load_form();
    </script>
  </body>
</html>