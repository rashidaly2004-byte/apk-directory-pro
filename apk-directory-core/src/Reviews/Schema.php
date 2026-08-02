<?php
/**
 * Review comment type registration.
 *
 * @package Adp\Core\Reviews
 */

namespace Adp\Core\Reviews;

defined( 'ABSPATH' ) || exit;

/**
 * Registers adp_review comment type.
 */
class Schema {

	public const COMMENT_TYPE = 'adp_review';
	public const RATING_META  = 'adp_rating';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_comment_type' ) );
		add_filter( 'wp_list_comments_args', array( self::class, 'filter_comments_args' ) );
		add_filter( 'comment_class', array( self::class, 'add_comment_class' ), 10, 5 );
	}

	/**
	 * Register comment type support.
	 *
	 * @return void
	 */
	public static function register_comment_type(): void {
		register_meta(
			'comment',
			self::RATING_META,
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => static function ( mixed $value ): int {
					return max( 1, min( 5, (int) $value ) );
				},
				'show_in_rest'      => true,
				'auth_callback'     => static fn(): bool => current_user_can( 'moderate_comments' ),
			)
		);
	}

	/**
	 * Filter comment list to show reviews on app posts.
	 *
	 * @param array<string, mixed> $args Comment args.
	 * @return array<string, mixed>
	 */
	public static function filter_comments_args( array $args ): array {
		if ( is_singular( 'adp_app' ) ) {
			$args['type'] = self::COMMENT_TYPE;
		}
		return $args;
	}

	/**
	 * Add CSS class for review comments.
	 *
	 * @param array<int, string> $classes    CSS classes.
	 * @param string             $class     Additional class.
	 * @param int                $comment_id Comment ID.
	 * @param \WP_Comment|null   $comment   Comment object.
	 * @param int                $post_id   Post ID.
	 * @return array<int, string>
	 */
	public static function add_comment_class( array $classes, string $class, int $comment_id, ?\WP_Comment $comment, int $post_id ): array {
		unset( $class, $comment_id, $post_id );
		if ( $comment && self::COMMENT_TYPE === $comment->comment_type ) {
			$classes[] = 'adp-review';
		}
		return $classes;
	}

	/**
	 * Get average rating for an app.
	 *
	 * @param int $post_id App post ID.
	 * @return array{count: int, average: float}
	 */
	public static function get_aggregate( int $post_id ): array {
		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'type'    => self::COMMENT_TYPE,
				'status'  => 'approve',
				'number'  => 0,
			)
		);

		if ( empty( $comments ) ) {
			return array(
				'count'   => 0,
				'average' => 0.0,
			);
		}

		$total = 0;
		foreach ( $comments as $comment ) {
			$total += (int) get_comment_meta( $comment->comment_ID, self::RATING_META, true );
		}

		return array(
			'count'   => count( $comments ),
			'average' => round( $total / count( $comments ), 1 ),
		);
	}
}
