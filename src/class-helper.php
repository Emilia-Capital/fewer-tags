<?php
/**
 * Helper class with utility methods.
 *
 * @package FewerTags
 */

namespace FewerTags;

/**
 * FewerTags Helper Class
 */
class Helper {

	/**
	 * Get a redirect notice.
	 *
	 * @param string $slug     The slug of the term to redirect.
	 * @param string $name     The name of the term to redirect.
	 * @param string $taxonomy The taxonomy of the term to redirect.
	 *
	 * @return string The redirect notice HTML.
	 */
	public static function redirect_term_notice( $slug, $name, $taxonomy ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Slugs are never user input.
		$notice  = '<div id="fewer-tags-redirect-' . $slug . '" class="notice notice-error is-dismissible fewer-tags-redirect-notice" data-slug="' . $slug . '">';
		$notice .= '<p>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped in function.
		$notice .= self::get_redirect_message( $slug, $taxonomy );
		$notice .= '</p>';
		$notice .= '<button type="button" data-nonce="' . \wp_create_nonce( 'fewer_tags_dismiss_notice' ) . '" data-id="fewer-tags-dismiss-' . $slug . '" data-taxonomy="' . $taxonomy . '" class="notice-dismiss"><span class="screen-reader-text">' . \esc_html__( 'Dismiss this notice.', 'fewer-tags' ) . '</span></button>';
		$notice .= '</div>';

		return $notice;
	}

	/**
	 * Determines which redirect tool to use.
	 *
	 * @return string|false The redirect tool to use.
	 */
	public static function determine_redirect_tool() {
		if ( \defined( 'REDIRECTION_DB_VERSION' ) ) {
			return 'redirection';
		}
		if ( \defined( 'WPSEO_PREMIUM_VERSION' ) ) {
			return 'yoast';
		}
		return false;
	}

	/**
	 * Gets the message to redirect a tag when it's deleted.
	 *
	 * @param string $slug     The slug of the tag to redirect.
	 * @param string $taxonomy The taxonomy of the term to redirect.
	 *
	 * @return string The message to redirect a tag when it's deleted.
	 */
	public static function get_redirect_message( $slug, $taxonomy ) {
		$options = Option::get_instance();

		$terms_to_redirect = $options->get( 'terms_to_redirect' );

		$term     = $terms_to_redirect[ $taxonomy ][ $slug ]['object'];
		$term_url = $terms_to_redirect[ $taxonomy ][ $slug ]['permalink'];

		$labels = \get_taxonomy_labels( \get_taxonomy( $taxonomy ) );

		$msg = '<strong>' . \esc_html__( 'Fewer Tags notice', 'fewer-tags' ) . '</strong><br/>';
		// translators: %1$s is the tag name, %2$s is the tag slug.
		$msg .= sprintf( \esc_html__( 'You\'ve deleted the %2$s "%1$s", let\'s redirect it?', 'fewer-tags' ), '<a href="' . $term_url . '">' . \esc_html( $term->name ) . '</a>', strtolower( $labels->singular_name ) );

		$tool = self::determine_redirect_tool();
		if ( $tool === 'redirection' ) {
			$msg .= ' ' . \esc_html__( 'We can use your Redirection plugin to do that.', 'fewer-tags' );
		}
		if ( $tool === 'yoast' ) {
			$msg .= ' ' . \esc_html__( 'We can use your Yoast SEO Premium plugin to do that.', 'fewer-tags' );
		}

		$msg .= '<ul>';
		$msg .= '<li style="list-style-type: disc; margin-left: 15px;"><a href="javascript:fewerTagsRedirectToUrl(\'' . $slug . '\',\'' . $taxonomy . '\',\'/\',\'' . \wp_create_nonce( 'fewer_tags_redirect_url' ) . '\')">' . \esc_html__( 'Redirect to homepage', 'fewer-tags' ) . '</a></li>';
		$msg .= '<li style="list-style-type: disc; margin-left: 15px;"><a href="javascript:fewerTagsRedirectToUrl(\'' . $slug . '\',\'' . $taxonomy . '\',prompt(\'' . \esc_html__( 'Where should the page redirect to?', 'fewer-tags' ) . '\'),\'' . \wp_create_nonce( 'fewer_tags_redirect_url' ) . '\')">' . \esc_html__( 'Redirect to another URL', 'fewer-tags' ) . '</a></li>';
		$msg .= '</ul>';

		return $msg;
	}
}
