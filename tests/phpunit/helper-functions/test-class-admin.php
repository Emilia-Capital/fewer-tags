<?php
/**
 * Class SampleTest
 *
 * @package FewerTags
 */

namespace FewerTags\Tests;

use FewerTags\Admin;

/**
 * Sample test case.
 */
class Admin_Test extends \WP_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var FewerTags\Admin
	 */
	private static $class_instance;

	/**
	 * Set up the class instance to be tested.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$class_instance = new Admin();
	}

	/**
	 * Tests hooks registration.
	 *
	 * @covers Admin::register_hooks
	 */
	public function test_register_hooks() {
		self::$class_instance->register_hooks();

		$this->assertSame( 10, has_action( 'admin_init', [ self::$class_instance, 'register_settings' ] ) );
		$this->assertSame( 10, has_action( 'manage_edit-post_tag_columns', [ self::$class_instance, 'add_tag_columns' ] ) );
		$this->assertSame( 10, has_action( 'manage_post_tag_custom_column', [ self::$class_instance, 'manage_tag_columns' ] ), 10, 3 );
		$this->assertSame( 10, has_action( 'tag_row_actions', [ self::$class_instance, 'remove_view_action' ] ), 10, 2 );
	}

	/**
	 * Tests settings registration.
	 *
	 * @covers Admin::register_settings
	 */
	public function test_register_settings() {
		self::$class_instance->register_settings();

		global $wp_settings_sections, $wp_settings_fields;

		$this->assertArrayHasKey( 'fewer_tags_section', $wp_settings_sections );
		$this->assertArrayHasKey( 'joost_min_posts_count', $wp_settings_fields );
	}

	/**
	 * Tests display section.
	 *
	 * @covers Admin::display_section
	 */
	public function test_display_section() {
		ob_start();
		self::$class_instance->display_section();
		$output = ob_get_clean();

		$this->assertSame( 'Set the minimum number of posts a tag should have to become live on the site and not be redirected to the homepage.', $output );
	}
}
