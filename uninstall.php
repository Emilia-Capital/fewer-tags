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

$option_name = 'joost_min_posts_count';

delete_option( $option_name );

// For site options in Multisite.
delete_site_option( $option_name );
