<?php
/**
 * Plugin that redirects tag pages to the home page if they contain fewer than a specified number of posts.
 *
 * @package FewerTags
 * @version 1.3.1
 *
 * Plugin Name:       Fewer Tags
 * Plugin URI:        https://joost.blog/plugins/fewer-tags/
 * Description:       Redirects tag pages to the home page if they contain fewer than a specified number of posts, defaults to 10. Change under Settings > Reading. Results in fewer useFewer tags, which is good for SEO.
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Version:           1.3.1
 * Author:            Joost de Valk
 * Author URI:        https://joost.blog
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       fewer-tags
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * FewerTags Class
 */
class FewerTags {

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

		require __DIR__ . '/vendor/autoload.php';

		if ( is_admin() ) {
			$admin = new FewerTags\Admin();
			$admin->register_hooks();
			return;
		}
		$frontend = new FewerTags\Frontend();
		$frontend->register_hooks();
	}
}

// Instantiate the plugin class.
$fewer_tags = new FewerTags();
$fewer_tags->register_hooks();
