<?php
/**
 * Class Regenerate_Thumbnails_Tests_Regenerator
 *
 * @package    Regenerate_Thumbnails
 * @subpackage Regenerator
 */

require_once( dirname( __FILE__ ) . '/helper.php' );
require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );

/**
 * Tests for the RegenerateThumbnails_Regenerator class.
 * @group regenerator
 */
class Regenerate_Thumbnails_Tests_Regenerator extends WP_UnitTestCase {
	public $attachment_id;

	public static $default_size_functions;

	/**
	 * Make sure a bunch of thumbnail options are what we expect them to be.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$default_size_functions = array(
			'thumbnail_size_w'    => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_150' ),
			'thumbnail_size_h'    => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_150' ),
			'thumbnail_crop'      => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_1' ),
			'medium_size_w'       => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_300' ),
			'medium_size_h'       => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_300' ),
			'medium_large_size_w' => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_768' ),
			'medium_large_size_h' => '__return_zero',
			'large_size_w'        => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_1024' ),
			'large_size_h'        => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_1024' ),
		);

		foreach ( self::$default_size_functions as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};

		Regenerate_Thumbnails_Tests_Helper::delete_upload_dir_contents();
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
		wp_delete_attachment( $this->attachment_id, true );

		Regenerate_Thumbnails_Tests_Helper::delete_upload_dir_contents();

		parent::tearDown();
	}

	public function helper_create_attachment() {
		return self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/33772.jpg' );
	}

	public function helper_get_custom_thumbnail_size_callbacks() {
		return array(
			'thumbnail_size_w'    => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_100' ),
			'thumbnail_size_h'    => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_100' ),
			'thumbnail_crop'      => '__return_zero',
			'medium_size_w'       => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_350' ),
			'medium_size_h'       => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_350' ),
			'medium_large_size_w' => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_500' ),
			'medium_large_size_h' => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_500' ),
			'large_size_w'        => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_1500' ),
			'large_size_h'        => array( 'Regenerate_Thumbnails_Tests_Helper', 'return_int_1500' ),
		);
	}

	public function helper_get_filemtimes( $upload_dir, $thumbnails ) {
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
		$this->attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/test-image.jpg' );

		unlink( get_attached_file( $this->attachment_id ) );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$result      = $regenerator->regenerate();

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'regenerate_thumbnails_regenerator_file_not_found', $result->get_error_code() );
	}

	public function test_regenerate_thumbnails_to_new_sizes() {
		$this->attachment_id = $this->helper_create_attachment();
		$old_metadata        = wp_get_attachment_metadata( $this->attachment_id );

		$upload_dir = dirname( get_attached_file( $this->attachment_id ) ) . DIRECTORY_SEPARATOR;

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

		// Now change the thumbnail sizes to something other than the defaults
		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};

		// Regenerate the thumbnails!
		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$regenerator->regenerate();

		$new_metadata = wp_get_attachment_metadata( $this->attachment_id );

		// Cleanup
		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
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

	public function test_imagemagick_installed() {
		$this->assertTrue( class_exists( 'Imagick' ) );
	}

	public function test_regenerate_thumbnails_for_pdf() {
		$test_pdf = DIR_TESTDATA . '/images/wordpress-gsoc-flyer.pdf';

		$editor = wp_get_image_editor( $test_pdf );
		if ( is_wp_error( $editor ) ) {
			$this->markTestSkipped( "The current image editor doesn't support making thumbnails for PDFs. Please install ImageMagick." );
		}

		$this->attachment_id = self::factory()->attachment->create_upload_object( $test_pdf );
		$old_metadata        = wp_get_attachment_metadata( $this->attachment_id );

		$upload_dir = dirname( get_attached_file( $this->attachment_id ) ) . DIRECTORY_SEPARATOR;

		$expected_default_thumbnail_sizes = array(
			'thumbnail' => array( 116, 150 ),
			'medium'    => array( 232, 300 ),
			'large'     => array( 791, 1024 ),
		);

		// Verify that the default thumbnails were made correctly during initial upload
		foreach ( $expected_default_thumbnail_sizes as $size => $dims ) {
			$this->assertFileExists( $upload_dir . "wordpress-gsoc-flyer-pdf-{$dims[0]}x{$dims[1]}.jpg" );
			$this->assertEquals( $dims[0], $old_metadata['sizes'][ $size ]['width'] );
			$this->assertEquals( $dims[1], $old_metadata['sizes'][ $size ]['height'] );
		}
		$this->assertFileExists( $upload_dir . 'wordpress-gsoc-flyer-pdf.jpg' );
		$this->assertEquals( 1088, $old_metadata['sizes']['full']['width'] );
		$this->assertEquals( 1408, $old_metadata['sizes']['full']['height'] );

		// Remove the fullsize thumbnail. It causes "-1" to be added after "pdf" in the filenames.
		unlink( $upload_dir . 'wordpress-gsoc-flyer-pdf.jpg' );

		// Now change the thumbnail sizes to something other than the defaults
		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};

		// Regenerate the thumbnails!
		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$regenerator->regenerate();

		$new_metadata = wp_get_attachment_metadata( $this->attachment_id );

		// Cleanup
		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
			remove_filter( 'pre_option_' . $filter, $function );
		}

		$expected_custom_thumbnail_sizes = array(
			'thumbnail' => array( 77, 100 ),
			'medium'    => array( 270, 350 ),
			'large'     => array( 791, 1024 ),
		);

		// Verify that the new custom thumbnails were made correctly by this plugin
		foreach ( $expected_custom_thumbnail_sizes as $size => $dims ) {
			$this->assertFileExists( $upload_dir . "wordpress-gsoc-flyer-pdf-{$dims[0]}x{$dims[1]}.jpg" );
			$this->assertEquals( $dims[0], $new_metadata['sizes'][ $size ]['width'] );
			$this->assertEquals( $dims[1], $new_metadata['sizes'][ $size ]['height'] );
		}
	}

	public function test_regenerate_thumbnails_skipping_existing_thumbnails() {
		$this->helper_regenerate_thumbnails_skipping_existing_thumbnails( true );
	}

	public function test_regenerate_thumbnails_without_skipping_existing_thumbnails() {
		$this->helper_regenerate_thumbnails_skipping_existing_thumbnails( false );
	}

	public function helper_regenerate_thumbnails_skipping_existing_thumbnails( $only_regenerate_missing_thumbnails ) {
		$this->attachment_id = $this->helper_create_attachment();

		// These are the expected thumbnail filenames
		$thumbnails = array(
			'thumbnail'    => '33772-150x150.jpg',
			'medium'       => '33772-300x169.jpg',
			'medium_large' => '33772-768x432.jpg',
			'large'        => '33772-1024x576.jpg',
		);

		$upload_dir = dirname( get_attached_file( $this->attachment_id ) ) . DIRECTORY_SEPARATOR;
		$filemtimes = $this->helper_get_filemtimes( $upload_dir, $thumbnails );

		// Delete some of the thumbnail files
		$missing_thumbnails = array( 'medium', 'large' );
		foreach ( $missing_thumbnails as $size ) {
			unlink( $upload_dir . $thumbnails[ $size ] );
			$this->assertFileNotExists( $upload_dir . $thumbnails[ $size ] );
		}

		// Sleep to make sure that filemtime() changes
		sleep( 1 );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
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

	public function test_delete_unregistered_thumbnail_files_no() {
		$this->helper_delete_unregistered_thumbnail_files( false );
	}

	public function test_delete_unregistered_thumbnail_files_yes() {
		$this->helper_delete_unregistered_thumbnail_files( true );
	}

	public function helper_delete_unregistered_thumbnail_files( $delete_unregistered_thumbnail_files ) {
		add_image_size( 'regenerate-thumbnails-test-inmeta', 521, 567 );
		add_image_size( 'regenerate-thumbnails-test-notinmeta', 621, 667 );

		// Both test thumbnails will be created on upload
		$this->attachment_id = $this->helper_create_attachment();
		$old_metadata        = wp_get_attachment_metadata( $this->attachment_id );

		copy( DIR_TESTDATA . '/images/33772.jpg', DIR_TESTDATA . '/images/33772-123x456.jpg' );
		$attachment_to_keep_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/33772-123x456.jpg' );

		$this->assertArrayHasKey( 'regenerate-thumbnails-test-inmeta', $old_metadata['sizes'] );
		$this->assertArrayHasKey( 'regenerate-thumbnails-test-notinmeta', $old_metadata['sizes'] );

		$thumbnail_file_inmeta    = dirname( get_attached_file( $this->attachment_id ) ) . DIRECTORY_SEPARATOR . $old_metadata['sizes']['regenerate-thumbnails-test-inmeta']['file'];
		$thumbnail_file_notinmeta = dirname( get_attached_file( $this->attachment_id ) ) . DIRECTORY_SEPARATOR . $old_metadata['sizes']['regenerate-thumbnails-test-notinmeta']['file'];
		$thumbnail_file_to_keep   = get_attached_file( $attachment_to_keep_id );

		$this->assertFileExists( $thumbnail_file_inmeta );
		$this->assertFileExists( $thumbnail_file_notinmeta );
		$this->assertFileExists( $thumbnail_file_to_keep );

		remove_image_size( 'regenerate-thumbnails-test-notinmeta' );

		// After this, "inmeta" will be in the meta and "notinmeta" will exist but not be in the meta
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );
		$step2_metadata = wp_generate_attachment_metadata( $this->attachment_id, get_attached_file( $this->attachment_id ) );
		wp_update_attachment_metadata( $this->attachment_id, $step2_metadata );

		$step2_metadata = wp_get_attachment_metadata( $this->attachment_id );

		$this->assertArrayHasKey( 'regenerate-thumbnails-test-inmeta', $step2_metadata['sizes'] );
		$this->assertArrayNotHasKey( 'regenerate-thumbnails-test-notinmeta', $step2_metadata['sizes'] );

		$this->assertFileExists( $thumbnail_file_inmeta );
		$this->assertFileExists( $thumbnail_file_notinmeta );
		$this->assertFileExists( $thumbnail_file_to_keep );

		remove_image_size( 'regenerate-thumbnails-test-inmeta' );

		// Now let's verify that the regenerate() method works as expected
		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$regenerator->regenerate( array(
			'delete_unregistered_thumbnail_files' => $delete_unregistered_thumbnail_files,
		) );

		$new_metadata = wp_get_attachment_metadata( $this->attachment_id );

		if ( $delete_unregistered_thumbnail_files ) {
			$this->assertFileNotExists( $thumbnail_file_inmeta );
			$this->assertArrayNotHasKey( 'regenerate-thumbnails-test-inmeta', $new_metadata['sizes'] );
			$this->assertFileNotExists( $thumbnail_file_notinmeta );
			$this->assertArrayNotHasKey( 'regenerate-thumbnails-test-notinmeta', $new_metadata['sizes'] );
		} else {
			$this->assertFileExists( $thumbnail_file_inmeta );
			$this->assertArrayHasKey( 'regenerate-thumbnails-test-inmeta', $new_metadata['sizes'] );
			$this->assertFileExists( $thumbnail_file_notinmeta );
		}

		$this->assertFileExists( $thumbnail_file_to_keep );
	}

	public function test_verify_that_site_icons_are_not_regenerated() {
		$this->attachment_id = $this->helper_create_attachment();

		// See wp_ajax_crop_image()

		require_once( ABSPATH . '/wp-admin/includes/class-wp-site-icon.php' );
		$wp_site_icon = new WP_Site_Icon();

		$cropped = wp_crop_image( $this->attachment_id, 1300, 300, 512, 512, 512, 512 );
		$this->assertInternalType( 'string', $cropped );

		$object = $wp_site_icon->create_attachment_object( $cropped, $this->attachment_id );
		unset( $object['ID'] );

		// Update the attachment.
		add_filter( 'intermediate_image_sizes_advanced', array( $wp_site_icon, 'additional_sizes' ) );
		$this->attachment_id = $wp_site_icon->insert_attachment( $object, $cropped );
		remove_filter( 'intermediate_image_sizes_advanced', array( $wp_site_icon, 'additional_sizes' ) );

		$attachment_metadata = wp_get_attachment_metadata( $this->attachment_id );

		$thumbnails = array();
		foreach ( $attachment_metadata['sizes'] as $size => $size_data ) {
			$thumbnails[ $size ] = $size_data['file'];
		}

		$upload_dir = dirname( get_attached_file( $this->attachment_id ) ) . DIRECTORY_SEPARATOR;

		$filemtimes = $this->helper_get_filemtimes( $upload_dir, $thumbnails );

		// Sleep to make sure that filemtime() will change if the thumbnail files do
		sleep( 1 );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );

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

	public function test_update_usages_in_posts() {
		$this->attachment_id = $this->helper_create_attachment();

		$thumbnail_thumbnail = image_downsize( $this->attachment_id, 'thumbnail' );
		$thumbnail_medium    = image_downsize( $this->attachment_id, 'medium' );
		$thumbnail_large     = image_downsize( $this->attachment_id, 'large' );

		$test_contents = array(
			'<img src="' . esc_attr( $thumbnail_medium[0] ) . '" alt="" width="300" height="169" class="align size-medium wp-image-' . $this->attachment_id . '" />',
			'<a href="https://wordpress.org/"><img src="' . esc_attr( $thumbnail_large[0] ) . '" alt="WordPress" width="1024" height="576" class="align size-large wp-image-' . $this->attachment_id . '" /></a>',
			'[caption id="attachment_' . $this->attachment_id . '" align="aligncenter" width="300"]<img src="' . esc_attr( $thumbnail_medium[0] ) . '" alt="" width="300" height="169" class="size-medium wp-image-' . $this->attachment_id . '" /> This is the caption[/caption]',
			'<img class="cssclass wp-image-' . $this->attachment_id . ' size-thumbnail" title="img title" src="' . esc_attr( $thumbnail_thumbnail[0] ) . '" alt="alt" width="150" height="56" />',
		);

		$post_ids = array();
		foreach ( $test_contents as $test_content ) {
			$post_ids[] = self::factory()->post->create( array(
				'post_content' => $test_content,
			) );
		}

		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$regenerator->regenerate();
		$regenerator->update_usages_in_posts();

		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
			remove_filter( 'pre_option_' . $filter, $function );
		}

		$thumbnail_thumbnail = image_downsize( $this->attachment_id, 'thumbnail' );
		$thumbnail_medium    = image_downsize( $this->attachment_id, 'medium' );
		$thumbnail_large     = image_downsize( $this->attachment_id, 'large' );

		$result_contents = array(
			'<img src="' . esc_attr( $thumbnail_medium[0] ) . '" alt="" width="350" height="197" class="align size-medium wp-image-' . $this->attachment_id . '" />',
			'<a href="https://wordpress.org/"><img src="' . esc_attr( $thumbnail_large[0] ) . '" alt="WordPress" width="1500" height="844" class="align size-large wp-image-' . $this->attachment_id . '" /></a>',
			'[caption id="attachment_' . $this->attachment_id . '" align="aligncenter" width="350"]<img src="' . esc_attr( $thumbnail_medium[0] ) . '" alt="" width="350" height="197" class="size-medium wp-image-' . $this->attachment_id . '" /> This is the caption[/caption]',
			'<img class="cssclass wp-image-' . $this->attachment_id . ' size-thumbnail" title="img title" src="' . esc_attr( $thumbnail_thumbnail[0] ) . '" alt="alt" width="100" height="56" />',
		);

		$counter = 0;
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );

			$this->assertSame( $result_contents[ $counter ], $post->post_content );

			$counter ++;
		}
	}

	public function helper_get_current_thumbnail_statuses() {
		$attachment   = get_post( $this->attachment_id );
		$fullsizepath = get_attached_file( $this->attachment_id );

		return array(
			'name'               => $attachment->post_title,
			'preview'            => wp_get_attachment_url( $this->attachment_id ),
			'relative_path'      => _wp_get_attachment_relative_path( $fullsizepath ) . DIRECTORY_SEPARATOR . '33772.jpg',
			'edit_url'           => get_edit_post_link( $attachment->ID, 'raw' ),
			'width'              => 1920,
			'height'             => 1080,
			'registered_sizes'   => array(
				array(
					'label'      => 'thumbnail',
					'width'      => 150,
					'height'     => 150,
					'crop'       => true,
					'filename'   => '33772-150x150.jpg',
					'fileexists' => true,
				),
				array(
					'label'      => 'medium',
					'width'      => 300,
					'height'     => 300,
					'crop'       => false,
					'filename'   => '33772-300x169.jpg',
					'fileexists' => true,
				),
				array(
					'label'      => 'medium_large',
					'width'      => 768,
					'height'     => 0,
					'crop'       => false,
					'filename'   => '33772-768x432.jpg',
					'fileexists' => true,
				),
				array(
					'label'      => 'large',
					'width'      => 1024,
					'height'     => 1024,
					'crop'       => false,
					'filename'   => '33772-1024x576.jpg',
					'fileexists' => true,
				),
			),
			'unregistered_sizes' => array(),
		);
	}

	public function test_get_current_thumbnail_statuses_normal() {
		// To get edit_url to work correctly
		$admin_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $admin_id );

		$this->attachment_id = $this->helper_create_attachment();

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$statuses    = $regenerator->get_attachment_info();

		$this->assertSame( $statuses, $this->helper_get_current_thumbnail_statuses() );
	}

	public function test_get_current_thumbnail_statuses_with_unregistered_size() {
		add_image_size( 'regenerate-thumbnails-test', 500, 500 );
		$this->attachment_id = $this->helper_create_attachment();
		remove_image_size( 'regenerate-thumbnails-test' );

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$statuses    = $regenerator->get_attachment_info();

		$expected_statuses = $this->helper_get_current_thumbnail_statuses();

		$expected_statuses['unregistered_sizes'][] = array(
			'label'      => 'regenerate-thumbnails-test',
			'width'      => 500,
			'height'     => 281,
			'filename'   => '33772-500x281.jpg',
			'fileexists' => true,
		);

		$this->assertSame( $statuses, $expected_statuses );
	}

	public function test_get_current_thumbnail_statuses_with_changed_sizes() {
		$this->attachment_id = $this->helper_create_attachment();

		// Now change the thumbnail sizes to something other than the defaults
		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
			add_filter( 'pre_option_' . $filter, $function );
		};

		$regenerator = RegenerateThumbnails_Regenerator::get_instance( $this->attachment_id );
		$statuses    = $regenerator->get_attachment_info();

		foreach ( $this->helper_get_custom_thumbnail_size_callbacks() as $filter => $function ) {
			remove_filter( 'pre_option_' . $filter, $function );
		}

		$expected_statuses = $this->helper_get_current_thumbnail_statuses();

		$expected_statuses['registered_sizes'] = array(
			array(
				'label'      => 'thumbnail',
				'width'      => 100,
				'height'     => 100,
				'crop'       => false,
				'filename'   => '33772-100x56.jpg',
				'fileexists' => false,
			),
			array(
				'label'      => 'medium',
				'width'      => 350,
				'height'     => 350,
				'crop'       => false,
				'filename'   => '33772-350x197.jpg',
				'fileexists' => false,
			),
			array(
				'label'      => 'medium_large',
				'width'      => 500,
				'height'     => 500,
				'crop'       => false,
				'filename'   => '33772-500x281.jpg',
				'fileexists' => false,
			),
			array(
				'label'      => 'large',
				'width'      => 1500,
				'height'     => 1500,
				'crop'       => false,
				'filename'   => '33772-1500x844.jpg',
				'fileexists' => false,
			),
		);

		$expected_statuses['unregistered_sizes'] = array(
			array(
				'label'      => sprintf( __( '%s (old)', 'regenerate-thumbnails' ), 'thumbnail' ),
				'width'      => 150,
				'height'     => 150,
				'filename'   => '33772-150x150.jpg',
				'fileexists' => true,
			),
			array(
				'label'      => sprintf( __( '%s (old)', 'regenerate-thumbnails' ), 'medium' ),
				'width'      => 300,
				'height'     => 169,
				'filename'   => '33772-300x169.jpg',
				'fileexists' => true,
			),
			array(
				'label'      => sprintf( __( '%s (old)', 'regenerate-thumbnails' ), 'medium_large' ),
				'width'      => 768,
				'height'     => 432,
				'filename'   => '33772-768x432.jpg',
				'fileexists' => true,
			),
			array(
				'label'      => sprintf( __( '%s (old)', 'regenerate-thumbnails' ), 'large' ),
				'width'      => 1024,
				'height'     => 576,
				'filename'   => '33772-1024x576.jpg',
				'fileexists' => true,
			),
		);

		$this->assertSame( $statuses, $expected_statuses );
	}
}
