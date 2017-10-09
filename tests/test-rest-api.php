<?php
/**
 * Class Regenerate_Thumbnails_Tests_REST_API
 *
 * @package    Regenerate_Thumbnails
 * @subpackage REST API
 */

/**
 * Tests for the REST API.
 * @group restapi
 */
class Regenerate_Thumbnails_Tests_REST_API extends WP_UnitTestCase {

	public $subscriber;
	public $administrator;
	public $attachment_id;

	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		$this->subscriber    = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->administrator = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$this->attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.jpg' );
	}

	public function tearDown() {
		global $wp_rest_server;
		$wp_rest_server = null;

		$this->remove_added_uploads();

		parent::tearDown();
	}

	private function assertResponseStatus( $status, $response ) {
		$this->assertEquals( $status, $response->get_status() );
	}

	private function assertErrorResponse( $code, $response, $status = null ) {
		if ( is_a( $response, 'WP_REST_Response' ) ) {
			$response = $response->as_error();
		}

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( $code, $response->get_error_code() );

		if ( null !== $status ) {
			$data = $response->get_error_data();
			$this->assertArrayHasKey( 'status', $data );
			$this->assertEquals( $status, $data['status'] );
		}
	}

	public function test_auth_logged_out() {
		wp_set_current_user( 0 );

		$request  = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 403 );
	}

	public function test_auth_subscriber() {
		wp_set_current_user( $this->subscriber );

		$request  = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 403 );
	}

	public function test_auth_administrator() {
		wp_set_current_user( $this->administrator );

		$request  = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertResponseStatus( 200, $response );
	}

	public function test_arg_regeneration_args_not_array() {
		wp_set_current_user( $this->administrator );

		$request = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$request->set_param( 'regeneration_args', 'string' );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_invalid_param', $response, 400 );
	}

	public function test_arg_update_usages_in_posts_args_not_array() {
		wp_set_current_user( $this->administrator );

		$request = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$request->set_param( 'update_usages_in_posts_args', 'string' );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_invalid_param', $response, 400 );
	}
}