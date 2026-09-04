<?php
/**
 * 4 · Assets
 *
 * CSS og JS indlæses kun på sider, der faktisk har et album.
 * Derfor registreres de på wp_enqueue_scripts og sættes i kø fra
 * fa_need_assets() — som shortcoden kalder, når den har udsendt noget.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style(
		'final-album',
		plugins_url( 'frontend/album.css', dirname( __FILE__ ) . '/plugin.php' ),
		array(),
		'1.0.0'
	);
	wp_register_script(
		'final-album',
		plugins_url( 'frontend/album.js', dirname( __FILE__ ) . '/plugin.php' ),
		array(),
		'1.0.0',
		true
	);
} );

/* Kaldes af shortcoden. Overskriver stubben i 03-shortcode.php. */
function fa_need_assets() {
	$GLOBALS['fa_needs_assets'] = true;
	wp_enqueue_style( 'final-album' );
	wp_enqueue_script( 'final-album' );
}
