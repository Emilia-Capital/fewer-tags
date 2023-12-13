<?php
/**
 * Handles all frontend functions.
 *
 * @package FewerTags
 */

namespace FewerTags;

/**
 * FewerTags Frontend Class
 */
class Frontend {

	/**
	 * Register the needed hooks.
	 */
	public function register_hooks() {
		add_action( 'template_redirect', [ $this, 'redirect_tag_pages' ] );
		add_filter( 'get_the_tags', [ $this, 'filter_get_the_tags' ] );
		add_filter( 'get_the_terms', [ $this, 'filter_get_the_terms' ], 10, 3 );
		add_filter( 'wpseo_exclude_from_sitemap_by_term_ids', [ $this, 'exclude_tags_from_yoast_sitemap' ] );
		add_filter( 'wp_sitemaps_taxonomies_query_args', [ $this, 'exclude_tags_from_core_sitemap' ], 10, 2 );
	}

	/**
	 * Redirect tag pages with fewer than the specified number of posts to the home page.
	 */
	public function redirect_tag_pages() {
		if ( is_tag() ) {
			$tag = get_queried_object();
			if ( $tag && $tag->count < \FewerTags::$min_posts_count ) {
				wp_safe_redirect( home_url(), 301 );
				exit;
			}
		}
	}

	/**
	 * Filters the list of terms retrieved for a post to exclude tags with fewer than the specified minimum number of posts.
	 *
	 * Only works on post_tag taxonomy.
	 *
	 * @param WP_Term[]|WP_Error $terms    Array of attached terms, or WP_Error on failure.
	 * @param int                $post_id  Post ID.
	 * @param string             $taxonomy Name of the taxonomy.
	 *
	 * @return array The filtered array of term objects.
	 */
	public function filter_get_the_terms( $terms, $post_id, $taxonomy ) {
		if ( $taxonomy !== 'post_tag' || is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( is_array( $terms ) ) {
			foreach ( $terms as $key => $tag ) {
				if ( $tag->count < \FewerTags::$min_posts_count ) {
					unset( $terms[ $key ] );
				}
			}
		}

		return $terms;
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
				if ( $tag->count < \FewerTags::$min_posts_count ) {
					unset( $tags[ $key ] );
				}
			}
		}

		return $tags;
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
				return ( $tag->count < \FewerTags::$min_posts_count );
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
		if ( \FewerTags::$min_posts_count === 0 ) {
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
				if ( $term->count < \FewerTags::$min_posts_count ) {
					$excluded_term_ids[] = $tag_id;
				}
			}
		}

		return $excluded_term_ids;
	}
}
