<?php
/**
 * The main plugin class.
 *
 * @package FewerTags
 */

namespace FewerTags;

/**
 * FewerTags Class
 */
class Plugin {

	/**
	 * The option name.
	 *
	 * @var string
	 */
	public static $option_name = 'fewer_tags';

	/**
	 * Default value for the minimum number of posts a tag should have to not be redirected to the homepage.
	 *
	 * @var int
	 */
	public static $min_posts_count;

	/**
	 * The option class.
	 *
	 * @var Option
	 */
	public $options;

	/**
	 * Register plugin hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'plugins_loaded', [ $this, 'migrate_option' ] );
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin and register hooks.
	 *
	 * @return void
	 */
	public function init() {
		self::$min_posts_count = (int) get_option( static::$option_name, 10 );

		if ( is_admin() ) {
			if ( \wp_doing_ajax() ) {
				new Admin_Ajax();
				return;
			}

			$admin = new Admin();
			$admin->register_hooks();

			$this->options = Option::get_instance();

			\add_action( 'admin_notices', [ $this, 'do_notices' ] );
			\add_action( 'admin_footer', [ $this, 'output_modal' ] );
			\add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			\add_filter( 'tag_row_actions', [ $this, 'add_merge_action' ], 10, 2 );
			\add_filter( 'category_row_actions', [ $this, 'add_merge_action' ], 10, 2 );

			// Add merge action for custom taxonomies.
			$taxonomies = \get_taxonomies(
				[
					'public'   => true,
					'_builtin' => false,
				],
				'names'
			);
			foreach ( $taxonomies as $taxonomy ) {
				\add_filter( "{$taxonomy}_row_actions", [ $this, 'add_merge_action' ], 10, 2 );
			}

			// Detect if we're running on the playground, if so, load our playground specific class.
			if ( defined( 'IS_PLAYGROUND_PREVIEW' ) && IS_PLAYGROUND_PREVIEW ) {
				$playground = new Playground();
				$playground->register_hooks();
			}

			return;
		}
		$frontend = new Frontend();
		$frontend->register_hooks();
	}

	/**
	 * Migrate the old option to the new one.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function migrate_option() {
		$old_option = get_option( 'joost_min_posts_count' );
		if ( $old_option ) {
			update_option( static::$option_name, $old_option );
			delete_option( 'joost_min_posts_count' );
		}
	}

	/**
	 * Adds a 'Merge' action to the term actions list.
	 *
	 * @param array    $actions An array of actions to be performed on the term.
	 * @param \WP_Term $term    The term object.
	 *
	 * @return array The modified actions array.
	 */
	public function add_merge_action( $actions, $term ) {
		$taxonomy = \get_taxonomy( $term->taxonomy );

		// Add a 'Merge' action link.
		$actions['merge'] = \sprintf(
			'<a class="thickbox fewer-tags-merge-action" data-term-id="%1$d" data-term-name="%2$s" data-term-taxonomy="%3$s" title="%4$s" href="#TB_inline?width=600&height=360&inlineId=merge-tags-modal">%5$s</a>',
			$term->term_id,
			$term->name,
			$term->taxonomy,
			// translators: %s is the name of the tag.
			sprintf( __( 'Merge %1$s', 'fewer-tags' ), strtolower( $taxonomy->labels->name ) ),
			__( 'Merge', 'fewer-tags' )
		);

		return $actions;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_terms_screen() ) {
			return;
		}
		\add_thickbox();
		\wp_enqueue_style( 'fewer-tags-choices', \plugins_url( 'js/vendor/choices.min.css', FEWER_TAGS_FILE ), [], '10.2.0' );
		\wp_add_inline_style(
			'fewer-tags-choices',
			'
			#merge-tags-modal {
				display: none;
			}
			#fewer-tags-merge-form br, #fewer-tags-merge-form p {
				clear: both;
			}
			#fewer-tags-note {
				display: none;
				border: 1px solid #c3c4c7;
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
				border-left: 4px solid #d63638;
				margin: 5px 0 15px 0;
				padding: 0px 8px;
				background-color: #fff;
			}
			#fewer-tags-note p {
				padding: 0;
			}
			#fewer-tags-merge-form label {
				display: block;
				float: left;
				width: 150px;
				margin-bottom: 35px;
			}
			#fewer-tags-merge-form label.choices_label {
				line-height: 40px;
				margin-bottom: 35px;
			}
			#fewer-tags-source-term-name {
				float: left;
				margin-bottom: 5px;
				width: 375px;
			}
			#TB_ajaxContent {
				overflow: visible !important;
			}
			div.choices {
				display: inline-block !important;
				width: 375px !important;
				box-sizing: border-box !important;
				margin-bottom: 10px !important;
			}
			.choices__inner {
				background-color: inherit !important;
				min-height: 40px !important;
				box-sizing: border-box !important;
				border-radius: 5px !important;
			}
			.choices__list--dropdown .choices__item--selectable, .choices__list[aria-expanded] .choices__item--selectable {
				padding-right: 10px !important;
			}
			.choices__list--dropdown .choices__item--selectable::after, .choices__list[aria-expanded] .choices__item--selectable::after {
				display: none !important;
			}
			.choices__item--selectable span {
				display: inline-block !important;
				float: right;
				color: #666;
			}'
		);
		\wp_enqueue_script( 'fewer-tags-choices', \plugins_url( 'js/vendor/choices.min.js', FEWER_TAGS_FILE ), [], '10.2.0', true );
		\wp_enqueue_script( 'fewer-tags', \plugins_url( 'js/fewer-tags.js', FEWER_TAGS_FILE ), [ 'fewer-tags-choices', 'wp-api' ], '2.0', true );
		\wp_add_inline_script(
			'fewer-tags',
			'const fewerTags = ' . \wp_json_encode(
				[
					'ajaxUrl'         => \admin_url( 'admin-ajax.php' ),
					'deleteTermNonce' => \wp_create_nonce( 'fewer_tags_just_deleted_term' ),
					'dismissText'     => __( 'Dismiss this notice.', 'fewer-tags' ),
					'restAPInonce'    => \wp_create_nonce( 'wp_rest' ),
				]
			),
			'after'
		);
	}

	/**
	 * Outputs the modal.
	 *
	 * @return void
	 */
	public function output_modal() {
		if ( ! $this->is_terms_screen() ) {
			return;
		}
		$screen   = \get_current_screen();
		$taxonomy = \get_taxonomy( $screen->taxonomy );

		?>
		<div id="merge-tags-modal">
			<form id="fewer-tags-merge-form">
				<?php // translators: %1$s is the taxonomy of the terms we're merging. ?>
				<h3><?php printf( \esc_html__( 'Merge %1$s', 'fewer-tags' ), \esc_html( strtolower( $taxonomy->labels->name ) ) ); ?></h3>
				<div id="fewer-tags-note"><p><?php \esc_html_e( 'If you merge the Uncategorized category into another, we will add the posts to the other category and remove them from Uncategorized. Unfortunately, the Uncategorized category cannot be deleted.', 'fewer-tags' ); ?></p></div>
				<input type="hidden" name="nonce" id="fewer-tags-merge-terms-nonce" value="<?php echo \esc_attr( \wp_create_nonce( 'fewer_tags_merge_terms' ) ); ?>" />
				<input type="hidden" name="source_id" id="fewer-tags-source-term-id" value="" />
				<input type="hidden" name="source_taxonomy" id="fewer-tags-taxonomy" value="<?php echo \esc_attr( $screen->taxonomy ); ?>" />
				<?php // translators: %1$s is the taxonomy of the source term. ?>
				<label for="fewer-tags-source-term-name"><?php printf( \esc_html__( 'Source %1$s:', 'fewer-tags' ), \esc_html( strtolower( $taxonomy->labels->singular_name ) ) ); ?></label>
				<input type="text" disabled="disabled" id="fewer-tags-source-term-name" value="" />
				<br>
				<?php // translators: %1$s is the taxonomy of the target term. ?>
				<label class="choices_label" id="fewer-tags-target-taxonomy-label" for="fewer-tags-target-taxonomy-slug"><?php \esc_html_e( 'Target taxonomy:', 'fewer-tags' ); ?></label>
				<select id="fewer-tags-target-taxonomy-slug" name="target_taxonomy" data-current-taxonomy="<?php echo \esc_attr( $screen->taxonomy ); ?>"></select>
				<br>
				<?php // translators: %1$s is the taxonomy of the target term. ?>
				<label class="choices_label" id="fewer-tags-target-term-label" for="fewer-tags-target-term-id"><?php printf( \esc_html__( 'Target %1$s:', 'fewer-tags' ), \esc_html( strtolower( $taxonomy->labels->singular_name ) ) ); ?></label>
				<select id="fewer-tags-target-term-id" name="target_id" data-taxonomy="<?php echo \esc_attr( $screen->taxonomy ); ?>"></select>
				<?php // translators: %1$s is the taxonomy of the term. ?>
				<p><?php printf( \esc_html__( 'The posts that have the source %1$s will have the target term added to them, and the %1$s archive for your source %1$s will redirect to your targeted term.', 'fewer-tags' ), \esc_html( strtolower( $taxonomy->labels->singular_name ) ) ); ?></p>
				<?php // translators: %1$s is the taxonomy of the term. ?>
				<input type="submit" class="button-primary" value="<?php printf( \esc_attr__( 'Merge %1$s', 'fewer-tags' ), \esc_html( strtolower( $taxonomy->labels->name ) ) ); ?>" />
			</form>
		</div>
		<?php
	}

	/**
	 * Checks if we're on the tags screen.
	 *
	 * @return bool
	 */
	private function is_terms_screen() {
		$screen = \get_current_screen();

		if ( ! \is_object( $screen ) ) {
			return false;
		}

		return ( $screen->base === 'edit-tags' );
	}

	/**
	 * Displays a notice to redirect a tag when it's deleted.
	 *
	 * @return void
	 */
	public function do_notices() {
		if ( ! $this->is_terms_screen() ) {
			return;
		}

		$taxonomy          = \get_current_screen()->taxonomy;
		$terms_to_redirect = $this->options->get( 'terms_to_redirect' );
		if ( ! isset( $terms_to_redirect[ $taxonomy ] ) ) {
			return;
		}
		$terms_to_redirect = $terms_to_redirect[ $taxonomy ];

		if ( ! empty( $terms_to_redirect ) && count( $terms_to_redirect ) > 0 ) {
			foreach ( $terms_to_redirect as $slug => $term_array ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output escaped in function.
				echo Helper::redirect_term_notice( $slug, $term_array['object']->name, $taxonomy );
			}
		}
	}
}
