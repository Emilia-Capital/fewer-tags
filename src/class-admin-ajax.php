<?php
/**
 * AJAX handler class for merge and redirect operations.
 *
 * @package FewerTags
 */

namespace FewerTags;

/**
 * FewerTags Admin Ajax Class
 */
class Admin_Ajax {
	/**
	 * Holds the plugin's options class.
	 *
	 * @var Option
	 */
	public $options;

	/**
	 * Constructor method
	 */
	public function __construct() {
		$this->options = Option::get_instance();

		// While our code here isn't AJAX, these actions run in AJAX context.
		\add_action( 'pre_delete_term', [ $this, 'pre_delete_term' ], 10, 2 );
		\add_action( 'delete_term', [ $this, 'redirect_term_on_deletion' ], 10, 4 );

		// Our AJAX functions.
		\add_action( 'wp_ajax_fewer_tags_redirect_url', [ $this, 'redirect_url_action' ] );
		\add_action( 'wp_ajax_fewer_tags_merge_terms', [ $this, 'merge_terms' ] );
		\add_action( 'wp_ajax_fewer_tags_get_just_deleted_term', [ $this, 'get_just_deleted_term' ] );
		\add_action( 'wp_ajax_fewer_tags_dismiss_notice', [ $this, 'dismiss_notice' ] );
	}

	/**
	 * Stores an option to show a nag to redirect a tag when it's deleted.
	 *
	 * @param int      $term         The term ID.
	 * @param int      $tt_id        The term-taxonomy ID.
	 * @param string   $taxonomy     The taxonomy of the deleted term.
	 * @param \WP_Term $deleted_term The deleted term.
	 *
	 * @return void
	 */
	public function redirect_term_on_deletion( $term, $tt_id, $taxonomy, $deleted_term ) {
		$terms_to_redirect = $this->options->get( 'terms_to_redirect' );
		if ( ! is_array( $terms_to_redirect ) ) {
			$terms_to_redirect = [];
		}
		// Use the permalink captured in pre_delete_term, since the term is already deleted at this point.
		$permalink = $this->options->get( 'just_deleted_term_permalink' );
		$terms_to_redirect[ $taxonomy ][ $deleted_term->slug ] = [
			'object'    => $deleted_term,
			'permalink' => $permalink,
		];
		$this->options->set( 'terms_to_redirect', $terms_to_redirect );
	}

	/**
	 * Before deleting a term, store it in the options so we can offer to redirect it.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return void
	 */
	public function pre_delete_term( $term_id, $taxonomy ) {
		$deleted_term = \get_term( $term_id, $taxonomy );
		$this->options->set( 'just_deleted_term', $deleted_term );
		$this->options->set( 'just_deleted_term_permalink', \get_term_link( $deleted_term, $taxonomy ) );
	}

	/**
	 * Retrieve a just deleted term.
	 *
	 * @return void
	 */
	public function get_just_deleted_term() {
		\check_ajax_referer( 'fewer_tags_just_deleted_term' );

		if ( ! \current_user_can( 'manage_categories' ) ) {
			\wp_send_json_error( [ 'msg' => __( 'You do not have permission to do this.', 'fewer-tags' ) ] );
		}

		$just_deleted_term = $this->options->get( 'just_deleted_term' );
		$msg               = Helper::redirect_term_notice( $just_deleted_term->slug, $just_deleted_term->name, $just_deleted_term->taxonomy );

		\wp_send_json_success( $msg );
	}

	/**
	 * Dismiss a notice.
	 *
	 * @return void
	 */
	public function dismiss_notice() {
		\check_ajax_referer( 'fewer_tags_dismiss_notice' );

		if ( ! \current_user_can( 'manage_categories' ) ) {
			\wp_send_json_error( [ 'msg' => __( 'You do not have permission to do this.', 'fewer-tags' ) ] );
		}

		if ( ! isset( $_POST['id'] ) || ! isset( $_POST['taxonomy'] ) ) {
			\wp_send_json_error( [ 'msg' => __( 'Invalid data.', 'fewer-tags' ) ] );
		}

		$slug     = \str_replace( 'fewer-tags-dismiss-', '', trim( \wp_strip_all_tags( \wp_unslash( $_POST['id'] ) ) ) );
		$taxonomy = trim( \wp_strip_all_tags( \wp_unslash( $_POST['taxonomy'] ) ) );

		$this->remove_term_from_terms_to_redirect( $slug, $taxonomy );

		\wp_send_json_success( $slug );
	}

	/**
	 * Create a redirect.
	 *
	 * @return void
	 */
	public function redirect_url_action() {
		\check_ajax_referer( 'fewer_tags_redirect_url' );

		if ( ! \current_user_can( 'manage_categories' ) ) {
			\wp_send_json_error( [ 'msg' => __( 'You do not have permission to do this.', 'fewer-tags' ) ] );
		}

		if ( ! isset( $_POST['slug'] ) || ! isset( $_POST['target'] ) || ! isset( $_POST['taxonomy'] ) ) {
			\wp_send_json_error(
				[
					'msg' => __( 'Invalid data.', 'fewer-tags' ),
				]
			);
		}
		$slug     = trim( \wp_strip_all_tags( \wp_unslash( $_POST['slug'] ) ) );
		$taxonomy = trim( \wp_strip_all_tags( \wp_unslash( $_POST['taxonomy'] ) ) );
		$target   = trim( \wp_strip_all_tags( \wp_unslash( $_POST['target'] ) ) );

		$terms_to_redirect = $this->options->get( 'terms_to_redirect' );
		$term              = $terms_to_redirect[ $taxonomy ][ $slug ]['object'];
		$term_url          = $terms_to_redirect[ $taxonomy ][ $slug ]['permalink'];

		$redirects = new Redirects();
		$redirects->create_redirect_from_slug( $slug, $taxonomy, $target );

		\wp_send_json_success(
			[
				'slug' => $term->slug,
				// translators: %1$s is the just redirected term name.
				'msg'  => sprintf( __( 'Redirect for %1$s created!', 'fewer-tags' ), '<a href="' . \esc_url( $term_url ) . '">' . \esc_html( $term->name ) . '</a>' ),
			]
		);
	}

	/**
	 * Merges two tags, source and target, into target, and redirects the source tag to the target tag.
	 *
	 * @return void
	 */
	public function merge_terms() {
		\check_ajax_referer( 'fewer_tags_merge_terms' );

		if ( ! \current_user_can( 'manage_categories' ) ) {
			\wp_send_json_error( [ 'msg' => __( 'You do not have permission to do this.', 'fewer-tags' ) ] );
		}

		if ( ! isset( $_POST['source_id'] ) || ! isset( $_POST['target_id'] ) || ! isset( $_POST['source_taxonomy'] ) || ! isset( $_POST['target_taxonomy'] ) ) {
			\wp_send_json_error( [ 'msg' => __( 'Invalid data.', 'fewer-tags' ) ] );
		}
		$source_term_id  = intval( $_POST['source_id'] );
		$target_term_id  = intval( $_POST['target_id'] );
		$source_taxonomy = \wp_strip_all_tags( \wp_unslash( $_POST['source_taxonomy'] ) );
		$target_taxonomy = \wp_strip_all_tags( \wp_unslash( $_POST['target_taxonomy'] ) );

		$source_term = \get_term( $source_term_id, $source_taxonomy );
		$source_url  = $this->make_relative_url( \get_term_link( $source_term_id, $source_taxonomy ) );
		$target_url  = $this->make_relative_url( \get_term_link( $target_term_id, $target_taxonomy ) );

		// Get all posts with the source tag and add the target tag to them.
		$posts = \get_objects_in_term( $source_term_id, $source_taxonomy );
		foreach ( $posts as $post_id ) {
			\wp_set_post_terms( (int) $post_id, [ $target_term_id ], $target_taxonomy, true );
			if ( $source_taxonomy === 'category' && $source_term_id === 1 ) {
				\wp_remove_object_terms( (int) $post_id, 1, 'category' );
			}
		}
		// Remove our term deletion functionality, as now we have merged the tags and thus know where to redirect.
		\remove_action( 'delete_term', [ $this, 'redirect_term_on_deletion' ], 10 );
		\remove_action( 'pre_delete_term', [ $this, 'pre_delete_term' ], 10 );

		\wp_delete_term( $source_term_id, $source_taxonomy );

		$redirects = new Redirects();
		$redirects->create_redirect( $source_url, $target_url );

		// We're grabbing this after we've moved the new posts into the term, so we get the correct count.
		$target_term = \get_term( $target_term_id, $target_taxonomy );

		// translators: %1$s is the source tag name, %2$s is the target tag name.
		$msg = sprintf( \esc_html__( '%1$s merged into %2$s!', 'fewer-tags' ), '<strong>' . \esc_html( $source_term->name ) . '</strong>', '<strong>' . \esc_html( $target_term->name ) . '</strong>' );
		if ( $source_taxonomy !== $target_taxonomy ) {
			// translators: %1$s is the source tag name, %2$s is the target tag name, %3$s is the source taxonomy, %4$s is the target taxonomy.
			$msg = sprintf( \esc_html__( '%1$s (%3$s) merged into %2$s (%4$s)!', 'fewer-tags' ), '<strong>' . \esc_html( $source_term->name ) . '</strong>', '<strong>' . \esc_html( $target_term->name ) . '</strong>', \esc_html( $source_taxonomy ), \esc_html( $target_taxonomy ) );
		}
		\wp_send_json_success(
			[
				'source_id'    => $source_term_id,
				'target_id'    => $target_term_id,
				'target_count' => $target_term->count,
				'msg'          => $msg,
			]
		);
	}

	/**
	 * Removes a tag from the "tags to redirect" option.
	 *
	 * @param string $slug     The slug of the tag to remove.
	 * @param string $taxonomy The taxonomy of the tag to remove.
	 *
	 * @return void
	 */
	private function remove_term_from_terms_to_redirect( $slug, $taxonomy ) {
		$terms_to_redirect = $this->options->get( 'terms_to_redirect' );
		unset( $terms_to_redirect[ $taxonomy ][ $slug ] );
		$this->options->set( 'terms_to_redirect', $terms_to_redirect );
	}

	/**
	 * Take an absolute URL and make it relative.
	 *
	 * @param string $url The URL to make relative.
	 *
	 * @return string The relative URL.
	 */
	private function make_relative_url( $url ) {
		if ( strpos( $url, 'http' ) === 0 ) {
			$url_parts = \wp_parse_url( $url );
			if ( is_array( $url_parts ) && isset( $url_parts['path'] ) ) {
				return $url_parts['path'];
			}
		}
		return $url;
	}
}
