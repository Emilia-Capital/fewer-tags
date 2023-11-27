<?php
/**
 * Plugin that redirects tag pages to the home page if they contain fewer than a specified number of posts.
 *
 * @package JoostFewerTags
 * @version 1.0.1
 *
 * Plugin Name:       Fewer Tags
 * Plugin URI:        https://joost.blog/plugins/fewer-tags/
 * Description:       Redirects tag pages to the home page if they contain fewer than a specified number of posts, defaults to 10. Change under Settings > Reading. Results in fewer useFewer tags, which is good for SEO.
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Version:           1.1
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
 * JoostFewerTags Class
 */
class JoostFewerTags {

	/**
	 * Default value for the minimum number of posts a tag should have to not be redirected to the homepage.
	 *
	 * @var int
	 */
	private $min_posts_count;

	/**
	 * Constructor method
	 */
	public function __construct() {
		$this->min_posts_count = get_option( 'joost_min_posts_count', 10 );
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin and register hooks.
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'template_redirect', [ $this, 'redirect_tag_pages' ] );
		add_filter( 'get_the_tags', [ $this, 'filter_get_the_tags' ] );
		add_filter( 'manage_edit-post_tag_columns', [ $this, 'add_tag_columns' ] );
		add_filter( 'manage_post_tag_custom_column', [ $this, 'manage_tag_columns' ], 10, 3 );
		add_filter( 'tag_row_actions', [ $this, 'remove_view_action' ], 10, 2 );
		add_filter( 'wpseo_exclude_from_sitemap_by_term_ids', [ $this, 'exclude_tags_from_yoast_sitemap' ] );
		add_filter( 'wp_sitemaps_taxonomies_query_args', [ $this, 'exclude_tags_from_core_sitemap' ], 10, 2 );
	}

	/**
	 * Register settings and add settings field to the Reading settings page.
	 */
	public function register_settings() {
		add_settings_section(
			'fewer_tags_section',
			__( 'Fewer tags settings', 'fewer-tags' ),
			[ $this, 'display_section' ],
			'reading'
		);

		add_settings_field(
			'joost_min_posts_count',
			__( 'Tags need to have', 'fewer-tags' ),
			[ $this, 'display_setting' ],
			'reading',
			'fewer_tags_section'
		);

		register_setting( 'reading', 'joost_min_posts_count' );
	}

	/**
	 * Display the section text.
	 */
	public function display_section() {
		esc_html_e( 'Set the minimum number of posts a tag should have to become live on the site and not be redirected to the homepage.', 'fewer-tags' );
	}

	/**
	 * Display the setting field in the Reading settings page.
	 */
	public function display_setting() {
		echo '<input name="joost_min_posts_count" id="joost_min_posts_count" type="number" min="1" value="' . esc_attr( $this->min_posts_count ) . '" class="small-text" /> ';
		esc_html_e( 'posts before being live on the site.', 'fewer-tags' );
	}

	/**
	 * Redirect tag pages with fewer than the specified number of posts to the home page.
	 */
	public function redirect_tag_pages() {
		if ( is_tag() ) {
			$tag = get_queried_object();
			if ( $tag && $tag->count < $this->min_posts_count ) {
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
				if ( $tag->count < $this->min_posts_count ) {
					unset( $tags[ $key ] );
				}
			}
		}

		return $tags;
	}

	/**
	 * Filters the list of terms (including tags) retrieved to exclude tags with fewer than the specified minimum number of posts.
	 *
	 * @param array    $terms      The list of terms retrieved.
	 * @param string[] $taxonomies The taxonomies for which terms were retrieved.
	 *
	 * @return array The filtered list of terms.
	 */
	public function filter_get_terms( $terms, $taxonomies ) {
		if ( is_admin() ) {
			return $terms;
		}

		if ( in_array( 'post_tag', $taxonomies, true ) && is_array( $terms ) ) {
			foreach ( $terms as $key => $term ) {
				if ( is_object( $term ) && $term->count < $this->min_posts_count ) {
					unset( $terms[ $key ] );
				}
			}
		}

		return $terms;
	}

	/**
	 * Adds a new column to the tag list table to show whether a tag is active or inactive.
	 *
	 * @param array $columns The existing array of columns.
	 *
	 * @return array The modified array of columns.
	 */
	public function add_tag_columns( $columns ) {
		$columns['active'] = __( 'Live on site', 'fewer-tags' );
		return $columns;
	}

	/**
	 * Manages the output for the custom column in the tag list table.
	 *
	 * @param string $out         The output for the custom column (this will be empty initially).
	 * @param string $column_name The name of the custom column.
	 * @param int    $tag_ID      The ID of the tag being displayed.
	 *
	 * @return string The output for the custom column.
	 */
	public function manage_tag_columns( $out, $column_name, $tag_ID ) {
		if ( $column_name === 'active' ) {
			$term = get_term( $tag_ID );
			$out  = esc_html__( 'Live', 'fewer-tags' );
			if ( $term->count < $this->min_posts_count ) {
				$out = '<span title="' . esc_html__( 'Not live due to not enough posts being in this tag.', 'fewer-tags' ) . '">' . esc_html__( 'Not live', 'fewer-tags' ) . '</span>';
			}
		}

		return $out;
	}

	/**
	 * Removes the "View" action link for tags that have fewer than the minimum number of posts.
	 *
	 * @param array   $actions An array of action links.
	 * @param WP_Term $tag     Current WP_Term object.
	 *
	 * @return array Modified array of action links.
	 */
	public function remove_view_action( $actions, $tag ) {
		if ( $tag->count < $this->min_posts_count ) {
			unset( $actions['view'] );
		}

		return $actions;
	}

	/**
	 * Excludes tags with fewer than the minimum number of posts from the core sitemap.
	 *
	 * @param array  $args     The arguments for the sitemap query.
	 * @param string $taxonomy The taxonomy for which the sitemap is being generated.
	 *
	 * @return array The modified arguments for the sitemap query.
	 */
	public function exclude_tags_from_core_sitemap( $args, $taxonomy ) {
		if ( $taxonomy !== 'post_tag' ) {
			return $args;
		}

		if ( ! isset( $args['exclude'] ) ) {
			$args['exclude'] = [];
		}

		// exclude terms with too few posts.
		$args['exclude'] = array_merge( $args['exclude'], $this->get_tag_ids_with_fewer_than_min_posts() );
		return $args;
	}

	/**
	 * Fetches the IDs of all tags with fewer than the given minimum post count.
	 *
	 * @return array List of term IDs that have fewer posts than the specified count.
	 */
	public function get_tag_ids_with_fewer_than_min_posts() {
		$args = [
			'taxonomy'   => 'post_tag',
			'fields'     => 'ids',  // Only fetch term IDs.
			'hide_empty' => true, // Change to false if you want to include tags with zero posts.
		];

		// Fetch all tag IDs.
		$tag_ids = get_terms( $args );

		// Filter tag IDs based on post count.
		$filtered_tag_ids = array_filter(
			$tag_ids,
			function ( $tag_id ) {
				$tag = get_term( $tag_id, 'post_tag' );
				return ( $tag->count < $this->min_posts_count );
			}
		);
		return $filtered_tag_ids;
	}

	/**
	 * Excludes tags with fewer than the minimum number of posts from the Yoast SEO sitemap.
	 *
	 * @param array $excluded_term_ids An array of term IDs to exclude from the sitemap.
	 *
	 * @return array Modified array of term IDs to exclude from the sitemap.
	 */
	public function exclude_tags_from_yoast_sitemap( $excluded_term_ids ) {
		if ( $this->min_posts_count === 0 ) {
			return $excluded_term_ids;
		}

		$args = [
			'taxonomy'   => 'post_tag',
			'fields'     => 'ids',
			'hide_empty' => true,
			'number'     => 0,
		];

		$tags = get_terms( $args );

		if ( ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag_id ) {
				$term = get_term( $tag_id );
				if ( $term->count < $this->min_posts_count ) {
					$excluded_term_ids[] = $tag_id;
				}
			}
		}

		return $excluded_term_ids;
	}
}

// Instantiate the plugin class.
new JoostFewerTags();
