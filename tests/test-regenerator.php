<?php
/**
 * Class Regenerate_Thumbnails_Tests_Regenerator
 *
 * @package Regenerate_Thumbnails
 * @subpackage Regenerator
 */

/**
 * Tests for the RegenerateThumbnails_Regenerator class.
 * @group regenerator
 */
class Regenerate_Thumbnails_Tests_Regenerator extends WP_UnitTestCase {

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
		$result = $regenerator->regenerate();

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'regenerate_thumbnails_regenerator_file_not_found', $result->get_error_code() );
	}
}