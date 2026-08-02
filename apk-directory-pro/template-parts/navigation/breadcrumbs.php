<?php
/**
 * Breadcrumbs navigation.
 *
 * @package AdpTheme
 */

$crumbs = apply_filters( 'adp_theme_breadcrumbs', array() );

if ( empty( $crumbs ) ) {
	$crumbs[] = array(
		'label' => __( 'Home', 'apk-directory-pro' ),
		'url'   => home_url( '/' ),
	);

	if ( get_query_var( 'adp_versions_page' ) && class_exists( '\Adp\Core\Versions\Frontend' ) ) {
		$app = \Adp\Core\Versions\Frontend::get_app_post();
		if ( $app ) {
			if ( post_type_exists( 'adp_app' ) ) {
				$crumbs[] = array(
					'label' => __( 'Apps', 'apk-directory-pro' ),
					'url'   => get_post_type_archive_link( 'adp_app' ),
				);
			}
			$terms = get_the_terms( $app->ID, 'adp_app_category' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$crumbs[] = array(
					'label' => $terms[0]->name,
					'url'   => get_term_link( $terms[0] ),
				);
			}
			$crumbs[] = array(
				'label' => get_the_title( $app ),
				'url'   => get_permalink( $app ),
			);
			$crumbs[] = array(
				'label' => __( 'All Versions', 'apk-directory-pro' ),
				'url'   => '',
			);
		}
	} elseif ( is_singular( 'adp_app' ) ) {
		if ( post_type_exists( 'adp_app' ) ) {
			$crumbs[] = array(
				'label' => __( 'Apps', 'apk-directory-pro' ),
				'url'   => get_post_type_archive_link( 'adp_app' ),
			);
		}
		$terms = get_the_terms( get_the_ID(), 'adp_app_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$crumbs[] = array(
				'label' => $terms[0]->name,
				'url'   => get_term_link( $terms[0] ),
			);
		}
		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_post_type_archive( 'adp_app' ) ) {
		$crumbs[] = array(
			'label' => __( 'Apps', 'apk-directory-pro' ),
			'url'   => '',
		);
	} elseif ( is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			if ( post_type_exists( 'adp_app' ) ) {
				$crumbs[] = array(
					'label' => __( 'Apps', 'apk-directory-pro' ),
					'url'   => get_post_type_archive_link( 'adp_app' ),
				);
			}
			$crumbs[] = array(
				'label' => $term->name,
				'url'   => '',
			);
		}
	} elseif ( is_search() ) {
		$crumbs[] = array(
			'label' => __( 'Search', 'apk-directory-pro' ),
			'url'   => '',
		);
	} elseif ( is_singular( 'post' ) ) {
		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_page() ) {
		$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
		foreach ( $ancestors as $ancestor_id ) {
			$crumbs[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}
		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	}
}

if ( count( $crumbs ) <= 1 ) {
	return;
}
?>
<nav class="adp-breadcrumbs adp-container" aria-label="<?php esc_attr_e( 'Breadcrumb', 'apk-directory-pro' ); ?>">
	<ol class="adp-breadcrumbs__list">
		<?php foreach ( $crumbs as $index => $crumb ) : ?>
			<li class="adp-breadcrumbs__item">
				<?php if ( $crumb['url'] && $index < count( $crumbs ) - 1 ) : ?>
					<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
