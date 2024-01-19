<?php
/**
 * Class Frontend_Test
 *
 * @package FewerTags
 */

namespace FewerTags\Tests;

use FewerTags\Plugin;
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
	 * Test post.
	 *
	 * @var int
	 */
	private static $test_post;

	/**
	 * Set up the class instance to be tested.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$class_instance = new Frontend();

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
		// add 1 post with both the live and the not live tag.
		self::$test_post = wp_insert_post(
			[
				'post_title'  => 'Post ' . $i,
				'post_status' => 'publish',
				'tags_input'  => [ 'Live tag', 'Not alive' ],
			]
		);
	}

	/**
	 * Set up the test.
	 */
	public function set_up() {
		parent::set_up();
		add_filter( 'wp_redirect', [ $this, 'filter_wp_redirect' ], 10, 2 );
	}

	/**
	 * Add an exception to wp_redirect / wp_safe_redirect so we can test it.
	 *
	 * @param string $location The redirect location.
	 * @param int    $status   The redirect status.
	 *
	 * @throws \Exception When redirects is being tried.
	 */
	public function filter_wp_redirect( $location, $status ) {
		throw new \Exception(
			wp_json_encode(
				[
					'location' => $location,
					'status'   => $status,
				]
			)
		);
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

	/**
	 * Tests redirect_tag_pages().
	 *
	 * @covers FewerTags\Frontend::redirect_tag_pages
	 */
	public function test_redirect_tag_pages() {
		$this->go_to( get_tag_link( self::$not_live_tag['term_id'] ) );

		// Set the global $wp_query object as a tag page with fewer posts than required.
		$GLOBALS['wp_query']->is_tag = true;

		// Assuming \FewerTags\Plugin::$min_posts_count > 1.
		$GLOBALS['wp_query']->queried_object = (object) [ 'count' => 1 ];

		try {
			self::$class_instance->redirect_tag_pages();
		} catch ( \Exception $e ) {
			$redirect = json_decode( $e->getMessage(), true );
			$this->assertSame( home_url(), $redirect['location'] );
			$this->assertSame( 301, $redirect['status'] );
		}

		$this->go_to( get_tag_link( self::$live_tag['term_id'] ) );

		// Set the global $wp_query object as a tag page with fewer posts than required.
		$GLOBALS['wp_query']->is_tag = true;

		// Assuming \FewerTags\Plugin::$min_posts_count > 1.
		$GLOBALS['wp_query']->queried_object = (object) [ 'count' => 11 ];

		self::$class_instance->redirect_tag_pages();
		$this->assertFalse( (bool) did_action( 'wp_redirect' ) );
	}

	/**
	 * Tests filter_get_the_terms().
	 *
	 * @covers FewerTags\Frontend::filter_get_the_terms
	 */
	public function test_filter_get_the_terms() {
		$live_tag     = get_term( self::$live_tag['term_id'], 'post_tag' );
		$not_live_tag = get_term( self::$not_live_tag['term_id'], 'post_tag' );

		// Test when count is 5, and the post has both the live and the not live tag.
		$terms = self::$class_instance->filter_get_the_terms( [ $live_tag, $not_live_tag ], self::$test_post, 'post_tag' );
		$this->assertSame( [ $live_tag ], $terms );

		Plugin::$min_posts_count = 1;

		// Test when count is 1, and the post has both the live and the not live tag (which has 1 post in it, so _is_ live now).
		$terms = self::$class_instance->filter_get_the_terms( [ $live_tag, $not_live_tag ], self::$test_post, 'post_tag' );
		$this->assertSame( [ $live_tag, $not_live_tag ], $terms );

		Plugin::$min_posts_count = 5;

		// Test with a taxonomy other than post_tag.
		$terms = self::$class_instance->filter_get_the_terms( [ $live_tag, $not_live_tag ], self::$test_post, 'category' );
		$this->assertSame( [ $live_tag, $not_live_tag ], $terms );
	}

	/**
	 * Tests filter_get_the_tags().
	 *
	 * @covers FewerTags\Frontend::filter_get_the_tags
	 */
	public function test_filter_get_the_tags() {
		$live_tag     = get_term( self::$live_tag['term_id'], 'post_tag' );
		$not_live_tag = get_term( self::$not_live_tag['term_id'], 'post_tag' );

		$tags = self::$class_instance->filter_get_the_tags( [ $live_tag, $not_live_tag ] );
		$this->assertSame( [ $live_tag ], $tags );

		Plugin::$min_posts_count = 1;

		$tags = self::$class_instance->filter_get_the_tags( [ $live_tag, $not_live_tag ] );
		$this->assertSame( [ $live_tag, $not_live_tag ], $tags );

		Plugin::$min_posts_count = 5;
	}

	/**
	 * Tests exclude_tags_from_yoast_sitemap().
	 *
	 * @covers FewerTags\Frontend::exclude_tags_from_yoast_sitemap
	 */
	public function test_exclude_tags_from_yoast_sitemap() {
		$term_ids = self::$class_instance->exclude_tags_from_yoast_sitemap( [] );
		$this->assertSame( [ self::$not_live_tag['term_id'] ], $term_ids );

		Plugin::$min_posts_count = 1;

		$term_ids = self::$class_instance->exclude_tags_from_yoast_sitemap( [] );
		$this->assertSame( [], $term_ids );

		Plugin::$min_posts_count = 0;

		// Test when count = 0 we don't do anything with the input.
		$term_ids = self::$class_instance->exclude_tags_from_yoast_sitemap( [ 'test' ] );
		$this->assertSame( [ 'test' ], $term_ids );

		Plugin::$min_posts_count = 5;
	}

	/**
	 * Tests exclude_tags_from_core_sitemap().
	 *
	 * @covers FewerTags\Frontend::exclude_tags_from_core_sitemap
	 * @covers FewerTags\Frontend::get_tag_ids_with_fewer_than_min_posts
	 */
	public function test_exclude_tags_from_core_sitemap() {
		$terms = self::$class_instance->exclude_tags_from_core_sitemap( [], 'post_tag' );
		$this->assertSame( [ 'exclude' => [ self::$not_live_tag['term_id'] ] ], $terms );

		Plugin::$min_posts_count = 1;

		$terms = self::$class_instance->exclude_tags_from_core_sitemap( [], 'post_tag' );
		$this->assertSame( [ 'exclude' => [] ], $terms );

		// Test with a taxonomy other than post_tag.
		$terms = self::$class_instance->exclude_tags_from_core_sitemap( [], 'category' );
		$this->assertSame( [], $terms );

		Plugin::$min_posts_count = 5;
	}
}
