<?php
/**
 * Class Regenerate_Thumbnails_Tests_Regenerator
 *
 * @package    Regenerate_Thumbnails
 * @subpackage Regenerator
 */

require( dirname( __FILE__ ) . '/functions-return-ints.php' );
require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );

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
		self::_delete_upload_dir_contents();

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

	public function tearDown() {
		self::_delete_upload_dir_contents();

		parent::tearDown();
	}

	public static function _delete_upload_dir_contents() {
		$upload_dir = wp_get_upload_dir();
		$upload_dir = $upload_dir['path'];

		$filesystem = new WP_Filesystem_Direct( array() );
		$filesystem->rmdir( trailingslashit( $upload_dir ), true );
	}

	public function _create_attachment() {
		return self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/33772.jpg' );
	}

	public function _get_filemtimes( $upload_dir, $thumbnails ) {
		$filemtimes = array();

		foreach ( $thumbnails as $size => $filename ) {
			$file = $upload_dir . $filename;
			$this->assertFileExists( $file );
			$filemtimes[ $size ] = filemtime( $file );
		}

		return $filemtimes;
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

	public function test_regenerate_thumbnails_to_new_sizes() {
		$attachment_id = $this->_create_attachment();
		$old_metadata  = wp_get_attachment_metadata( $attachment_id );

		$upload_dir = wp_get_upload_dir();
		$upload_dir = trailingslashit( $upload_dir['path'] );

		$expected_default_thumbnail_sizes = array(
			'thumbnail'    => array( 150, 150 ),
			'medium'       => array( 300, 169 ),
			'medium_large' => array( 768, 432 ),
			'large'        => array( 1024, 576 ),
		);

		// Verify that the default thumbnails were made correctly during initial upload
		foreach ( $expected_default_thumbnail_sizes as $size => $dims ) {
			$this->assertFileExists( $upload_dir . "33772-{$dims[0]}x{$dims[1]}.jpg" );
			$this->assertEquals( $dims[0], $old_metadata['sizes'][ $size ]['width'] );
			$this->assertEquals( $dims[1], $old_metadata['sizes'][ $size ]['height'] );
		}

		$custom_thumbnail_size_callbacks = array(
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

		// Now change the thumbnail sizes to something other than the defaults
		foreach ( $custom_thumbnail_size_callbacks as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};

		// Regenerate the thumbnails!
		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $attachment_id );
		$regenerator->regenerate();

		$new_metadata = wp_get_attachment_metadata( $attachment_id );

		// Cleanup
		foreach ( $custom_thumbnail_size_callbacks as $filter => $function ) {
			remove_filter( 'pre_option_' . $filter, $function );
		}

		$expected_custom_thumbnail_sizes = array(
			'thumbnail'    => array( 100, 56 ),
			'medium'       => array( 350, 197 ),
			'medium_large' => array( 500, 281 ),
			'large'        => array( 1500, 844 ),
		);

		// Verify that the new custom thumbnails were made correctly by this plugin
		foreach ( $expected_custom_thumbnail_sizes as $size => $dims ) {
			$this->assertFileExists( $upload_dir . "33772-{$dims[0]}x{$dims[1]}.jpg" );
			$this->assertEquals( $dims[0], $new_metadata['sizes'][ $size ]['width'] );
			$this->assertEquals( $dims[1], $new_metadata['sizes'][ $size ]['height'] );
		}
	}

	public function test_regenerate_thumbnails_skipping_existing_thumbnails() {
		$this->_regenerate_thumbnails_skipping_existing_thumbnails_helper( true );
	}

	public function test_regenerate_thumbnails_without_skipping_existing_thumbnails() {
		$this->_regenerate_thumbnails_skipping_existing_thumbnails_helper( false );
	}

	public function _regenerate_thumbnails_skipping_existing_thumbnails_helper( $only_regenerate_missing_thumbnails ) {
		$attachment_id = $this->_create_attachment();

		// These are the expected thumbnail filenames
		$thumbnails = array(
			'thumbnail'    => '33772-150x150.jpg',
			'medium'       => '33772-300x169.jpg',
			'medium_large' => '33772-768x432.jpg',
			'large'        => '33772-1024x576.jpg',
		);

		$upload_dir = wp_get_upload_dir();
		$upload_dir = trailingslashit( $upload_dir['path'] );

		$filemtimes = $this->_get_filemtimes( $upload_dir, $thumbnails );

		// Delete some of the thumbnail files
		$missing_thumbnails = array( 'medium', 'large' );
		foreach ( $missing_thumbnails as $size ) {
			unlink( $upload_dir . $thumbnails[ $size ] );
			$this->assertFileNotExists( $upload_dir . $thumbnails[ $size ] );
		}

		// Sleep to make sure that filemtime() changes
		sleep( 1 );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $attachment_id );
		$regenerator->regenerate( array(
			'only_regenerate_missing_thumbnails' => $only_regenerate_missing_thumbnails,
		) );

		// Clear the file stat cache to make sure that filemtime() works correctly
		clearstatcache();

		// Verify the modified times of files
		// When skipping existing thumbnails, the thumbnail files we didn't delete shouldn't change
		foreach ( $thumbnails as $size => $filename ) {
			$file = $upload_dir . $filename;
			if ( ! $only_regenerate_missing_thumbnails || in_array( $size, $missing_thumbnails ) ) {
				$this->assertFileExists( $file );
				$this->assertNotEquals( $filemtimes[ $size ], filemtime( $file ) );
			} else {
				$this->assertEquals( $filemtimes[ $size ], filemtime( $file ) );
			}
		}
	}

	public function test_dont_delete_unregistered_thumbnail_files() {
		$this->_delete_unregistered_thumbnail_files_helper( false );
	}

	public function test_delete_unregistered_thumbnail_files() {
		$this->_delete_unregistered_thumbnail_files_helper( true );
	}

	public function _delete_unregistered_thumbnail_files_helper( $delete_unregistered_thumbnail_files ) {
		add_image_size( 'regenerate-thumbnails-test', 500, 500 );

		$attachment_id = $this->_create_attachment();
		$old_metadata  = wp_get_attachment_metadata( $attachment_id );

		$upload_dir     = wp_get_upload_dir();
		$thumbnail_file = trailingslashit( $upload_dir['path'] ) . $old_metadata['sizes']['regenerate-thumbnails-test']['file'];

		$this->assertFileExists( $thumbnail_file );

		remove_image_size( 'regenerate-thumbnails-test' );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $attachment_id );
		$regenerator->regenerate( array(
			'delete_unregistered_thumbnail_files' => $delete_unregistered_thumbnail_files,
		) );

		$new_metadata = wp_get_attachment_metadata( $attachment_id );

		if ( $delete_unregistered_thumbnail_files ) {
			$this->assertFileNotExists( $thumbnail_file );
			$this->assertArrayNotHasKey( 'regenerate-thumbnails-test', $new_metadata['sizes'] );
		} else {
			$this->assertFileExists( $thumbnail_file );
			$this->assertArrayHasKey( 'regenerate-thumbnails-test', $new_metadata['sizes'] );
		}
	}

	public function test_verify_that_site_icons_are_not_regenerated() {
		$attachment_id = $this->_create_attachment();

		// See wp_ajax_crop_image()

		require_once( ABSPATH . '/wp-admin/includes/class-wp-site-icon.php' );
		$wp_site_icon = new WP_Site_Icon();

		$cropped = wp_crop_image( $attachment_id, 1300, 300, 512, 512, 512, 512 );
		$this->assertNotFalse( $cropped );
		$this->assertNotInstanceOf( 'WP_Error', $cropped );

		$object = $wp_site_icon->create_attachment_object( $cropped, $attachment_id );
		unset( $object['ID'] );

		// Update the attachment.
		add_filter( 'intermediate_image_sizes_advanced', array( $wp_site_icon, 'additional_sizes' ) );
		$attachment_id = $wp_site_icon->insert_attachment( $object, $cropped );
		remove_filter( 'intermediate_image_sizes_advanced', array( $wp_site_icon, 'additional_sizes' ) );

		$attachment_metadata = wp_get_attachment_metadata( $attachment_id );

		$thumbnails = array();
		foreach ( $attachment_metadata['sizes'] as $size => $size_data ) {
			$thumbnails[ $size ] = $size_data['file'];
		}

		$upload_dir = wp_get_upload_dir();
		$upload_dir = trailingslashit( $upload_dir['path'] );

		$filemtimes = $this->_get_filemtimes( $upload_dir, $thumbnails );

		// Sleep to make sure that filemtime() will change if the thumbnail files do
		sleep( 1 );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $attachment_id );

		$this->assertInstanceOf( 'WP_Error', $regenerator );
		$this->assertEquals( 'regenerate_thumbnails_regenerator_is_site_icon', $regenerator->get_error_code() );

		// Clear the file stat cache to make sure that filemtime() works correctly
		clearstatcache();

		// Verify that none of the thumbnail files have changed
		foreach ( $attachment_metadata['sizes'] as $size => $size_data ) {
			$file = $upload_dir . $size_data['file'];
			$this->assertFileExists( $file );
			$this->assertEquals( $filemtimes[ $size ], filemtime( $file ) );
		}
	}
}