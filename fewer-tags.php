<?php
/**
 * Plugin that redirects tag pages to the home page if they contain fewer than a specified number of posts.
 *
 * @package FewerTags
 * @version 1.5.1
 *
 * Plugin Name:       Fewer Tags
 * Plugin URI:        https://fewertags.com/
 * Description:       Redirects tag pages to the home page if they contain fewer than a specified number of posts, defaults to 10. Change under Settings → Reading. Learn more about this plugin at fewertags.com.
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Version:           1.5.1
 * Author:            Joost de Valk
 * Author URI:        https://joost.blog/
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       fewer-tags
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'FEWER_TAGS_DIR', __DIR__ );
require_once __DIR__ . '/src/autoload.php';

// Instantiate the plugin class.
$fewer_tags = new FewerTags\Plugin();
$fewer_tags->register_hooks();
