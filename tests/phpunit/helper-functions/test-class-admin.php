<?php
/**
 * Class SampleTest
 *
 * @package FewerTags
 */

/**
 * Sample test case.
 */
class Admin extends WP_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Admin
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
	 * Tests class constructor.
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
}
