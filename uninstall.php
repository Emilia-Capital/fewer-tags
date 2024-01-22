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

$fewer_tags_option_name = 'joost_min_posts_count';

delete_option( $fewer_tags_option_name );

// For site options in Multisite.
delete_site_option( $fewer_tags_option_name );
