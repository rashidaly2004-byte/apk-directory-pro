<?php
/**
 * Blog post card.
 *
 * @package AdpTheme
 */
?>
<article <?php post_class( 'adp-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="adp-post-card__image-link" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'medium', array( 'class' => 'adp-post-card__image' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="adp-post-card__body">
		<h2 class="adp-post-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<div class="adp-post-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</div>
		<div class="adp-post-card__excerpt">
			<?php the_excerpt(); ?>
		</div>
		<a href="<?php the_permalink(); ?>" class="adp-post-card__read-more">
			<?php esc_html_e( 'Read more', 'apk-directory-pro' ); ?>
		</a>
	</div>
</article>
