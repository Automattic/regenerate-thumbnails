<?php
/**
 * Regenerate Thumbnails: Post updater class.
 *
 * @package RegenerateThumbnails
 * @since   3.1.0
 */

/**
 * Updates the usages of an attachment in posts.
 *
 * @since 3.1.0
 */
class RegenerateThumbnails_Post_Updater {

	public $attachment;

	public static function get_instance( $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! $attachment ) {
			return new WP_Error(
				'regenerate_thumbnails_post_updater_attachment_doesnt_exist',
				__( 'No attachment exists with that ID.', 'regenerate-thumbnails' ),
				array(
					'status' => 404,
				)
			);
		}

		return new RegenerateThumbnails_Post_Updater( $attachment );
	}

	private function __construct( WP_Post $attachment ) {
		$this->attachment = $attachment;
	}

	public function get_posts( $page, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'post_type'   => array(),
			'post_ids'    => array(),
			'numberposts' => 10,
		) );

		if ( empty( $args['post_type'] ) ) {
			$args['post_type'] = array_values( get_post_types( array( 'public' => true ) ) );
			unset( $args['post_type']['attachment'] );
		}

		return new WP_Query( array(
			's'                      => 'wp-image-' . $this->attachment->ID,
			'post_type'              => $args['post_type'],
			'post__in'               => $args['post_ids'],
			'posts_per_page'         => $args['numberposts'],
			'paged'                  => $page,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );
	}

	public function update_post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error(
				'regenerate_thumbnails_post_updater_post_doesnt_exist',
				__( 'No post exists with that ID.', 'regenerate-thumbnails' ),
				array(
					'post_id' => $post_id,
					'status'  => 404,
				)
			);
		}

		$content = $this->update_post_content( $post->post_content );

		if ( $content === $post->post_content ) {
			return false;
		}

		//return wp_update_post();
	}

	public function update_post_content( $content ) {


		return $content;
	}
}