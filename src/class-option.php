<?php
/**
 * Options singleton class.
 *
 * @package FewerTags
 */

namespace FewerTags;

/**
 * Option Class
 */
class Option {
	/**
	 * Name of the option value in the database.
	 *
	 * @var string
	 */
	private $option_name = 'fewer_tags_pro';

	/**
	 * Holds the plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Get the instance of this class.
	 */
	public static function get_instance(): Option {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new Option();
		}
		return $instance;
	}

	/**
	 * Constructor method
	 */
	public function __construct() {
		$this->options = \get_option( $this->option_name, [] );
	}

	/**
	 * Option getter.
	 *
	 * @param string $key The option key to get.
	 *
	 * @return mixed|null
	 */
	public function get( $key ) {
		if ( isset( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
		}
		return null;
	}

	/**
	 * Option setter.
	 *
	 * @param string $key   The option key to set.
	 * @param mixed  $value The value to set.
	 *
	 * @return void
	 */
	public function set( $key, $value ): void {
		$this->options[ $key ] = $value;
		\update_option( $this->option_name, $this->options );
	}
}
