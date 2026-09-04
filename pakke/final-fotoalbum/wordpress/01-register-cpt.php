<?php
/**
 * 1 · Indholdstypen
 *
 * Et album er en post af typen final_album. Billederne gemmes som en
 * liste af attachment-ID'er fra det almindelige mediebibliotek — ikke
 * som filer i pluginnets eget bibliotek. Det er hele pointen i at
 * afløse Photo Gallery WD.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FA_CPT       = 'final_album';
const FA_META_IDS  = '_fa_image_ids';   // kommasepareret liste af attachment-ID'er, i visningsrækkefølge
const FA_META_PROD = '_fa_production';  // ID på den produktion albummet hører til
const FA_META_PAGE = '_fa_public_page'; // '1' = albummet har sin egen URL
const FA_META_SHOW = '_fa_in_production'; // '1' = vis albummet inde i produktionen

add_action( 'init', function () {

	register_post_type( FA_CPT, array(
		'labels' => array(
			'name'          => 'Fotoalbums',
			'singular_name' => 'Fotoalbum',
			'add_new_item'  => 'Tilføj nyt album',
			'edit_item'     => 'Rediger album',
			'menu_name'     => 'Fotoalbums',
		),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-format-gallery',
		'menu_position'=> 21,
		'supports'     => array( 'title', 'thumbnail' ),
		'has_archive'  => false,
		'rewrite'      => array( 'slug' => 'album', 'with_front' => false ),
	) );

	/* Metafelterne registreres, så de kan tilgås via REST og
	   beskyttes mod at blive skrevet af andet end vores egen skærm. */
	$fields = array(
		FA_META_IDS  => 'string',
		FA_META_PROD => 'integer',
		FA_META_PAGE => 'string',
		FA_META_SHOW => 'string',
	);

	foreach ( $fields as $key => $type ) {
		register_post_meta( FA_CPT, $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
} );

/**
 * Billed-ID'erne som et rent array.
 */
function fa_get_image_ids( $album_id ) {
	$raw = get_post_meta( $album_id, FA_META_IDS, true );
	if ( ! $raw ) {
		return array();
	}
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/**
 * Albummet der hører til en produktion — eller 0.
 */
function fa_get_album_for_production( $production_id ) {
	$found = get_posts( array(
		'post_type'        => FA_CPT,
		'post_status'      => 'publish',
		'posts_per_page'   => 1,
		'fields'           => 'ids',
		'no_found_rows'    => true,
		'meta_query'       => array(
			array( 'key' => FA_META_PROD, 'value' => (int) $production_id ),
		),
	) );

	return $found ? (int) $found[0] : 0;
}

/**
 * "Vis som egen side" slået fra betyder at URL'en ikke må svare.
 * Albummet virker stadig inde i produktionen — det er én kontakt,
 * ikke to kopier af indholdet.
 */
add_action( 'template_redirect', function () {
	if ( ! is_singular( FA_CPT ) ) {
		return;
	}
	if ( '1' !== get_post_meta( get_the_ID(), FA_META_PAGE, true ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
} );
