<?php
/**
 * Front page template.
 *
 * @package AdpTheme
 */

get_header();
?>

<div class="adp-home">
	<?php
	$sections = Adp\Theme\get_homepage_sections();
	foreach ( $sections as $section ) {
		if ( ! Adp\Theme\is_homepage_section_enabled( $section ) ) {
			continue;
		}
		get_template_part( 'template-parts/home/' . $section );
	}
	?>
</div>

<?php
get_footer();
