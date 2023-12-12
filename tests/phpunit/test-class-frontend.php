<?php
/**
 * Class Frontend_Test
 *
 * @package FewerTags
 */

namespace FewerTags\Tests;

use FewerTags;
use FewerTags\Frontend;

/**
 * Sample test case.
 */
class Frontend_Test extends \WP_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var FewerTags\Frontend
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
		self::$class_instance = new Frontend();

		FewerTags::$min_posts_count = 5;

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
	 * @covers FewerTags\Frontend::register_hooks
	 */
	public function test_register_hooks() {
		self::$class_instance->register_hooks();

		$this->assertSame( 10, has_action( 'template_redirect', [ self::$class_instance, 'redirect_tag_pages' ] ) );
		$this->assertSame( 10, has_filter( 'get_the_tags', [ self::$class_instance, 'filter_get_the_tags' ] ) );
		$this->assertSame( 10, has_filter( 'get_the_terms', [ self::$class_instance, 'filter_get_the_terms' ] ) );
		$this->assertSame( 10, has_filter( 'wpseo_exclude_from_sitemap_by_term_ids', [ self::$class_instance, 'exclude_tags_from_yoast_sitemap' ] ) );
		$this->assertSame( 10, has_filter( 'wp_sitemaps_taxonomies_query_args', [ self::$class_instance, 'exclude_tags_from_core_sitemap' ] ) );
	}
}
