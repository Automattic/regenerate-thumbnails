<?php
/**
 * Class Regenerate_Thumbnails_Tests_REST_API
 *
 * @package    Regenerate_Thumbnails
 * @subpackage REST API
 */

require_once( dirname( __FILE__ ) . '/helper.php' );

/**
 * Tests for the REST API.
 * @group restapi
 */
class Regenerate_Thumbnails_Tests_REST_API extends WP_Test_REST_TestCase {
	public $attachment_id;

	public static $superadmin_id;
	public static $contributor_id;

	public static function wpSetUpBeforeClass( $factory ) {
		Regenerate_Thumbnails_Tests_Helper::delete_upload_dir_contents();

		self::$superadmin_id  = $factory->user->create( array(
			'role'       => 'administrator',
			'user_login' => 'superadmin',
		) );
		self::$contributor_id = $factory->user->create( array(
			'role' => 'contributor',
		) );

		if ( is_multisite() ) {
			update_site_option( 'site_admins', array( 'superadmin' ) );
		}
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$superadmin_id );
		self::delete_user( self::$contributor_id );
	}

	public function setUp() {
		parent::setUp();

		add_filter( 'rest_url', array( $this, 'filter_rest_url_for_leading_slash' ), 10, 2 );

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new Spy_REST_Server;
		do_action( 'rest_api_init' );

		$this->attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.jpg' );
	}

	public function tearDown() {
		remove_filter( 'rest_url', array( $this, 'test_rest_url_for_leading_slash' ), 10 );

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$wp_rest_server = null;

		wp_delete_attachment( $this->attachment_id, true );

		// Just to be sure
		Regenerate_Thumbnails_Tests_Helper::delete_upload_dir_contents();

		parent::tearDown();
	}

	public function filter_rest_url_for_leading_slash( $url, $path ) {
		if ( is_multisite() ) {
			return $url;
		}

		// Make sure path for rest_url has a leading slash for proper resolution.
		$this->assertTrue( 0 === strpos( $path, '/' ), 'REST API URL should have a leading slash.' );

		return $url;
	}

	private function assertResponseStatus( $status, $response ) {
		$this->assertEquals( $status, $response->get_status() );
	}

	public function test_regenerate_logged_out() {
		wp_set_current_user( 0 );

		$request  = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 403 );
	}

	public function test_regenerate_without_permission() {
		wp_set_current_user( self::$contributor_id );

		$request  = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 403 );
	}

	public function test_regenerator_with_permission() {
		wp_set_current_user( self::$superadmin_id );

		$request  = new WP_REST_Request( 'POST', '/regenerate-thumbnails/v1/regenerate/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertResponseStatus( 200, $response );
	}

	public function test_attachmentinfo_logged_out() {
		wp_set_current_user( 0 );

		$request  = new WP_REST_Request( 'GET', '/regenerate-thumbnails/v1/attachmentinfo/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 403 );
	}

	public function test_attachmentinfo_without_permission() {
		wp_set_current_user( self::$contributor_id );

		$request  = new WP_REST_Request( 'GET', '/regenerate-thumbnails/v1/attachmentinfo/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_forbidden', $response, 403 );
	}

	public function test_attachmentinfo_with_permission() {
		wp_set_current_user( self::$superadmin_id );

		$request  = new WP_REST_Request( 'GET', '/regenerate-thumbnails/v1/attachmentinfo/' . $this->attachment_id );
		$response = $this->server->dispatch( $request );

		$this->assertResponseStatus( 200, $response );
	}
}
