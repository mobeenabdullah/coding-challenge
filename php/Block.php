<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$class_name = sanitize_html_class( esc_attr( $attributes['className'] )) ?? '';
		$post_id = get_the_ID();
		ob_start(); ?>

        <div class="<?php echo esc_attr($class_name); ?>">
			<h2>Post Counts</h2>
			<ul>
				<?php foreach ( $post_types as $post_type_object ) : ?>
				<?php
					$post_type_slug = $post_type_object->name;
					$post_count = wp_count_posts( $post_type_slug )->publish;
				?>
				<li><?php _e('There are ' . $post_count . ' ' . $post_type_object->labels->name . '.', 'site-counts'); ?></li>
				<?php endforeach; ?>
			</ul>
			<p><?php _e('The current post ID is ' . $post_id . '.', 'site-counts'); ?></p>

			<?php
			$tag_slug = 'foo';
			$cat_slug = 'baz';
			$query_args = [
				'post_type' => [ 'post', 'page' ],
				'post_status' => 'any',
				'date_query' => [
					[
						'hour' => 9,
						'compare' => '>=',
					],
					[
						'hour' => 17,
						'compare' => '<=',
					],
				],
				'tag' => $tag_slug,
				'category_name' => $cat_slug,
				'post__not_in' => [ $post_id ],
				'posts_per_page' => 5,
			];
			$query = new WP_Query($query_args); ?>

			<?php if ( $query->found_posts ) : ?>
				<h2><?php _e( $query->post_count . ' posts with the tag of foo and the category of baz', 'site-counts'); ?></h2>
                <ul>
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <li><?php echo get_the_title() ?></li>
				<?php endwhile; ?>
				</ul>
			<?php endif; ?>
			<?php wp_reset_query(); ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
