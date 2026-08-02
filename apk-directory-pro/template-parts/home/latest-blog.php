<?php
/**
 * Latest blog posts section.
 *
 * @package AdpTheme
 */

$count = Adp\Theme\get_homepage_section_count( 'latest-blog', 3 );
$query = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => $count,
	'post_status'    => 'publish',
) );

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="adp-section adp-container" aria-labelledby="adp-latest-blog-title">
	<header class="adp-section__header">
		<h2 id="adp-latest-blog-title" class="adp-section__title"><?php esc_html_e( 'From the blog', 'apk-directory-pro' ); ?></h2>
		<?php
		$blog_page = get_option( 'page_for_posts' );
		if ( $blog_page ) :
			?>
			<a href="<?php echo esc_url( get_permalink( $blog_page ) ); ?>" class="adp-section__link"><?php esc_html_e( 'See all', 'apk-directory-pro' ); ?></a>
		<?php endif; ?>
	</header>
	<div class="adp-post-list adp-post-list--grid">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			get_template_part( 'template-parts/cards/post-card' );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
