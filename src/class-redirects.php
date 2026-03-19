<?php
/**
 * Redirect creation class supporting Redirection plugin and Yoast SEO Premium.
 *
 * @package FewerTags
 */

namespace FewerTags;

/**
 * FewerTags Redirects Class
 */
class Redirects {
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
	}

	/**
	 * Create a redirect from a slug and a target URL.
	 *
	 * @param string $slug       The slug of the term we're redirecting.
	 * @param string $taxonomy   The taxonomy of the term we're redirecting.
	 * @param string $target_url The target URL.
	 *
	 * @return boolean Whether the redirect was created successfully.
	 */
	public function create_redirect_from_slug( $slug, $taxonomy, $target_url ) {
		$terms_to_redirect = $this->options->get( 'terms_to_redirect' );
		if ( ! isset( $terms_to_redirect[ $taxonomy ][ $slug ] ) ) {
			return false;
		}

		$response = $this->create_redirect( $terms_to_redirect[ $taxonomy ][ $slug ]['permalink'], $target_url );
		if ( ! $response ) {
			return false;
		}

		unset( $terms_to_redirect[ $taxonomy ][ $slug ] );
		$this->options->set( 'terms_to_redirect', $terms_to_redirect );
		return true;
	}

	/**
	 * Creates a Redirection 'Fewer Tags' group.
	 *
	 * @return object|false The group object or false on failure.
	 */
	private function create_redirection_group() {
		if ( ! class_exists( '\Red_Group' ) ) {
			return false;
		}

		return \Red_Group::create(
			'Fewer Tags',
			1, // Means they're WordPress based redirects, the default.
		);
	}

	/**
	 * Create a redirect.
	 *
	 * @param string $source_url Source URL.
	 * @param string $target_url Target URL.
	 *
	 * @return boolean Whether the redirect was created successfully.
	 */
	public function create_redirect( $source_url, $target_url ) {
		$tool = Helper::determine_redirect_tool();
		if ( ! $tool ) {
			return false;
		}

		$response = false;
		switch ( $tool ) {
			case 'redirection':
				$response = $this->create_redirection_redirect( $source_url, $target_url );
				break;
			case 'yoast':
				$response = $this->create_yoast_redirect( $source_url, $target_url );
				break;
		}
		return $response;
	}

	/**
	 * Create a Redirection redirect.
	 *
	 * @param string $source_url The slug of the tag to redirect.
	 * @param string $target_url The target URL to redirect to.
	 *
	 * @return bool Whether the redirect was created successfully.
	 */
	public function create_redirection_redirect( $source_url, $target_url ) {
		$redirection_group = $this->options->get( 'redirect_group' );
		if ( ! empty( $redirection_group ) || ! is_int( $redirection_group ) ) {
			$response = $this->create_redirection_group();
			if ( false !== $response ) {
				$this->options->set( 'redirection_group', $response->get_id() );
			}
		}

		$redirect = \Red_Item::create(
			[
				'url'         => $source_url,
				'action_code' => 301,
				'action_data' => [ 'url' => $target_url ],
				'action_type' => 'url',
				'match_type'  => 'url',
				'group_id'    => $this->options->get( 'redirection_group' ),
			]
		);
		if ( ! \is_wp_error( $redirect ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Create a Yoast redirect.
	 *
	 * @param string $source_url The slug of the tag to redirect.
	 * @param string $target_url The target URL to redirect to.
	 *
	 * @return boolean Whether the redirect was created successfully.
	 */
	public function create_yoast_redirect( $source_url, $target_url ) {
		$redirect_option = new \WPSEO_Redirect_Option();

		// Add the redirect to Yoast SEO table.
		$redirect_object = new \WPSEO_Redirect( $source_url, $target_url, 301, 'plain' );
		$redirect_option->add( $redirect_object );
		$redirect_option->save();

		// Apply the redirect.
		$manager = new \WPSEO_Redirect_Manager();
		$manager->export_redirects();

		return true;
	}
}
