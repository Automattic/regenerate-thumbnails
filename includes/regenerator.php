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
				$attachment_id );
		}

		// We can only regenerate thumbnails for attachments
		if ( 'attachment' != get_post_type( $attachment ) ) {
			return new WP_Error(
				'regenerate_thumbnails_regenerator_not_attachment',
				esc_html__( 'This item is not an attachment.', 'regenerate-thumbnails' ),
				$attachment
			);
		}

		return new RegenerateThumbnails_Regenerator( $attachment );
	}

	public function __construct( WP_Post $attachment ) {
		$this->attachment = $attachment;
	}

	public function regenerate() {
		return array(
			'success' => true,
		);
	}
}