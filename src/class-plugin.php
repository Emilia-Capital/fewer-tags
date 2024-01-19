<?php
/**
 * The main plugin class.
 *
 * @package FewerTags
 */

namespace FewerTags;

/**
 * FewerTags Class
 */
class Plugin {

	/**
	 * Default value for the minimum number of posts a tag should have to not be redirected to the homepage.
	 *
	 * @var int
	 */
	public static $min_posts_count;

	/**
	 * Register plugin hooks.
	 */
	public function register_hooks() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin and register hooks.
	 */
	public function init() {
		self::$min_posts_count = (int) get_option( 'joost_min_posts_count', 10 );

		if ( is_admin() ) {
			$admin = new Admin();
			$admin->register_hooks();

			// Detect if we're running on the playground, if so, load our playground specific class.
			if ( defined( 'IS_PLAYGROUND_PREVIEW' ) && IS_PLAYGROUND_PREVIEW ) {
				$playground = new Playground();
				$playground->register_hooks();
			}

			return;
		}
		$frontend = new Frontend();
		$frontend->register_hooks();
	}
}
