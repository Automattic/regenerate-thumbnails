<?php

class RegenerateThumbnails_Regenerator {

	public $attachment;

	/**
	 * Generates an instance of this class after doing some setup.
	 *
	 * MIME type is purposefully not validated in order to be more future proof and
	 * to avoid duplicating a ton of logic that already exists in WordPress core.
	 *
	 * @param int $attachment_id Attachment Id to process.
	 *
	 * @return RegenerateThumbnails_Regenerator|WP_Error
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

	public function __construct( WP_Post $attachment ) {
		$this->attachment = $attachment;
	}

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