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

$ft_option_name = 'joost_min_posts_count';

delete_option( $ft_option_name );

// For site options in Multisite.
delete_site_option( $ft_option_name );
