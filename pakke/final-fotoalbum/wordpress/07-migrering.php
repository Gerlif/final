<?php
/**
 * 7 · Flytningen fra Photo Gallery WD
 *
 * De fire eksisterende gallerier ligger i pluginnets egne tabeller
 * (wp_bwg_gallery og wp_bwg_image) med filerne i
 * /uploads/photo-gallery/. Billederne skal ind i mediebiblioteket,
 * og der skal oprettes et final_album pr. galleri.
 *
 * Kør ÉN gang, og tag en database-backup først.
 * Kald: wp eval-file 07-migrering.php   — eller besøg
 * /wp-admin/?fa_migrate=1 som administrator.
 *
 * Scriptet er idempotent: har et billede allerede en attachment med
 * samme filnavn, genbruges den i stedet for at blive uploadet igen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fa_migrer_fra_bwg() {

	global $wpdb;

	if ( ! current_user_can( 'manage_options' ) ) {
		return 'Kræver administrator.';
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$gallerier = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}bwg_gallery WHERE published = 1" );
	if ( ! $gallerier ) {
		return 'Fandt ingen gallerier. Er tabellen wp_bwg_gallery der stadig?';
	}

	$log = array();

	foreach ( $gallerier as $g ) {

		/* Er galleriet allerede flyttet? */
		$findes = get_posts( array(
			'post_type'      => FA_CPT,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( array( 'key' => '_fa_bwg_id', 'value' => (int) $g->id ) ),
		) );
		if ( $findes ) {
			$log[] = sprintf( 'Springer over: "%s" er flyttet før.', $g->name );
			continue;
		}

		$billeder = $wpdb->get_results( $wpdb->prepare(
			"SELECT filename, alt, `order` FROM {$wpdb->prefix}bwg_image
			 WHERE gallery_id = %d AND published = 1 ORDER BY `order` ASC",
			$g->id
		) );

		$ids = array();

		foreach ( $billeder as $b ) {

			$sti = trailingslashit( wp_upload_dir()['basedir'] ) . 'photo-gallery/' . ltrim( $b->filename, '/' );
			if ( ! file_exists( $sti ) ) {
				$log[] = 'Mangler fil: ' . $b->filename;
				continue;
			}

			/* Genbrug hvis filnavnet allerede findes i mediebiblioteket */
			$eksisterende = get_posts( array(
				'post_type'      => 'attachment',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'name'           => sanitize_title( pathinfo( $b->filename, PATHINFO_FILENAME ) ),
			) );

			if ( $eksisterende ) {
				$ids[] = (int) $eksisterende[0];
				continue;
			}

			/* Kopiér ind i uploads og opret attachment.
			   media_handle_sideload flytter filen, så vi kopierer først. */
			$tmp = wp_tempnam( $sti );
			copy( $sti, $tmp );

			$fil = array(
				'name'     => basename( $sti ),
				'tmp_name' => $tmp,
			);

			$att = media_handle_sideload( $fil, 0 );

			if ( is_wp_error( $att ) ) {
				@unlink( $tmp );
				$log[] = 'Fejl: ' . $b->filename . ' — ' . $att->get_error_message();
				continue;
			}

			if ( $b->alt ) {
				update_post_meta( $att, '_wp_attachment_image_alt', sanitize_text_field( $b->alt ) );
			}

			$ids[] = (int) $att;
		}

		if ( ! $ids ) {
			$log[] = sprintf( 'Ingen billeder flyttet for "%s".', $g->name );
			continue;
		}

		$album_id = wp_insert_post( array(
			'post_type'   => FA_CPT,
			'post_status' => 'publish',
			'post_title'  => $g->name,
		) );

		update_post_meta( $album_id, FA_META_IDS, implode( ',', $ids ) );
		update_post_meta( $album_id, FA_META_SHOW, '1' );
		update_post_meta( $album_id, FA_META_PAGE, '' ); // egen side er slået fra som udgangspunkt
		update_post_meta( $album_id, '_fa_bwg_id', (int) $g->id );

		$log[] = sprintf( 'Flyttet "%s": %d billeder → album %d', $g->name, count( $ids ), $album_id );
	}

	return implode( "\n", $log );
}

add_action( 'admin_init', function () {
	if ( isset( $_GET['fa_migrate'] ) && current_user_can( 'manage_options' ) ) {
		echo '<pre>' . esc_html( fa_migrer_fra_bwg() ) . '</pre>';
		exit;
	}
} );
