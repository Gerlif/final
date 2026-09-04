<?php
/**
 * 6 · Fotokortene på oversigten
 *
 * I dag: videokort har <a href="....mp4" data-lity> og spiller i en
 * lightbox. Fotokort har <a href="/produktion/xxx-fotos/"> og
 * navigerer væk. Det er den eneste forskel — og den er årsagen til at
 * foto føles som en anden slags indhold end video.
 *
 * Efter: fotokortet får det samme greb som et galleribillede,
 * så album.js åbner lightboxen. Kortet skal derfor udsendes som en
 * <button class="fa-item"> pakket i en skjult .fa-gal med albummets
 * billeder — så har lightboxen hele albummet at bladre i.
 *
 * Bemærk: markup til oversigten ligger i jeres eget loop, ikke i
 * pluginnet. Funktionen her er tænkt som den, loopet kalder.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returnerer det klikbare felt til et produktionskort.
 * Har produktionen et album, åbnes lightboxen; ellers falder den
 * tilbage til et almindeligt link til produktionen.
 */
function fa_listekort_medie( $production_id ) {

	$album_id = fa_get_album_for_production( $production_id );
	$ids      = $album_id ? fa_get_image_ids( $album_id ) : array();

	if ( ! $ids ) {
		return ''; // ikke en fotoproduktion — loopet bruger sin nuværende markup
	}

	$forside = $ids[0];
	$src     = wp_get_attachment_image_src( $forside, 'large' );
	$navn    = get_the_title( $album_id );

	$ud = '<div class="fa-gal fa-gal-card" data-album="' . esc_attr( $navn ) . '">';

	foreach ( $ids as $i => $id ) {
		$full = wp_get_attachment_image_src( $id, 'full' );
		if ( ! $full ) {
			continue;
		}

		/* Kun det første felt vises. Resten er skjult, men ligger i
		   DOM'en, så lightboxen kan bladre i hele albummet. */
		$skjult = $i === 0 ? '' : ' hidden';

		$ud .= '<button type="button" class="fa-item"' . $skjult
			. ' data-w="' . (int) $full[1] . '"'
			. ' data-h="' . (int) $full[2] . '"'
			. ' data-full="' . esc_url( $full[0] ) . '">'
			. wp_get_attachment_image( $id, 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) );

		if ( $i === 0 ) {
			$ud .= '<span class="fa-card-badge">'
				. '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"'
				. ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. '<rect x="3" y="3" width="18" height="18" rx="2"/>'
				. '<circle cx="8.5" cy="8.5" r="1.5"/>'
				. '<polyline points="21 15 16 10 5 21"/></svg>'
				. count( $ids ) . '</span>';
		}

		$ud .= '</button>';
	}

	$ud .= '</div>';

	fa_need_assets();

	return $ud;
}
