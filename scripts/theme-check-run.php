<?php
/**
 * Run Theme Check against apk-directory-pro (production ZIP theme when available).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$check_file = WP_PLUGIN_DIR . '/theme-check/checkbase.php';
if ( ! file_exists( $check_file ) ) {
	WP_CLI::error( 'Theme Check plugin not installed.' );
}

require_once $check_file;

$candidates = array( 'apk-directory-pro-prod', 'apk-directory-pro' );
$slug       = getenv( 'THEME_CHECK_SLUG' ) ?: null;
if ( ! $slug ) {
	foreach ( $candidates as $candidate ) {
		if ( wp_get_theme( $candidate )->exists() ) {
			$slug = $candidate;
			break;
		}
	}
}

if ( ! $slug ) {
	WP_CLI::error( 'No apk-directory-pro theme found.' );
}

$theme = wp_get_theme( $slug );
if ( ! $theme->exists() ) {
	WP_CLI::error( 'Theme not found: ' . $slug );
}

$pass = run_themechecks_against_theme( $theme, $slug );

global $themechecks;
$required    = array();
$warnings    = array();
$recommended = array();
$info        = array();

foreach ( $themechecks as $check ) {
	if ( ! $check instanceof themecheck ) {
		continue;
	}
	$errors = (array) $check->getError();
	foreach ( $errors as $error ) {
		$plain = wp_strip_all_tags( (string) $error );
		if ( str_contains( $error, 'tc-required' ) ) {
			$required[] = $plain;
		} elseif ( str_contains( $error, 'tc-warning' ) ) {
			$warnings[] = $plain;
		} elseif ( str_contains( $error, 'tc-recommended' ) ) {
			$recommended[] = $plain;
		} else {
			$info[] = $plain;
		}
	}
}

WP_CLI::log( 'Theme Check slug: ' . $slug );
WP_CLI::log( 'Theme Check: ' . $theme->get( 'Name' ) );
WP_CLI::log( 'REQUIRED: ' . count( $required ) );
WP_CLI::log( 'WARNING: ' . count( $warnings ) );
WP_CLI::log( 'RECOMMENDED: ' . count( $recommended ) );
WP_CLI::log( 'INFO: ' . count( $info ) );

foreach ( $required as $line ) {
	WP_CLI::warning( '[REQUIRED] ' . $line );
}

foreach ( $warnings as $line ) {
	WP_CLI::log( '[WARNING] ' . $line );
}

foreach ( $recommended as $line ) {
	WP_CLI::log( '[RECOMMENDED] ' . $line );
}

foreach ( $info as $line ) {
	WP_CLI::log( '[INFO] ' . $line );
}

if ( ! empty( $required ) ) {
	WP_CLI::error( 'Theme Check REQUIRED failures: ' . count( $required ) );
}

if ( $pass ) {
	WP_CLI::success( 'Theme Check passed (no REQUIRED failures).' );
} else {
	WP_CLI::success( 'Theme Check completed with no REQUIRED failures.' );
}
