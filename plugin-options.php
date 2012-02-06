<?php
/**
 * Load up the menu page
 */
add_action( 'admin_menu', 'ors_vehicle_options_add_page' );
function ors_vehicle_options_add_page() {
	add_submenu_page( "edit.php?post_type=vehicle", "Vehicle Options", "Options", 'read', 'vehicle_options', 'vehicle_options_do_page');
}

/**
 * Create the options page
 */
function vehicle_options_do_page() {
	$updated = false;

	if ($_POST) {
		if ($_POST['gallery-shortcode']) {
			update_option('ors-vehicle-gallery-shortcode', trim(stripslashes($_POST['gallery-shortcode'])));
			$updated = true;
		}
		if ($_POST['inquiry-form']) {
			update_option('ors-vehicle-inquiry-form', trim(stripslashes($_POST['inquiry-form'])));
			$updated = true;
		}
		if ($_POST['tell-a-friend-form']) {
			update_option('ors-vehicle-tell-a-friend-form', trim(stripslashes($_POST['tell-a-friend-form'])));
			$updated = true;
		}
		if ($_POST['vehicle-types']) {
			update_option('ors-vehicle-types', trim($_POST['vehicle-types']));
			$updated = true;
		}
		if ($_POST['global-options']) {
			update_option('ors-vehicle-global-options', trim($_POST['global-options']));
			$updated = true;
		}
	}

	?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . 'Vehicle Options' . "</h2>"; ?>

		<?php if ( $updated == true ) : ?>
		<div class="updated fade"><p><strong>Options Saved</strong></p></div>
		<?php endif; ?>

		<form method="post">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Image Gallery Shortcode</th>
					<td><input type="text" name="gallery-shortcode" size=80 value="<?php echo get_option('ors-vehicle-gallery-shortcode'); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row">Inquiry Form Shortcode</th>
					<td><input type="text" name="inquiry-form" size=80 value="<?php echo get_option('ors-vehicle-inquiry-form'); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row">Tell-A-Friend Shortcode</th>
					<td><input type="text" name="tell-a-friend-form" size=80 value="<?php echo get_option('ors-vehicle-tell-a-friend-form'); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row">Vehicle Types</th>
					<td><textarea name="vehicle-types" cols=80 rows=1><?php echo get_option('ors-vehicle-types'); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">Equipment</th>
					<td><textarea name="global-options" cols=80 rows=5><?php echo get_option('ors-vehicle-global-options'); ?></textarea></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="Save Options" />
			</p>
		</form>
	</div>
<?php
}
