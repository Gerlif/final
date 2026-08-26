<?php
/**
 * Trin 3 — indlæs CSS og JS, kun hvor de bruges
 *
 * Læg filerne i child theme:
 *   /wp-content/themes/kadence-child-theme/css/filter-ui.css
 *   /wp-content/themes/kadence-child-theme/js/filter-ui.js
 */

add_action( 'wp_enqueue_scripts', function () {

	// kun på produktionsoversigten og på term-arkiverne
	if ( ! is_page( 'produktioner' ) && ! is_tax( 'produktionstype' ) ) {
		return;
	}

	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	wp_enqueue_style(
		'ff-filter',
		$uri . '/css/filter-ui.css',
		array(),
		file_exists( $dir . '/css/filter-ui.css' ) ? filemtime( $dir . '/css/filter-ui.css' ) : null
	);

	wp_enqueue_script(
		'ff-filter',
		$uri . '/js/filter-ui.js',
		array(),
		file_exists( $dir . '/js/filter-ui.js' ) ? filemtime( $dir . '/js/filter-ui.js' ) : null,
		true
	);

}, 20 );
