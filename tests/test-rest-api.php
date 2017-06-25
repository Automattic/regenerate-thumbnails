<?php
/**
 * Class Regenerate_Thumbnails_Tests_REST_API
 *
 * @package Regenerate_Thumbnails
 */

/**
 * Tests for the REST API.
 */
class Regenerate_Thumbnails_Tests_REST_API extends WP_UnitTestCase {

	public $attachment;

	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		$this->subscriber    = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->administrator = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$filename         = DIR_TESTDATA . '/images/33772.jpg';
		$upload           = wp_upload_bits( basename( $filename ), null, file_get_contents( $filename ) );
		$this->attachment = $this->_make_attachment( $upload );
	}

	public function tearDown() {
		parent::tearDown();

		global $wp_rest_server;
		$wp_rest_server = null;
	}

	public function test_auth_logged_out() {
		wp_set_current_user( 0 );
		$request  = new WP_REST_Request( 'GET', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 403, $response );
	}

	public function test_auth_subscriber() {
		wp_set_current_user( $this->subscriber );
		$request  = new WP_REST_Request( 'GET', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 403, $response );
	}

	public function test_auth_administrator() {
		wp_set_current_user( $this->administrator );
		$request  = new WP_REST_Request( 'GET', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
	}

	protected function assertResponseStatus( $status, $response ) {
		$this->assertEquals( $status, $response->get_status() );
	}

	protected function assertResponseData( $data, $response ) {
		$response_data = $response->get_data();
		$tested_data   = array();
		foreach ( $data as $key => $value ) {
			if ( isset( $response_data[ $key ] ) ) {
				$tested_data[ $key ] = $response_data[ $key ];
			} else {
				$tested_data[ $key ] = null;
			}
		}
		$this->assertEquals( $data, $tested_data );
	}
}