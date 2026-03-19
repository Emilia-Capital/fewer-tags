<?php
/**
 * Plugin that redirects tag pages to the home page if they contain fewer than a specified number of posts.
 *
 * @package FewerTags
 * @version 2.0
 *
 * Plugin Name:       Fewer Tags
 * Plugin URI:        https://fewertags.com/
 * Description:       Manage your site's tags: set minimum post counts, merge terms, and create redirects. Change settings under Settings → Reading. Learn more at fewertags.com.
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Version:           2.0
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
define( 'FEWER_TAGS_FILE', __FILE__ );
require_once __DIR__ . '/src/autoload.php';

// Instantiate the plugin class.
$fewer_tags = new FewerTags\Plugin();
$fewer_tags->register_hooks();
