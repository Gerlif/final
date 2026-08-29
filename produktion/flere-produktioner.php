<?php
/**
 * [final_flere_produktioner] — sidepanelet på produktionssiden.
 *
 * Viser de nyeste produktioner i samme kategori som den, man er inde på,
 * og fylder op med de nyeste i det hele taget, hvis der ikke er nok.
 *
 * Læg denne fil i child-temaet og inkludér den fra functions.php:
 *     require_once get_stylesheet_directory() . '/flere-produktioner.php';
 * — eller indsæt hele blokken direkte i functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * De boolean-felter på produktions-poden, der fungerer som kategorier.
 * Præcis de samme, som Pods-templaten bruger i sine [if ...]-blokke.
 */
function final_produktion_kategorifelter() {
	return array(
		'brandingfilm',
		'employer_branding',
		'produktvideo',
		'biografreklame',
		'rekrutteringsvideo',
		'explainervideo',
		'animation',
		'vfx',
		'drone',
		'beauty',
		'event',
		'case',
		'some',
		'foto',
		'interview',
		'travel',
	);
}

/**
 * Henter ID'er på produktioner, der deler mindst én kategori med $post_id.
 */
function final_produktioner_samme_kategori( $post_id, $limit, $exclude = array() ) {
	$meta_query = array( 'relation' => 'OR' );

	foreach ( final_produktion_kategorifelter() as $felt ) {
		$vaerdi = get_post_meta( $post_id, $felt, true );

		// Tomt, "0", "no" osv. tæller ikke som sat.
		if ( '' === $vaerdi || '0' === $vaerdi || 'no' === $vaerdi || 'false' === $vaerdi ) {
			continue;
		}

		// Vi matcher på præcis den værdi, feltet selv har, så det virker
		// uanset om Pods gemmer "1", "yes" eller noget tredje.
		$meta_query[] = array(
			'key'     => $felt,
			'value'   => $vaerdi,
			'compare' => '=',
		);
	}

	// Ingen kategorier sat på denne produktion.
	if ( count( $meta_query ) < 2 ) {
		return array();
	}

	return get_posts( array(
		'post_type'              => 'produktion',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'post__not_in'           => array_merge( array( $post_id ), $exclude ),
		'meta_query'             => $meta_query,
		'fields'                 => 'ids',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	) );
}

/**
 * Henter de nyeste produktioner, uanset kategori.
 */
function final_produktioner_nyeste( $limit, $exclude = array() ) {
	if ( $limit < 1 ) {
		return array();
	}

	return get_posts( array(
		'post_type'              => 'produktion',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'post__not_in'           => $exclude,
		'fields'                 => 'ids',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	) );
}

add_shortcode( 'final_flere_produktioner', function ( $atts ) {
	$atts = shortcode_atts( array(
		'limit' => 6,
		'id'    => 0,
	), $atts, 'final_flere_produktioner' );

	$limit   = max( 1, (int) $atts['limit'] );
	$post_id = (int) $atts['id'];

	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return '';
	}

	// 1. Samme kategori, nyeste først.
	$ids = final_produktioner_samme_kategori( $post_id, $limit );

	// 2. Fyld op med de nyeste, hvis der mangler.
	if ( count( $ids ) < $limit ) {
		$ids = array_merge(
			$ids,
			final_produktioner_nyeste(
				$limit - count( $ids ),
				array_merge( array( $post_id ), $ids )
			)
		);
	}

	if ( empty( $ids ) ) {
		return '';
	}

	$ud = '';

	foreach ( $ids as $id ) {
		// Poden har et eget titel-felt, der vinder over post-titlen —
		// samme logik som templaten bruger på selve siden.
		$titel = get_post_meta( $id, 'titel', true );
		if ( '' === $titel ) {
			$titel = get_the_title( $id );
		}

		$klient = get_post_meta( $id, 'klient', true );
		$billede = get_the_post_thumbnail_url( $id, 'medium_large' );

		$ud .= '<a class="fp-ri" href="' . esc_url( get_permalink( $id ) ) . '">';
		$ud .= '<span class="fp-ri-media">';

		if ( $billede ) {
			$ud .= '<img src="' . esc_url( $billede ) . '" alt="" loading="lazy" />';
		}

		$ud .= '</span>';
		$ud .= '<span class="fp-ri-text">';
		$ud .= '<span class="fp-ri-t notranslate">' . esc_html( $titel ) . '</span>';

		if ( $klient ) {
			$ud .= '<span class="fp-ri-k notranslate">' . esc_html( $klient ) . '</span>';
		}

		$ud .= '</span></a>';
	}

	return '<div class="fp-rail-list">' . $ud . '</div>';
} );
