<?php

class RegenerateThumbnails_REST_Controller extends WP_REST_Controller {
	public $namespace = 'regenerate-thumbnails/v1';
	public $rest_base = 'regenerate';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,//WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'regenerate_item' ),
				'permission_callback' => array( $this, 'regenerate_item_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Regenerate the thumbnails for a specific media item.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function regenerate_item( $request ) {
		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $request->get_param( 'id' ) );

		if ( is_wp_error( $regenerator ) ) {
			return $regenerator;
		}

		$result = $regenerator->regenerate();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response( $result );
	}

	/**
	 * Check if a given request has access to regenerate the thumbnails for a given item.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool
	 */
	public function regenerate_item_permissions_check( $request ) {
		return current_user_can( RegenerateThumbnails()->capability );
	}
}