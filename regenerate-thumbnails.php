<?php /*

**************************************************************************

Plugin Name:  Regenerate Thumbnails
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/regenerate-thumbnails/
Description:  Allows you to regenerate all thumbnails after changing the thumbnail sizes.
Version:      2.0.2
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

Copyright (C) 2008 Viper007Bond

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

class RegenerateThumbnails {

	// Plugin initialization
	function RegenerateThumbnails() {
		if ( !function_exists('admin_url') )
			return false;

		// Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's "localization" folder and name it "regenerate-thumbnails-[value in wp-config].mo"
		load_plugin_textdomain( 'regenerate-thumbnails', false, '/regenerate-thumbnails/localization' );

		add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
		add_action( 'admin_enqueue_scripts', array(&$this, 'admin_enqueues') );
		add_action( 'wp_ajax_regeneratethumbnail', array(&$this, 'ajax_process_image') );
	}


	// Register the management page
	function add_admin_menu() {
		add_management_page( __( 'Regenerate Thumbnails', 'regenerate-thumbnails' ), __( 'Regen. Thumbnails', 'regenerate-thumbnails' ), 'manage_options', 'regenerate-thumbnails', array(&$this, 'regenerate_interface') );
	}


	// Enqueue the needed Javascript and CSS
	function admin_enqueues( $hook_suffix ) {
		if ( 'tools_page_regenerate-thumbnails' != $hook_suffix )
			return;

		wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( 'jquery-ui/ui.progressbar.js', __FILE__ ), array('jquery-ui-core'), '1.7.2' );
		wp_enqueue_style( 'jquery-ui-regenthumbs', plugins_url( 'jquery-ui/redmond/jquery-ui-1.7.2.custom.css', __FILE__ ), array(), '1.7.2' );
	}


	// The user interface plus thumbnail regenerator
	function regenerate_interface() {
		global $wpdb;

		?>

<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap regenthumbs">
	<h2><?php _e('Regenerate Thumbnails', 'regenerate-thumbnails'); ?></h2>

<?php

		// If the button was clicked
		if ( !empty($_POST['regenerate-thumbnails']) ) {
			// Capability check
			if ( !current_user_can('manage_options') )
				wp_die( __('Cheatin&#8217; uh?') );

			// Form nonce check
			check_admin_referer( 'regenerate-thumbnails' );

			// Get all image attachments
			/*
			$images =& get_children( array(
								'post_type' => 'attachment',
								'post_mime_type' => 'image',
								'numberposts' => -1,
								'post_status' => 'inherit',
								'post_parent' => null, // any parent
								'output' => 'object',
							) );
			*/

			// Just query for the IDs only to reduce memory usage
			$images = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'" );

			// Make sure there are images to process
			if ( empty($images) ) {
				echo '	<p>' . sprintf( __( "Unable to find any images. Are you sure <a href='%s'>some exist</a>?", 'regenerate-thumbnails' ), admin_url('upload.php?post_mime_type=image') ) . "</p>\n\n";
			}

			// Valid results
			else {
				echo '	<p>' . __( "Please be patient while all thumbnails are regenerated. This can take a while if your server is slow (cheap hosting) or if you have many images. Do not navigate away from this page until this script is done or all thumbnails won't be resized. You will be notified via this page when all regenerating is completed.", 'regenerate-thumbnails' ) . '</p>';

				// Generate the list of IDs
				$ids = array();
				foreach ( $images as $image )
					$ids[] = $image->ID;
				$ids = implode( ',', $ids );

				$count = count( $images );
?>


	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'regenerate-thumbnails' ) ?></em></p></noscript>

	<div id="regenthumbsbar" style="position:relative;height:25px;">
		<div id="regenthumbsbar-percent" style="position:absolute;left:50%;top:50%;width:50px;margin-left:-25px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var rt_images = [<?php echo $ids; ?>];
			var rt_total = rt_images.length;
			var rt_count = 1;
			var rt_percent = 0;

			$("#regenthumbsbar").progressbar();
			$("#regenthumbsbar-percent").html( "0%" );

			function RegenThumbs( id ) {
				$.post( "admin-ajax.php", { action: "regeneratethumbnail", id: id }, function() {
					rt_percent = ( rt_count / rt_total ) * 100;
					$("#regenthumbsbar").progressbar( "value", rt_percent );
					$("#regenthumbsbar-percent").html( Math.round(rt_percent) + "%" );
					rt_count = rt_count + 1;

					if ( rt_images.length ) {
						RegenThumbs( rt_images.shift() );
					} else {
						$("#message").html("<p><strong><?php echo js_escape( sprintf( __( 'All done! Processed %d images.', 'regenerate-thumbnails' ), $count ) ); ?></strong></p>");
						$("#message").show();
					}

				});
			}

			RegenThumbs( rt_images.shift() );
		});
	// ]]>
	</script>
<?php
			}
		}

		// No button click? Display the form.
		else {
?>
	<p><?php printf( __( "Use this tool to regenerate thumbnails for all images that you have uploaded to your blog. This is useful if you've changed any of the thumbnail dimensions on the <a href='%s'>media settings page</a>. Old thumbnails will be kept to avoid any broken images due to hard-coded URLs.", 'regenerate-thumbnails'), admin_url('options-media.php') ); ?></p>

	<p><?php _e( "This process is not reversible, although you can just change your thumbnail dimensions back to the old values and click the button again if you don't like the results.", 'regenerate-thumbnails'); ?></p>

	<p><?php _e( "To begin, just press the button below.", 'regenerate-thumbnails'); ?></p>

	<form method="post" action="">
<?php wp_nonce_field('regenerate-thumbnails') ?>


	<p><input type="submit" class="button hide-if-no-js" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?php _e( 'Regenerate All Thumbnails', 'regenerate-thumbnails' ) ?>" /></p>

	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'regenerate-thumbnails' ) ?></em></p></noscript>

	</form>
<?php
		} // End if button
?>
</div>

<?php
	}


	// Process a single image ID (this is an AJAX handler)
	function ajax_process_image() {
		if ( !current_user_can( 'manage_options' ) )
			die('-1');

		$id = (int) $_REQUEST['id'];

		if ( empty($id) )
			die('-1');

		$fullsizepath = get_attached_file( $id );

		if ( false === $fullsizepath || !file_exists($fullsizepath) )
			die('-1');

		set_time_limit( 60 );

		if ( wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $fullsizepath ) ) )
			die('1');
		else
			die('-1');
	}
}

// Start up this plugin
add_action( 'init', 'RegenerateThumbnails' );
function RegenerateThumbnails() {
	global $RegenerateThumbnails;
	$RegenerateThumbnails = new RegenerateThumbnails();
}

?>