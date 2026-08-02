<?php
$content = get_post_field( 'post_content', get_the_ID() );
if ( ! preg_match_all( '/<h([2-3])[^>]*>(.*?)<\/h\1>/i', $content, $matches, PREG_SET_ORDER ) ) {
	return;
}
?>
<nav class="adp-toc" aria-label="<?php esc_attr_e( 'Table of contents', 'apk-directory-pro' ); ?>">
	<h2 class="adp-toc__title"><?php esc_html_e( 'Contents', 'apk-directory-pro' ); ?></h2>
	<ol class="adp-toc__list">
		<?php foreach ( $matches as $i => $match ) : ?>
			<li class="adp-toc__item adp-toc__item--h<?php echo esc_attr( $match[1] ); ?>">
				<a href="#adp-section-<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( wp_strip_all_tags( $match[2] ) ); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
