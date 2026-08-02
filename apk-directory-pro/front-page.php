<?php
/**
 * Front page template.
 */

get_header();
?>
<main id="main-content" class="adp-main">
	<?php get_template_part( 'template-parts/home/hero' ); ?>
	<?php get_template_part( 'template-parts/home/featured-categories' ); ?>
	<?php get_template_part( 'template-parts/home/trending' ); ?>
	<?php get_template_part( 'template-parts/home/latest-updates' ); ?>
	<?php get_template_part( 'template-parts/home/latest-games' ); ?>
	<?php get_template_part( 'template-parts/home/editors-choice' ); ?>
	<?php get_template_part( 'template-parts/home/popular' ); ?>
	<?php get_template_part( 'template-parts/home/blog' ); ?>
</main>
<?php
get_footer();
