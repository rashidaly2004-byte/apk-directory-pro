<section class="adp-hero adp-container">
	<div class="adp-hero__inner">
		<h1 class="adp-hero__title"><?php bloginfo( 'name' ); ?></h1>
		<p class="adp-hero__subtitle"><?php bloginfo( 'description' ); ?></p>
		<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="adp-hero__search">
			<label class="adp-search-label" for="adp-hero-search"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></label>
			<input type="search" id="adp-hero-search" name="s" placeholder="<?php esc_attr_e( 'Find apps, games, guides…', 'apk-directory-pro' ); ?>" class="adp-search-input" />
		</form>
	</div>
</section>
