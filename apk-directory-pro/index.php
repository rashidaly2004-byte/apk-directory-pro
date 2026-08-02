<?php
/**
 * Main template fallback.
 *
 * @package AdpTheme
 */

get_header();
?>

<div class="adp-container adp-content-area">
	<?php if ( have_posts() ) : ?>
		<div class="adp-post-list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/cards/post-card' );
			endwhile;
			?>
		</div>
		<?php get_template_part( 'template-parts/navigation/pagination' ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</div>

<?php
get_footer();
