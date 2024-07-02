<?php
/**
 * Class Admin_Test
 *
 * @package FewerTags
 */

namespace FewerTags\Tests;

use FewerTags\Plugin;
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
	 * Live tag.
	 *
	 * @var array
	 */
	private static $live_tag;

	/**
	 * Not live tag.
	 *
	 * @var array
	 */
	private static $not_live_tag;

	/**
	 * Set up the class instance to be tested.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$class_instance = new Admin();

		Plugin::$min_posts_count = 5;

		self::$live_tag     = wp_insert_term( 'Live tag', 'post_tag' );
		self::$not_live_tag = wp_insert_term( 'Not alive', 'post_tag' );

		$i = 0;
		while ( $i < 11 ) {
			wp_insert_post(
				[
					'post_title'  => 'Post ' . $i,
					'post_status' => 'publish',
					'tags_input'  => 'Live tag',
				]
			);
			++$i;
		}
	}

	/**
	 * Tests hooks registration.
	 *
	 * @covers FewerTags\Admin::register_hooks
	 */
	public function test_register_hooks() {
		self::$class_instance->register_hooks();

		$this->assertSame( 10, has_action( 'admin_init', [ self::$class_instance, 'register_settings' ] ) );
		$this->assertSame( 10, has_action( 'manage_edit-post_tag_columns', [ self::$class_instance, 'add_tag_columns' ] ) );
		$this->assertSame( 10, has_action( 'manage_post_tag_custom_column', [ self::$class_instance, 'manage_tag_columns' ] ), 10, 3 );
		$this->assertSame( 10, has_action( 'post_tag_row_actions', [ self::$class_instance, 'remove_view_action' ] ), 10, 2 );
	}

	/**
	 * Tests settings registration.
	 *
	 * @covers FewerTags\Admin::register_settings
	 */
	public function test_register_settings() {
		self::$class_instance->register_settings();

		global $wp_settings_sections, $wp_settings_fields;

		$this->assertArrayHasKey( 'fewer_tags_section', $wp_settings_sections['reading'] );
		$this->assertArrayHasKey( Plugin::$option_name, $wp_settings_fields['reading']['fewer_tags_section'] );
	}

	/**
	 * Tests display section.
	 *
	 * @covers FewerTags\Admin::display_section
	 */
	public function test_display_section() {
		ob_start();
		self::$class_instance->display_section();
		$output = ob_get_clean();

		$this->assertSame( 'Set the minimum number of posts a tag should have to become live on the site and not be redirected to the homepage.', $output );
	}

	/**
	 * Tests display setting.
	 *
	 * @covers FewerTags\Admin::display_setting
	 */
	public function test_display_setting() {
		ob_start();
		self::$class_instance->display_setting();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'name="' . Plugin::$option_name . '"', $output );
		$this->assertStringContainsString( 'id="' . Plugin::$option_name . '"', $output );
		$this->assertStringContainsString( 'type="number"', $output );
		$this->assertStringContainsString( 'min="1"', $output );
		$this->assertStringContainsString( 'value="5"', $output ); // This is tied to the value of Plugin::$min_posts_count.
		$this->assertStringContainsString( 'class="small-text"', $output );
		$this->assertStringContainsString( 'posts before being live on the site.', $output );
	}

	/**
	 * Tests add tag columns.
	 *
	 * @covers FewerTags\Admin::add_tag_columns
	 */
	public function test_add_tag_columns() {
		$columns = self::$class_instance->add_tag_columns( [] );

		$this->assertArrayHasKey( 'active', $columns );
		$this->assertSame( 'Live on site', $columns['active'] );
	}

	/**
	 * Tests manage tag columns.
	 *
	 * @covers FewerTags\Admin::manage_tag_columns
	 */
	public function test_manage_tag_columns() {
		$out = self::$class_instance->manage_tag_columns( '', 'active', self::$not_live_tag['term_id'] );
		$this->assertMatchesRegularExpression( '/Not live/', $out );

		$out = self::$class_instance->manage_tag_columns( '', 'active', self::$live_tag['term_id'] );
		$this->assertSame( 'Live', $out );
	}

	/**
	 * Tests remove view action.
	 *
	 * @covers FewerTags\Admin::remove_view_action
	 */
	public function test_remove_view_action() {
		$actions = self::$class_instance->remove_view_action( $this->get_term_row_actions(), get_term( self::$not_live_tag['term_id'] ) );
		$this->assertArrayNotHasKey( 'view', $actions );

		$actions = self::$class_instance->remove_view_action( $this->get_term_row_actions(), get_term( self::$live_tag['term_id'] ) );
		$this->assertArrayHasKey( 'view', $actions );
	}

	/**
	 * Returns the term row actions.
	 *
	 * @return array
	 */
	private function get_term_row_actions() {
		return [
			'edit'   => 'Edit',
			'delete' => 'Delete',
			'view'   => 'View',
		];
	}
}
