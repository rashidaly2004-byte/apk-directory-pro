<?php
/**
 * Pagination navigation.
 *
 * @package AdpTheme
 *
 * @var array $args Template args.
 */

$total   = isset( $args['total'] ) ? (int) $args['total'] : 0;
$current = isset( $args['current'] ) ? (int) $args['current'] : 0;
$base    = $args['base'] ?? '';
$format  = $args['format'] ?? '';

if ( $total > 1 && $base ) {
	$pagination = paginate_links(
		array(
			'type'      => 'array',
			'total'     => $total,
			'current'   => $current,
			'base'      => $base,
			'format'    => $format,
			'prev_text' => '&larr; ' . __( 'Previous', 'apk-directory-pro' ),
			'next_text' => __( 'Next', 'apk-directory-pro' ) . ' &rarr;',
		)
	);
} else {
	global $wp_query;

	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

	$pagination = paginate_links(
		array(
			'type'      => 'array',
			'prev_text' => '&larr; ' . __( 'Previous', 'apk-directory-pro' ),
			'next_text' => __( 'Next', 'apk-directory-pro' ) . ' &rarr;',
		)
	);
}

if ( ! $pagination ) {
	return;
}
?>
<nav class="adp-pagination adp-container" aria-label="<?php esc_attr_e( 'Pagination', 'apk-directory-pro' ); ?>">
	<ul class="adp-pagination__list">
		<?php foreach ( $pagination as $link ) : ?>
			<li class="adp-pagination__item"><?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
		<?php endforeach; ?>
	</ul>
</nav>
