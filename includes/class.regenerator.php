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
	 * Stores the full path to the original image so that it can be passed between methods.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public $fullsizepath;

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
				__( 'No attachment exists with that ID.', 'regenerate-thumbnails' ),
				array(
					'status' => 404,
				)
			);
		}

		// We can only regenerate thumbnails for attachments
		if ( 'attachment' != get_post_type( $attachment ) ) {
			return new WP_Error(
				'regenerate_thumbnails_regenerator_not_attachment',
				__( 'This item is not an attachment.', 'regenerate-thumbnails' ),
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
	 * @todo  Additional parameters such as deleting old thumbnails or only regenerating certain sizes.
	 *
	 * @since 3.0.0
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments thumbnail regeneration.
	 *
	 *     @type bool $only_regenerate_missing_thumbnails Skip regenerating existing thumbnail files. Default true.
	 * }
	 *
	 * @return mixed|WP_Error Metadata for attachment (see wp_generate_attachment_metadata()), or WP_Error on error.
	 */
	public function regenerate( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'only_regenerate_missing_thumbnails'  => true,
			'delete_unregistered_thumbnail_files' => false,
		) );

		$this->fullsizepath = get_attached_file( $this->attachment->ID );

		if ( false === $this->fullsizepath || ! file_exists( $this->fullsizepath ) ) {
			return new WP_Error(
				'regenerate_thumbnails_regenerator_file_not_found',
				__( "Unable to locate the original file for this attachment.", 'regenerate-thumbnails' ),
				array(
					'status'       => 404,
					'fullsizepath' => _wp_relative_upload_path( $this->fullsizepath ),
					'attachment'   => $this->attachment,
				)
			);
		}

		$old_metadata = wp_get_attachment_metadata( $this->attachment->ID );

		if ( $args['only_regenerate_missing_thumbnails'] ) {
			add_filter( 'intermediate_image_sizes_advanced', array( $this, 'filter_image_sizes_to_only_missing_thumbnails' ), 10, 2 );
		}

		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		$new_metadata = wp_generate_attachment_metadata( $this->attachment->ID, $this->fullsizepath );

		if ( $args['only_regenerate_missing_thumbnails'] ) {
			$new_metadata['sizes'] = array_merge( $old_metadata['sizes'], $new_metadata['sizes'] );
			remove_filter( 'intermediate_image_sizes_advanced', array( $this, 'filter_image_sizes_to_only_missing_thumbnails' ), 10 );
		}

		/**
		 * If the user wants to keep their storage usage down, we can iterate over the list of
		 * thumbnails in the old metadata and delete any that are no longer registered as valid sizes.
		 */
		if ( $args['delete_unregistered_thumbnail_files'] ) {
			$upload_dir = wp_get_upload_dir();
			$upload_dir = trailingslashit( $upload_dir['path'] );

			$intermediate_image_sizes = get_intermediate_image_sizes();
			foreach ( $old_metadata['sizes'] as $size => $thumbnail ) {
				if ( in_array( $size, $intermediate_image_sizes ) ) {
					continue;
				}

				// @todo: Prefix with @ for release. Could wrap in file_exists() but that just adds overhead.
				unlink( $upload_dir . $thumbnail['file'] );

				unset( $new_metadata['sizes'][ $size ] );
			}
		}

		wp_update_attachment_metadata( $this->attachment->ID, $new_metadata );

		return $new_metadata;
	}

	/**
	 * Filters the list of thumbnail sizes to only include those which have missing files.
	 *
	 * @param array $sizes    An associative array of image sizes.
	 * @param array $metadata An associative array of image metadata: width, height, file.
	 *
	 * @return array An associative array of image sizes.
	 */
	public function filter_image_sizes_to_only_missing_thumbnails( $sizes, $metadata ) {
		if ( ! $sizes ) {
			return $sizes;
		}

		$editor = wp_get_image_editor( $this->fullsizepath );

		if ( is_wp_error( $editor ) ) {
			return $sizes;
		}

		// This is based on WP_Image_Editor_GD::multi_resize() and others
		foreach ( $sizes as $size => $size_data ) {
			if ( ! isset( $size_data['width'] ) && ! isset( $size_data['height'] ) ) {
				continue;
			}

			if ( ! isset( $size_data['width'] ) ) {
				$size_data['width'] = null;
			}
			if ( ! isset( $size_data['height'] ) ) {
				$size_data['height'] = null;
			}

			if ( ! isset( $size_data['crop'] ) ) {
				$size_data['crop'] = false;
			}

			$dims = image_resize_dimensions( $metadata['width'], $metadata['height'], $size_data['width'], $size_data['height'], $size_data['crop'] );

			if ( ! $dims ) {
				unset( $sizes[ $size ] );
				continue;
			}

			list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;

			$suffix   = "{$dst_w}x{$dst_h}";
			$file_ext = strtolower( pathinfo( $this->fullsizepath, PATHINFO_EXTENSION ) );

			$filename = $editor->generate_filename( $suffix, null, $file_ext );

			if ( file_exists( $filename ) ) {
				unset( $sizes[ $size ] );
			}
		}

		return $sizes;
	}
}