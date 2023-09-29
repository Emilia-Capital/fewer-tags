<?php
/**
 * Plugin that redirects tag pages to the home page if they contain fewer than a specified number of posts.
 *
 * @package Joost_Blog_NurtureTags
 * @version 1.0
 *
 * Plugin Name: NurtureTags
 * Plugin URI: https://joost.blog/plugins/nurture-tags/
 * Description: Redirects tag pages to the home page if they contain fewer than a specified number of posts, defaults to 10. Change under Settings > Reading.
 * Version: 1.0
 * Author: Joost de Valk
 * Author URI: https://joost.blog/
 * License: GPL-3.0+
 * Text Domain: nurture-tags
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NurtureTags Class
 */
class Joost_Blog_NurtureTags {

	/**
	 * Default value for the minimum number of posts a tag should have to not be redirected to the homepage.
	 *
	 * @var int
	 */
	private $min_tag_count;

	/**
	 * Constructor method
	 */
	public function __construct() {
		$this->min_tag_count = get_option( 'nurture_tags_min_tag_count', 10 );
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin and register hooks.
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'template_redirect', [ $this, 'redirect_tag_pages' ] );
		add_filter( 'get_the_tags', [ $this, 'filter_get_the_tags' ] );
	}

	/**
	 * Register settings and add settings field to the Reading settings page.
	 */
	public function register_settings() {
		add_settings_section(
			'nurture_tags_section',
			__( 'Nurture tag settings', 'nurture-tags' ),
			[ $this, 'display_section' ],
			'reading'
		);

		add_settings_field(
			'nurture_tags_min_tag_count',
			__( 'Tags need to have', 'nurture-tags' ),
			[ $this, 'display_setting' ],
			'reading',
			'nurture_tags_section'
		);

		register_setting( 'reading', 'nurture_tags_min_tag_count' );
	}

	/**
	 * Display the section text.
	 */
	public function display_section() {
		echo '';
	}

	/**
	 * Display the setting field in the Reading settings page.
	 */
	public function display_setting() {
		echo '<input name="nurture_tags_min_tag_count" id="nurture_tags_min_tag_count" type="number" min="1" value="' . esc_attr( $this->min_tag_count ) . '" class="small-text" /> ';
		esc_html_e( 'posts before being live on the site.', 'nurture-tags' );
	}

	/**
	 * Redirect tag pages with less than the specified number of posts to the home page.
	 */
	public function redirect_tag_pages() {
		if ( is_tag() ) {
			$tag = get_queried_object();
			if ( $tag && $tag->count < $this->min_tag_count ) {
				wp_safe_redirect( home_url(), 301 );
				exit;
			}
		}
	}

	/**
	 * Filters the list of tags retrieved for a post to exclude tags with fewer than the specified minimum number of posts.
	 *
	 * @param array $tags The array of tag objects retrieved for the post.
	 *
	 * @return array The filtered array of tag objects.
	 */
	public function filter_get_the_tags( $tags ) {
		if ( is_array( $tags ) ) {
			foreach ( $tags as $key => $tag ) {
				if ( $tag->count < $this->min_tag_count ) {
					unset( $tags[ $key ] );
				}
			}
		}

		return $tags;
	}
}

// Instantiate the plugin class.
new Joost_Blog_NurtureTags();
