<?php /*

**************************************************************************

Plugin Name:  Regenerate Thumbnails
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/regenerate-thumbnails/
Description:  Allows you to regenerate all thumbnails after changing the thumbnail sizes.
Version:      2.3.1
Author:       Alex Mills (Viper007Bond)
Author URI:   http://www.viper007bond.com/
Text Domain:  regenerate-thumbnails

**************************************************************************

Copyright (C) 2008-2017 Alex Mills (Viper007Bond)

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
	public $menu_id;

	// Plugin initialization
	public function __construct() {
		// Load up the localization file if we're using WordPress in a different language
		load_plugin_textdomain( 'regenerate-thumbnails' );

		add_action( 'admin_menu',                              array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts',                   array( $this, 'admin_enqueues' ) );
		add_action( 'wp_ajax_regeneratethumbnail',             array( $this, 'ajax_process_image' ) );
		add_filter( 'media_row_actions',                       array( $this, 'add_regenerate_link_to_media_list_view' ), 10, 2 );
		add_action( 'admin_head-upload.php',                   array( $this, 'add_bulk_actions_via_javascript' ) );
		add_action( 'admin_action_bulk_regenerate_thumbnails', array( $this, 'bulk_action_handler' ) ); // Top drowndown
		add_action( 'admin_action_-1',                         array( $this, 'bulk_action_handler' ) ); // Bottom dropdown (assumes top dropdown = default value)
		add_action( 'attachment_submitbox_misc_actions',       array( $this, 'add_button_to_media_edit_page' ), 99 ); // Button on media edit screen

		// Add a regenerate button to the list of fields in the edit media modal
		// Ideally this would with the action links but I'm not good enough with JavaScript to do it
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_button_to_edit_media_modal_fields_area' ), 99, 2 );

		// Allow people to change what capability is required to use this plugin
		$this->capability = apply_filters( 'regenerate_thumbs_cap', 'manage_options' );
	}


	// Register the management page
	public function add_admin_menu() {
		$this->menu_id = add_management_page( __( 'Regenerate Thumbnails', 'regenerate-thumbnails' ), __( 'Regen. Thumbnails', 'regenerate-thumbnails' ), $this->capability, 'regenerate-thumbnails', array($this, 'regenerate_interface') );
	}


	// Enqueue the needed Javascript and CSS
	public function admin_enqueues( $hook_suffix ) {
		if ( $hook_suffix != $this->menu_id ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-progressbar' );

		wp_enqueue_style( 'jquery-ui-regenthumbs', plugins_url( 'jquery-ui/redmond/jquery-ui-1.7.2.custom.css', __FILE__ ), array(), '1.7.2' );
	}


	/**
	 * Creates a nonced URL to the plugin's admin page for a given set of attachment IDs.
	 *
	 * @param array $ids An array of attachment IDs that should be regenerated.
	 *
	 * @return string The nonced URL to the admin page.
	 */
	public function create_page_url( $ids ) {
		$url_args = array(
			'page'     => 'regenerate-thumbnails',
			'goback'   => 1,
			'ids'      => implode( ',', $ids ),
			'_wpnonce' => wp_create_nonce( 'regenerate-thumbnails' ), // Can't use wp_nonce_url() as it escapes HTML entities
		);

		// https://core.trac.wordpress.org/ticket/17923
		$url_args = array_map( 'rawurlencode', $url_args );

		return add_query_arg( $url_args, admin_url( 'tools.php' ) );
	}


	// Add a "Regenerate Thumbnails" link to the media row actions
	public function add_regenerate_link_to_media_list_view( $actions, $post ) {
		if ( 'image/' != substr( $post->post_mime_type, 0, 6 ) || ! current_user_can( $this->capability ) )
			return $actions;

		$url = wp_nonce_url( admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $post->ID ), 'regenerate-thumbnails' );
		$actions['regenerate_thumbnails'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( __( "Regenerate the thumbnails for this single image", 'regenerate-thumbnails' ) ) . '">' . _x( 'Regenerate Thumbnails', 'action of regenerate thumbnails', 'regenerate-thumbnails' ) . '</a>';

		return $actions;
	}

	/**
	 * Add a "Regenerate Thumbnails" button to the submit box on the non-modal "Edit Media" screen for an image attachment.
	 */
	public function add_button_to_media_edit_page() {
		global $post;

		if ( 'image/' != substr( $post->post_mime_type, 0, 6 ) || ! current_user_can( $this->capability ) ) {
			return;
		}

		echo '<div class="misc-pub-section misc-pub-regenerate-thumbnails">';
		echo '<a href="' . esc_url( $this->create_page_url( array( $post->ID ) ) ) . '" class="button-secondary button-large" title="' . esc_attr( __( "Regenerate the thumbnails for this single image", 'regenerate-thumbnails' ) ) . '">' . __( 'Regenerate Thumbnails', 'regenerate-thumbnails' ) . '</a>';
		echo '</div>';
	}


	/**
	 * Adds a "Regenerate Thumbnails" button to the edit media modal view.
	 *
	 * Ideally it would be down with the actions but I'm not good enough at JavaScript
	 * in order to be able to do it, so instead I'm adding it to the bottom of the list
	 * of media fields. Pull requests to improve this are welcome!
	 *
	 * @param array  $form_fields An array of existing form fields.
	 * @param object $post        The current media item, as a post object.
	 *
	 * @return array The new array of form fields.
	 */
	public function add_button_to_edit_media_modal_fields_area( $form_fields, $post ) {
		$form_fields['regenerate_thumbnails'] = array(
			'label'         => '',
			'input'         => 'html',
			'html'          => '<a href="' . esc_url( $this->create_page_url( array( $post->ID ) ) ) . '" class="button-secondary button-large" title="' . esc_attr( __( "Regenerate the thumbnails for this single image", 'regenerate-thumbnails' ) ) . '">' . __( 'Regenerate Thumbnails', 'regenerate-thumbnails' ) . '</a>',
			'show_in_modal' => true,
			'show_in_edit'  => false,
		);

		return $form_fields;
	}


	// Add new items to the Bulk Actions using Javascript
	// A last minute change to the "bulk_actions-xxxxx" filter in 3.1 made it not possible to add items using that
	public function add_bulk_actions_via_javascript() {
		if ( ! current_user_can( $this->capability ) )
			return;
?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('select[name^="action"] option:last-child').before('<option value="bulk_regenerate_thumbnails"><?php echo esc_attr( _x( 'Regenerate Thumbnails', 'action of regenerate thumbnails', 'regenerate-thumbnails' ) ); ?></option>');
			});
		</script>
<?php
	}


	// Handles the bulk actions POST
	public function bulk_action_handler() {
		if (
				empty( $_REQUEST['action'] ) ||
				empty( $_REQUEST['action2'] ) ||
				( 'bulk_regenerate_thumbnails' != $_REQUEST['action'] && 'bulk_regenerate_thumbnails' != $_REQUEST['action2'] ) ||
				empty( $_REQUEST['media'] ) ||
				! is_array( $_REQUEST['media'] )
		) {
			return;
		}

		check_admin_referer( 'bulk-media' );

		$ids = implode( ',', array_map( 'intval', $_REQUEST['media'] ) );

		// Can't use wp_nonce_url() as it escapes HTML entities
		wp_redirect( add_query_arg( '_wpnonce', wp_create_nonce( 'regenerate-thumbnails' ), admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $ids ) ) );
		exit();
	}


	// The user interface plus thumbnail regenerator
	public function regenerate_interface() {
		global $wpdb;

		?>

<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap regenthumbs">
	<h2><?php _e('Regenerate Thumbnails', 'regenerate-thumbnails'); ?></h2>

<?php

		// If the button was clicked
		if ( ! empty( $_POST['regenerate-thumbnails'] ) || ! empty( $_REQUEST['ids'] ) ) {
			// Capability check
			if ( ! current_user_can( $this->capability ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			// Form nonce check
			check_admin_referer( 'regenerate-thumbnails' );

			// Create the list of image IDs
			if ( ! empty( $_REQUEST['ids'] ) ) {
				$images = array_map( 'intval', explode( ',', trim( $_REQUEST['ids'], ',' ) ) );
				$ids = implode( ',', $images );
			} else {
				// Directly querying the database is normally frowned upon, but all
				// of the API functions will return the full post objects which will
				// suck up lots of memory. This is best, just not as future proof.
				if ( ! $images = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC" ) ) {
					echo '	<p>' . sprintf( __( "Unable to find any images. Are you sure <a href='%s'>some exist</a>?", 'regenerate-thumbnails' ), admin_url( 'upload.php?post_mime_type=image' ) ) . "</p></div>";
					return;
				}

				// Generate the list of IDs
				$ids = array();
				foreach ( $images as $image )
					$ids[] = $image->ID;
				$ids = implode( ',', $ids );
			}

			echo '	<p>' . __( "Please be patient while the thumbnails are regenerated. This can take a while if your server is slow (inexpensive hosting) or if you have many images. Do not navigate away from this page until this script is done or the thumbnails will not be resized. You will be notified via this page when the regenerating is completed.", 'regenerate-thumbnails' ) . '</p>';

			$count = count( $images );

			$text_goback = ( ! empty( $_GET['goback'] ) ) ? sprintf( __( 'To go back to the previous page, <a href="%s">click here</a>.', 'regenerate-thumbnails' ), 'javascript:history.go(-1)' ) : '';
			$text_failures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were %3$s failure(s). To try regenerating the failed images again, <a href="%4$s">click here</a>. %5$s', 'regenerate-thumbnails' ), "' + rt_successes + '", "' + rt_totaltime + '", "' + rt_errors + '", esc_url( wp_nonce_url( admin_url( 'tools.php?page=regenerate-thumbnails&goback=1' ), 'regenerate-thumbnails' ) . '&ids=' ) . "' + rt_failedlist + '", $text_goback );
			$text_nofailures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were 0 failures. %3$s', 'regenerate-thumbnails' ), "' + rt_successes + '", "' + rt_totaltime + '", $text_goback );
?>


	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'regenerate-thumbnails' ) ?></em></p></noscript>

	<div id="regenthumbs-bar" style="position:relative;height:25px;">
		<div id="regenthumbs-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<p><input type="button" class="button hide-if-no-js" name="regenthumbs-stop" id="regenthumbs-stop" value="<?php _e( 'Abort Resizing Images', 'regenerate-thumbnails' ) ?>" /></p>

	<h3 class="title"><?php _e( 'Debugging Information', 'regenerate-thumbnails' ) ?></h3>

	<p>
		<?php printf( __( 'Total Images: %s', 'regenerate-thumbnails' ), $count ); ?><br />
		<?php printf( __( 'Images Resized: %s', 'regenerate-thumbnails' ), '<span id="regenthumbs-debug-successcount">0</span>' ); ?><br />
		<?php printf( __( 'Resize Failures: %s', 'regenerate-thumbnails' ), '<span id="regenthumbs-debug-failurecount">0</span>' ); ?>
	</p>

	<ol id="regenthumbs-debuglist">
		<li style="display:none"></li>
	</ol>

	<script type="text/javascript">
		jQuery(document).ready(function($){
			var i;
			var rt_images = [<?php echo $ids; ?>];
			var rt_total = rt_images.length;
			var rt_count = 1;
			var rt_percent = 0;
			var rt_successes = 0;
			var rt_errors = 0;
			var rt_failedlist = '';
			var rt_resulttext = '';
			var rt_timestart = new Date().getTime();
			var rt_timeend = 0;
			var rt_totaltime = 0;
			var rt_continue = true;

			// Create the progress bar
			$("#regenthumbs-bar").progressbar();
			$("#regenthumbs-bar-percent").html( "0%" );

			// Stop button
			$("#regenthumbs-stop").click(function() {
				rt_continue = false;
				$('#regenthumbs-stop').val("<?php echo $this->esc_quotes( __( 'Stopping...', 'regenerate-thumbnails' ) ); ?>");
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$("#regenthumbs-debuglist li").remove();

			// Called after each resize. Updates debug information and the progress bar.
			function RegenThumbsUpdateStatus( id, success, response ) {
				$("#regenthumbs-bar").progressbar( "value", ( rt_count / rt_total ) * 100 );
				$("#regenthumbs-bar-percent").html( Math.round( ( rt_count / rt_total ) * 1000 ) / 10 + "%" );
				rt_count = rt_count + 1;

				if ( success ) {
					rt_successes = rt_successes + 1;
					$("#regenthumbs-debug-successcount").html(rt_successes);
					$("#regenthumbs-debuglist").append("<li>" + response.success + "</li>");
				}
				else {
					rt_errors = rt_errors + 1;
					rt_failedlist = rt_failedlist + ',' + id;
					$("#regenthumbs-debug-failurecount").html(rt_errors);
					$("#regenthumbs-debuglist").append("<li>" + response.error + "</li>");
				}
			}

			// Called when all images have been processed. Shows the results and cleans up.
			function RegenThumbsFinishUp() {
				rt_timeend = new Date().getTime();
				rt_totaltime = Math.round( ( rt_timeend - rt_timestart ) / 1000 );

				$('#regenthumbs-stop').hide();

				if ( rt_errors > 0 ) {
					rt_resulttext = '<?php echo $text_failures; ?>';
				} else {
					rt_resulttext = '<?php echo $text_nofailures; ?>';
				}

				$("#message").html("<p><strong>" + rt_resulttext + "</strong></p>");
				$("#message").show();
			}

			// Regenerate a specified image via AJAX
			function RegenThumbs( id ) {
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: { action: "regeneratethumbnail", id: id },
					success: function( response ) {
						if ( response !== Object( response ) || ( typeof response.success === "undefined" && typeof response.error === "undefined" ) ) {
							response = new Object;
							response.success = false;
							response.error = "<?php printf( esc_js( __( 'The resize request was abnormally terminated (ID %s). This is likely due to the image exceeding available memory or some other type of fatal error.', 'regenerate-thumbnails' ) ), '" + id + "' ); ?>";
						}

						if ( response.success ) {
							RegenThumbsUpdateStatus( id, true, response );
						}
						else {
							RegenThumbsUpdateStatus( id, false, response );
						}

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					},
					error: function( response ) {
						RegenThumbsUpdateStatus( id, false, response );

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					}
				});
			}

			RegenThumbs( rt_images.shift() );
		});
	</script>
<?php
		}

		// No button click? Display the form.
		else {
?>
	<form method="post" action="">
<?php wp_nonce_field('regenerate-thumbnails') ?>

	<p><?php printf( __( "Use this tool to regenerate thumbnails for all images that you have uploaded to your blog. This is useful if you've changed any of the thumbnail dimensions on the <a href='%s'>media settings page</a>. Old thumbnails will be kept to avoid any broken images due to hard-coded URLs.", 'regenerate-thumbnails' ), admin_url( 'options-media.php' ) ); ?></p>

	<p><?php printf( __( "You can regenerate specific images (rather than all images) from the <a href='%s'>Media</a> page. Hover over an image's row and click the link to resize just that one image or use the checkboxes and the &quot;Bulk Actions&quot; dropdown to resize multiple images (WordPress 3.1+ only).", 'regenerate-thumbnails'), admin_url( 'upload.php' ) ); ?></p>

	<p><?php _e( "Thumbnail regeneration is not reversible, but you can just change your thumbnail dimensions back to the old values and click the button again if you don't like the results.", 'regenerate-thumbnails' ); ?></p>

	<p><?php _e( 'To begin, just press the button below.', 'regenerate-thumbnails'); ?></p>

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
	public function ajax_process_image() {
		@error_reporting( 0 ); // Don't break the JSON result

		header( 'Content-type: application/json' );

		$id = (int) $_REQUEST['id'];
		$image = get_post( $id );

		if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) )
			die( json_encode( array( 'error' => sprintf( __( 'Failed resize: %s is an invalid image ID.', 'regenerate-thumbnails' ), esc_html( $_REQUEST['id'] ) ) ) ) );

		if ( ! current_user_can( $this->capability ) )
			$this->die_json_error_msg( $image->ID, __( "Your user account doesn't have permission to resize images", 'regenerate-thumbnails' ) );

		$fullsizepath = get_attached_file( $image->ID );

		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) )
			$this->die_json_error_msg( $image->ID, sprintf( __( 'The originally uploaded image file cannot be found at %s', 'regenerate-thumbnails' ), '<code>' . esc_html( $fullsizepath ) . '</code>' ) );

		@set_time_limit( 900 ); // 5 minutes per image should be PLENTY

		$metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

		if ( is_wp_error( $metadata ) )
			$this->die_json_error_msg( $image->ID, $metadata->get_error_message() );
		if ( empty( $metadata ) )
			$this->die_json_error_msg( $image->ID, __( 'Unknown failure reason.', 'regenerate-thumbnails' ) );

		// If this fails, then it just means that nothing was changed (old value == new value)
		wp_update_attachment_metadata( $image->ID, $metadata );

		die( json_encode( array( 'success' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.', 'regenerate-thumbnails' ), esc_html( get_the_title( $image->ID ) ), $image->ID, timer_stop() ) ) ) );
	}


	// Helper to make a JSON error message
	public function die_json_error_msg( $id, $message ) {
		die( json_encode( array( 'error' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s', 'regenerate-thumbnails' ), esc_html( get_the_title( $id ) ), $id, $message ) ) ) );
	}


	// Helper function to escape quotes in strings for use in Javascript
	public function esc_quotes( $string ) {
		return str_replace( '"', '\"', $string );
	}
}

// Start up this plugin
add_action( 'init', 'RegenerateThumbnails' );
function RegenerateThumbnails() {
	global $RegenerateThumbnails;
	$RegenerateThumbnails = new RegenerateThumbnails();
}