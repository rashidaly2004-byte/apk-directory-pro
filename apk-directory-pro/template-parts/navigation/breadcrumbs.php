<?php
if ( ! apply_filters( 'adp_output_breadcrumbs', true ) ) {
	return;
}
?>
<nav class="adp-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'apk-directory-pro' ); ?>">
	<ol class="adp-breadcrumbs__list">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'apk-directory-pro' ); ?></a></li>
		<?php if ( is_singular( 'adp_app' ) ) : ?>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'adp_app' ) ); ?>"><?php esc_html_e( 'Apps', 'apk-directory-pro' ); ?></a></li>
			<li aria-current="page"><?php the_title(); ?></li>
		<?php elseif ( is_post_type_archive( 'adp_app' ) ) : ?>
			<li aria-current="page"><?php esc_html_e( 'Apps', 'apk-directory-pro' ); ?></li>
		<?php elseif ( is_tax() ) : ?>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'adp_app' ) ); ?>"><?php esc_html_e( 'Apps', 'apk-directory-pro' ); ?></a></li>
			<li aria-current="page"><?php single_term_title(); ?></li>
		<?php elseif ( is_search() ) : ?>
			<li aria-current="page"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></li>
		<?php endif; ?>
	</ol>
</nav>
