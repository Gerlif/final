<?php
/**
 * 3 · Visningen
 *
 * [final_album id="12"]              — et bestemt album
 * [final_album production="5376"]    — albummet der hører til en produktion
 * [final_album]                      — albummet for den produktion man står på
 *
 * Udsender præcis den markup, der står i frontend/markup.html.
 * data-w og data-h er billedernes naturlige mål — album.js bruger dem
 * til at regne rækkerne ud, FØR billederne er hentet, så gitteret
 * ikke hopper undervejs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fa_render_album( $album_id ) {

	$ids = fa_get_image_ids( $album_id );
	if ( ! $ids ) {
		return '';
	}

	$navn = get_the_title( $album_id );
	$ud   = '<div class="fa-gal" data-album="' . esc_attr( $navn ) . '">';

	foreach ( $ids as $id ) {

		/* Fuld størrelse til lightboxen, "large" til gitteret. */
		$full = wp_get_attachment_image_src( $id, 'full' );
		if ( ! $full ) {
			continue; // billedet findes ikke længere
		}

		$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );

		$ud .= '<button type="button" class="fa-item"'
			. ' data-w="' . (int) $full[1] . '"'
			. ' data-h="' . (int) $full[2] . '"'
			. ' data-full="' . esc_url( $full[0] ) . '">';

		/* wp_get_attachment_image giver srcset og sizes gratis. */
		$ud .= wp_get_attachment_image( $id, 'large', false, array(
			'alt'     => $alt,
			'loading' => 'lazy',
			'sizes'   => '(max-width: 600px) 50vw, 33vw',
		) );

		$ud .= '</button>';
	}

	$ud .= '</div>';

	fa_need_assets();

	return $ud;
}

add_shortcode( 'final_album', function ( $atts ) {

	$atts = shortcode_atts( array(
		'id'         => 0,
		'production' => 0,
	), $atts, 'final_album' );

	$album_id = absint( $atts['id'] );

	if ( ! $album_id ) {
		$prod = absint( $atts['production'] );
		if ( ! $prod ) {
			$prod = get_the_ID();
		}
		$album_id = fa_get_album_for_production( $prod );

		/* Inde i en produktion respekteres kontakten "Vis i produktionen". */
		if ( $album_id && '1' !== get_post_meta( $album_id, FA_META_SHOW, true ) ) {
			return '';
		}
	}

	if ( ! $album_id ) {
		return '';
	}

	return fa_render_album( $album_id );
} );

/**
 * Lightboxen udskrives én gang pr. side, uanset hvor mange gallerier
 * der er — den genbruges af dem alle.
 *
 * fa_need_assets() ligger i 04-enqueue.php. Den må kun defineres ét
 * sted, ellers giver PHP "Cannot redeclare function" og hele sitet
 * går ned.
 */
add_action( 'wp_footer', function () {

	if ( empty( $GLOBALS['fa_needs_assets'] ) ) {
		return;
	}
	?>
	<div class="fa-lb" role="dialog" aria-modal="true" aria-label="Billedvisning">
		<div class="fa-lb-top">
			<span><span class="fa-lb-title"></span> <span class="fa-lb-count"></span></span>
			<button type="button" class="fa-lb-close" aria-label="Luk">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
				     stroke-linecap="round" aria-hidden="true">
					<line x1="6" y1="6" x2="18" y2="18"></line>
					<line x1="18" y1="6" x2="6" y2="18"></line>
				</svg>
			</button>
		</div>
		<div class="fa-lb-mid">
			<div class="fa-lb-stage"><img class="fa-lb-img" alt=""></div>
			<button type="button" class="fa-lb-nav fa-lb-prev" aria-label="Forrige billede">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
				     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<polyline points="15 18 9 12 15 6"></polyline>
				</svg>
			</button>
			<button type="button" class="fa-lb-nav fa-lb-next" aria-label="Næste billede">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
				     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<polyline points="9 18 15 12 9 6"></polyline>
				</svg>
			</button>
		</div>
		<div class="fa-lb-strip"></div>
	</div>
	<?php
}, 5 );
