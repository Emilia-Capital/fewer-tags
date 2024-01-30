<?php
/**
 * Handles all admin functions.
 *
 * @package FewerTags
 */

namespace FewerTags;

use FewerTags\Plugin;

/**
 * FewerTags Admin Class
 */
class Admin {

	/**
	 * Register the needed hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_filter( 'manage_edit-post_tag_columns', [ $this, 'add_tag_columns' ] );
		add_filter( 'manage_post_tag_custom_column', [ $this, 'manage_tag_columns' ], 10, 3 );
		add_filter( 'tag_row_actions', [ $this, 'remove_view_action' ], 10, 2 );
	}

	/**
	 * Register settings and add settings field to the Reading settings page.
	 */
	public function register_settings() {
		add_settings_section(
			'fewer_tags_section',
			__( 'Fewer Tags settings', 'fewer-tags' ),
			[ $this, 'display_section' ],
			'reading'
		);

		add_settings_field(
			Plugin::$option_name,
			__( 'Tags need to have', 'fewer-tags' ),
			[ $this, 'display_setting' ],
			'reading',
			'fewer_tags_section'
		);

		register_setting( 'reading', Plugin::$option_name );
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
		?>
		<input
			name="<?php echo esc_attr( Plugin::$option_name ); ?>"
			id="<?php echo esc_attr( Plugin::$option_name ); ?>"
			type="number"
			min="1"
			value="<?php echo esc_attr( Plugin::$min_posts_count ); ?>"
			class="small-text"
		/>
		<?php esc_html_e( 'posts before being live on the site.', 'fewer-tags' ); ?>
		<?php
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
			if ( $term->count < \FewerTags\Plugin::$min_posts_count ) {
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
		if ( $tag->count < \FewerTags\Plugin::$min_posts_count ) {
			unset( $actions['view'] );
		}

		return $actions;
	}
}
