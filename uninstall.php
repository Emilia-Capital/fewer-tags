<?php
/**
 * Plugin uninstall routines.
 *
 * @package FewerTags
 */

// If uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Backwards-compatibility for older versions of the plugin.
delete_option( 'joost_min_posts_count' );

// Delete the option.
delete_option( 'fewer_tags' );
