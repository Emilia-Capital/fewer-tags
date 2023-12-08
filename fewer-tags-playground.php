<?php
/**
 * File that creates a better playground environment for the Fewer Tags plugin.
 *
 * @package Fewer_Tags_Playground
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class FewerTags_Playground
 */
class FewerTags_Playground {
	/**
	 * Class instance.
	 *
	 * @var FewerTags_Playground
	 */
	private static $instance = false;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Print an admin notice helping people.
	 */
	public function admin_notices() {
		$screen = \get_current_screen();
		if ( ! \is_object( $screen ) || $screen->base !== 'edit-tags' ) {
			return;
		}

		echo '<div id="fewer-tags-playground-notice" class="notice notice-success">';
		echo '<p><strong>' . esc_html__( 'Fewer Tags demo', 'fewer-tags' ) . '</strong><br>';
		esc_html_e( 'This is a demo of the Fewer Tags plugin. As you can see if you hover over them, tags that don\'t hit the required count, do not have a view action. That is because they are not live on the site.', 'fewer-tags' );
		echo '</p>';
		echo '<p>';
		esc_html_e( 'You can go to Settings → Reading to change how many tags a tag needs to have to be live on the site.', 'fewer-tags' );
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Get the instance of the class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new FewerTags_Playground();
		}

		return self::$instance;
	}

	/**
	 * Generate random posts.
	 */
	public function generate_posts() {
		$common_tag  = $this->create_tag( 'Common Tag' );
		$special_tag = $this->create_tag( 'Special Tag' );

		$post_ids = [];
		for ( $i = 0; $i < 20; $i++ ) {
			$post_ids[] = $this->create_random_post( $common_tag );
		}

		// Attach 'Special Tag' to 5 random posts.
		$selected_posts = array_rand( $post_ids, 5 );
		foreach ( $selected_posts as $post_id ) {
			wp_set_object_terms( $post_ids[ $post_id ], $special_tag, 'post_tag', true );
		}
	}

	/**
	 * Create a random post.
	 *
	 * @param string $common_tag Tag to attach to all posts.
	 * @return int Post ID.
	 */
	private function create_random_post( $common_tag ) {
		$postarr = [
			'post_title'   => $this->create_random_string( 5 ),
			'post_content' => $this->create_random_string( 10 ),
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'tags_input'   => [ $common_tag ],
		];

		return wp_insert_post( $postarr );
	}

	/**
	 * Create a tag.
	 *
	 * @param string $tag_name Name of the tag.
	 *
	 * @return string Tag name.
	 */
	private function create_tag( $tag_name ) {
		if ( ! term_exists( $tag_name, 'post_tag' ) ) {
			$term = wp_insert_term( $tag_name, 'post_tag' );
			return $term['term_id'];
		}
	}

	/**
	 * Create a random string of content.
	 *
	 * @param int $length Length of the string to create.
	 *
	 * @return string Random string.
	 */
	private function create_random_string( $length ) {
		$words     = [ 'the', 'and', 'have', 'that', 'for', 'you', 'with', 'say', 'this', 'they', 'but', 'his', 'from', 'not', 'she', 'as', 'what', 'their', 'can', 'who', 'get', 'would', 'her', 'all', 'make', 'about', 'know', 'will', 'one', 'time', 'there', 'year', 'think', 'when', 'which', 'them', 'some', 'people', 'take', 'out', 'into', 'just', 'see', 'him', 'your', 'come', 'could', 'now', 'than', 'like', 'other', 'how', 'then', 'its', 'our', 'two', 'more', 'these', 'want', 'way', 'look', 'first', 'also', 'new', 'because', 'day', 'use', 'man', 'find', 'here', 'thing', 'give', 'many', 'well', 'only', 'those', 'tell', 'very', 'even', 'back', 'any', 'good', 'woman', 'through', 'life', 'child', 'work', 'down', 'may', 'after', 'should', 'call', 'world', 'over', 'school', 'still', 'try', 'last', 'ask', 'need' ];
		$word_keys = array_rand( $words, $length );
		$sentence  = '';
		foreach ( $word_keys as $key ) {
			$sentence .= $words[ $key ] . ' ';
		}
		return ucfirst( trim( $sentence ) ) . '.';
	}
}

// Usage.
$fewertags_playground = new FewerTags_Playground();