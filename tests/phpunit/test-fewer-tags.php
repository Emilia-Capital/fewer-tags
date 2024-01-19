<?php
/**
 * Class Frontend_Test
 *
 * @package FewerTags
 */

namespace FewerTags\Tests;

use FewerTags\Plugin;
use FewerTags\Admin;
use FewerTags\Frontend;

/**
 * Sample test case.
 */
class FewerTags_Test extends \WP_UnitTestCase {
	/**
	 * Instance of the class being tested.
	 *
	 * @var FewerTags
	 */
	private static $class_instance;

	/**
	 * Set up the class instance to be tested.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		update_option( 'joost_min_posts_count', 10 );

		self::$class_instance = new Plugin();
	}

	/**
	 * Tests hooks registration.
	 *
	 * @covers FewerTags\Plugin::register_hooks
	 */
	public function test_register_hooks() {
		self::$class_instance->register_hooks();

		$this->assertSame( 10, has_action( 'init', [ self::$class_instance, 'init' ] ) );
	}

	/**
	 * Tests init method.
	 *
	 * @covers FewerTags\Plugin::init
	 */
	public function test_init() {
		self::$class_instance->init();

		$this->assertSame( 10, self::$class_instance::$min_posts_count );
	}

	/**
	 * Tests init method.
	 *
	 * @covers FewerTags\Plugin::init
	 */
	public function test_init_admin() {
		// Destroy the existing instance.
		self::$class_instance = null;

		// Make it an admin environment.
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		$user    = wp_set_current_user( $user_id );

		set_current_screen( 'dashboard' );

		self::$class_instance = new Plugin();
		self::$class_instance->init();

		global $wp_filter;
		$test_function = '';
		foreach ( $wp_filter['manage_post_tag_custom_column'][10] as $key => $hooked_function ) {
			if ( $hooked_function['function'][0] instanceof Admin ) {
				$test_function = $hooked_function['function'][1];
			}
		}
		$this->assertSame( 'manage_tag_columns', $test_function );
	}
}
