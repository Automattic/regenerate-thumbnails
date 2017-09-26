<?php

/**
 * Regenerates the thumbnails for a given attachment.
 *
 * @since 3.0.0
 */
class RegenerateThumbnails_Regenerator {

	/**
	 * The WP_Post object for the attachment that is being operated on.
	 *
	 * @since 3.0.0
	 *
	 * @var WP_Post
	 */
	public $attachment;

	/**
	 * Generates an instance of this class after doing some setup.
	 *
	 * MIME type is purposefully not validated in order to be more future proof and
	 * to avoid duplicating a ton of logic that already exists in WordPress core.
	 *
	 * @since 3.0.0
	 *
	 * @param int $attachment_id Attachment ID to process.
	 *
	 * @return RegenerateThumbnails_Regenerator|WP_Error A new instance of RegenerateThumbnails_Regenerator on success, or WP_Error on error.
	 */
	public static function get_instance( $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! $attachment ) {
			return new WP_Error(
				'regenerate_thumbnails_regenerator_attachment_doesnt_exist',
				esc_html__( 'No attachment exists with that ID.', 'regenerate-thumbnails' ),
				array(
					'status' => 404,
				)
			);
		}

		// We can only regenerate thumbnails for attachments
		if ( 'attachment' != get_post_type( $attachment ) ) {
			return new WP_Error(
				'regenerate_thumbnails_regenerator_not_attachment',
				esc_html__( 'This item is not an attachment.', 'regenerate-thumbnails' ),
				array(
					'status' => 400,
				)
			);
		}

		return new RegenerateThumbnails_Regenerator( $attachment );
	}

	/**
	 * The constructor for this class. Don't call this directly, see get_instance() instead.
	 * This is done so that WP_Error objects can be returned during class initiation.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_Post $attachment The WP_Post object for the attachment that is being operated on.
	 */
	private function __construct( WP_Post $attachment ) {
		$this->attachment = $attachment;
	}

	/**
	 * Regenerate the thumbnails for this instance's attachment.
	 *
	 * @todo Additional parameters such as deleting old thumbnails or only regenerating certain sizes.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed|WP_Error Metadata for attachment (see wp_generate_attachment_metadata()), or WP_Error on error.
	 */
	public function regenerate() {
		$fullsizepath = get_attached_file( $this->attachment->ID );

		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) ) {
			return new WP_Error(
				'regenerate_thumbnails_regenerator_file_not_found',
				esc_html__( "Unable to locate the original file for this attachment.", 'regenerate-thumbnails' ),
				array(
					'status'       => 404,
					'fullsizepath' => _wp_relative_upload_path( $fullsizepath ),
					'attachment'   => $this->attachment,
				)
			);
		}

		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		$metadata = wp_generate_attachment_metadata( $this->attachment->ID, $fullsizepath );

		wp_update_attachment_metadata( $this->attachment->ID, $metadata );

		return $metadata;
	}
}