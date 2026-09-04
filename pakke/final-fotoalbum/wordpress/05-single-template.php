<?php
/**
 * 5 · Albummets egen side
 *
 * Kun relevant når "Vis som egen side" er slået til — ellers har
 * 01-register-cpt.php allerede sendt et 404 afsted.
 *
 * Skabelonen ligger i pluginnet, så temaet ikke skal røres. Vil I
 * hellere styre den fra child-temaet, så læg en single-final_album.php
 * der; WordPress vælger temaets udgave frem for pluginnets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'single_template', function ( $template ) {

	if ( ! is_singular( FA_CPT ) ) {
		return $template;
	}

	$tema = locate_template( array( 'single-' . FA_CPT . '.php' ) );
	if ( $tema ) {
		return $tema;
	}

	return dirname( __FILE__ ) . '/views/single-album.php';
} );
