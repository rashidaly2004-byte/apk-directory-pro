<?php
?>
<article class="adp-blog-card">
	<a href="<?php the_permalink(); ?>" class="adp-blog-card__link">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="adp-blog-card__image"><?php the_post_thumbnail( 'medium', [ 'loading' => 'lazy' ] ); ?></div>
		<?php endif; ?>
		<div class="adp-blog-card__body">
			<h2 class="adp-blog-card__title"><?php the_title(); ?></h2>
			<p class="adp-blog-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
			<p class="adp-blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
		</div>
	</a>
</article>
