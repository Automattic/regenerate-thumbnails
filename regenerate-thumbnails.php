<?php /*

**************************************************************************

Plugin Name:  Regenerate Thumbnails
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/regenerate-thumbnails/
Description:  Allows you to regenerate all thumbnails after changing the thumbnail sizes.
Version:      1.1.0
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
		// Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's folder and name it "regenerate-thumbnails-[value in wp-config].mo"
		load_plugin_textdomain( 'regenerate-thumbnails', FALSE, '/regenerate-thumbnails' );

		add_action( 'admin_menu', array(&$this, 'AddAdminMenu') );
	}


	// Register the management page
	function AddAdminMenu() {
		add_management_page( __( 'Regenerate Thumbnails', 'regenerate-thumbnails' ), __( 'Regen. Thumbnails', 'regenerate-thumbnails' ), 'manage_options', 'regenerate-thumbnails', array(&$this, 'ManagementPage') );
	}


	// The user interface plus thumbnail regenerator
	function ManagementPage() { ?>

<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap">
	<h2><?php _e('Regenerate Thumbnails', 'regenerate-thumbnails'); ?></h2>

<?php

		// If the button was clicked
		if ( !empty($_POST['regenerate-thumbnails']) ) {
			// Capability check
			if ( !current_user_can('manage_options') )
				wp_die( __('Cheatin&#8217; uh?') );

			// Form nonce check
			check_admin_referer('regenerate-thumbnails');

			// Record start time
			$rt_starttime = explode(' ', microtime() );
			$rt_starttime = $rt_starttime[1] + $rt_starttime[0];

			// Get all image attachments
			$attachments =& get_children( array(
								'post_type' => 'attachment',
								'post_mime_type' => 'image',
								'numberposts' => -1,
								'post_status' => null,
								'post_parent' => null, // any parent
								'output' => 'object',
							) );

			// Check for results
			if ( FALSE === $attachments ) {
				echo '	<p>' . __( "No attachments were found. Go upload some!", 'regenerate-thumbnails' ) . "</p>\n\n";
			}

			// Valid results, process them
			else {
				echo '	<p>' . __( "Please be patient while all thumbnails are regenerated. This can take a while if your server is slow (cheap hosting) or if you have many attachments. Do not navigate away from this page until this script is done or all thumbnails won't be resized. You will be notified when all regenerating is completed.", 'regenerate-thumbnails' ) . "</p>\n\n";

				// Find out the path to the upload directory (so we can hide it)
				$upload = wp_upload_dir();
				$uploadpath = $upload['basedir'];

				// Output progress so far to browser
				$this->flush();

				// Loop through each attachment
				$count = 0;
				echo "	<ol>\n";
				foreach ( $attachments as $attachment ) {
					$fullsizepath = get_attached_file( $attachment->ID );

					// If the file exists, regenerate thumbnail and update attachment metadata in all one go
					if ( FALSE !== $fullsizepath && @file_exists($fullsizepath) ) {
						// Start the execution time limit over but only allow 30 seconds for the image to be resized.
						// This is a better solution than just doing set_time_limit( 0 ); in my opinion.
						set_time_limit( 30 );

						wp_update_attachment_metadata( $attachment->ID, wp_generate_attachment_metadata( $attachment->ID, $fullsizepath ) );

						echo '		<li>' . str_replace( $uploadpath, '', $fullsizepath ) . " processed.</code></li>\n";
						$count++;
					}

					// Output progress so far to browser
					$this->flush();
				}
				echo "	</ol>\n\n";

				// Calculate time taken
				$rt_endtime = explode(' ', microtime() );
				$rt_endtime = $rt_endtime[1] + $rt_endtime[0];
				$rt_timetotal = number_format_i18n( $rt_endtime - $rt_starttime, 3 );

				// Output the fallback for no-JS users
				echo '	<p><noscript>' . sprintf( __( 'All done! Processed %d attachments in %s seconds.', 'regenerate-thumbnails' ), $count, $rt_timetotal ) . "</noscript></p>\n\n";

				// Output the Javascript to show the success box
?>
	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function() {
			jQuery("#message").html("<p><strong><?php echo js_escape( sprintf( __( 'All done! Processed %d attachments in %s seconds.', 'regenerate-thumbnails' ), $count, $rt_timetotal ) ); ?></strong></p>");
			jQuery("#message").show();
		});
	// ]]>
	</script>
<?php
			}
		}

		// No button click? Display the form.
		else {
?>
	<p><?php

	$optionsmisc_url = ( function_exists('admin_url') ) ? admin_url('options-misc.php') : 'options-misc.php';

	printf( __( "After you've changed either of the thumbnail dimensions on the <a href='%s'>miscellaneous settings page</a>, click the button below to regenerate all thumbnails (both the small and medium sizes) for all attachments. The old thumbnails will be kept to avoid any broken images due to hard-coded URLs.", 'regenerate-thumbnails'), $optionsmisc_url );
	
	?></p>

	<p><?php _e( "This process is not reversible, although you can just change your thumbnail dimensions to old values and click the button again if you don't like the results.", 'regenerate-thumbnails'); ?></p>

	<form method="post" action="">
<?php wp_nonce_field('regenerate-thumbnails') ?>


	<p><input type="submit" class="button" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?php _e( 'Regenerate All Thumbnails', 'regenerate-thumbnails' ) ?>" /></p>

	</form>
<?php
		} // End if button
?>
</div>

<?php
	}


	// Outputs all HTML up to this point to the browser
	function flush() {
		ob_flush();
		flush();
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $RegenerateThumbnails; $RegenerateThumbnails = new RegenerateThumbnails();' ) );

?>