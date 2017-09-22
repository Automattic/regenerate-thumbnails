<?php
/**
 * Class Regenerate_Thumbnails_Tests_Regenerator
 *
 * @package    Regenerate_Thumbnails
 * @subpackage Regenerator
 */

require( dirname( __FILE__ ) . '/functions-return-ints.php' );

/**
 * Tests for the RegenerateThumbnails_Regenerator class.
 * @group regenerator
 */
class Regenerate_Thumbnails_Tests_Regenerator extends WP_UnitTestCase {

	public static $default_size_functions;

	/**
	 * Make sure a bunch of thumbnail options are what we expect them to be.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$default_size_functions = array(
			'thumbnail_size_w'    => '__return_int_150',
			'thumbnail_size_h'    => '__return_int_150',
			'thumbnail_crop'      => '__return_int_1',
			'medium_size_w'       => '__return_int_300',
			'medium_size_h'       => '__return_int_300',
			'medium_large_size_w' => '__return_int_768',
			'medium_large_size_h' => '__return_zero',
			'large_size_w'        => '__return_int_1024',
			'large_size_h'        => '__return_int_1024',
		);

		foreach ( self::$default_size_functions as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};
	}

	public static function wpTearDownAfterClass() {
		foreach ( self::$default_size_functions as $filter => $function ) {
			remove_filter( 'pre_option_' . $filter, $function );
		}
	}

	public function setUp() {
		parent::setUp();

		if ( ! wp_image_editor_supports( array( 'methods' => array( 'resize' ) ) ) ) {
			$this->markTestSkipped( "This system doesn't have an image editor engine capable of resizing images. Try installing Imagick or GD." );
		}
	}

	public function test_attachment_doesnt_exist() {
		$regenerator = RegenerateThumbnails_Regenerator::get_instance( 0 );

		$this->assertInstanceOf( 'WP_Error', $regenerator );
		$this->assertEquals( 'regenerate_thumbnails_regenerator_attachment_doesnt_exist', $regenerator->get_error_code() );
	}

	public function test_not_attachment() {
		$post_id = self::factory()->post->create( array() );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $post_id );

		$this->assertInstanceOf( 'WP_Error', $regenerator );
		$this->assertEquals( 'regenerate_thumbnails_regenerator_not_attachment', $regenerator->get_error_code() );
	}

	public function test_missing_original_file() {
		$attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.jpg' );

		unlink( get_attached_file( $attachment_id ) );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $attachment_id );
		$result      = $regenerator->regenerate();

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'regenerate_thumbnails_regenerator_file_not_found', $result->get_error_code() );
	}

	public function test_regeneration() {
		$attachment_id       = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/33772.jpg' );
		$attachment_metadata = wp_get_attachment_metadata( $attachment_id );

		// Verify thumbnail sizes are the expected defaults after initial upload
		$this->assertEquals( 150, $attachment_metadata['sizes']['thumbnail']['width'] );
		$this->assertEquals( 150, $attachment_metadata['sizes']['thumbnail']['height'] );
		$this->assertEquals( 300, $attachment_metadata['sizes']['medium']['width'] );
		$this->assertEquals( 169, $attachment_metadata['sizes']['medium']['height'] );
		$this->assertEquals( 768, $attachment_metadata['sizes']['medium_large']['width'] );
		$this->assertEquals( 432, $attachment_metadata['sizes']['medium_large']['height'] );
		$this->assertEquals( 1024, $attachment_metadata['sizes']['large']['width'] );
		$this->assertEquals( 576, $attachment_metadata['sizes']['large']['height'] );

		// And that all of the thumbnail sizes have been made
		foreach ( get_intermediate_image_sizes() as $size ) {
			$this->assertArrayHasKey( $size, $attachment_metadata['sizes'] );
		}

		// Now change the thumbnail sizes to something other than the defaults
		foreach ( $this->_get_custom_thumbnail_size_filter_functions() as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};

		// Regenerate the thumbnails!
		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $attachment_id );
		$result      = $regenerator->regenerate();

		// Verify thumbnail sizes are the new non-default sizes
		$this->assertEquals( 100, $result['sizes']['thumbnail']['width'] );
		$this->assertEquals( 56, $result['sizes']['thumbnail']['height'] );
		$this->assertEquals( 350, $result['sizes']['medium']['width'] );
		$this->assertEquals( 197, $result['sizes']['medium']['height'] );
		$this->assertEquals( 500, $result['sizes']['medium_large']['width'] );
		$this->assertEquals( 281, $result['sizes']['medium_large']['height'] );
		$this->assertEquals( 1500, $result['sizes']['large']['width'] );
		$this->assertEquals( 844, $result['sizes']['large']['height'] );

		// And that all of the thumbnail sizes have been made
		foreach ( get_intermediate_image_sizes() as $size ) {
			$this->assertArrayHasKey( $size, $result['sizes'] );
		}

		// Cleanup
		foreach ( $this->_get_custom_thumbnail_size_filter_functions() as $filter => $function ) {
			remove_filter( 'pre_option_' . $filter, $function );
		};
		$this->remove_added_uploads();
	}

	public function _get_custom_thumbnail_size_filter_functions() {
		return array(
			'thumbnail_size_w'    => '__return_int_100',
			'thumbnail_size_h'    => '__return_int_100',
			'thumbnail_crop'      => '__return_zero',
			'medium_size_w'       => '__return_int_350',
			'medium_size_h'       => '__return_int_350',
			'medium_large_size_w' => '__return_int_500',
			'medium_large_size_h' => '__return_int_500',
			'large_size_w'        => '__return_int_1500',
			'large_size_h'        => '__return_int_1500',
		);
	}
}