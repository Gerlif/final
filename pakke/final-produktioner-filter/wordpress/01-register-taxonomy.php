<?php
/**
 * Trin 1 — taksonomi til produktionstyper
 *
 * Hierarkisk, så de tre hovedkategorier er forældre og de 20+ typer
 * er børn under dem. Det matcher filter-UI'et 1:1:
 *
 *   Video                (forælder → data-main="video")
 *     ├─ Brandingfilm    (barn     → data-types="brandingfilm")
 *     ├─ Reklamefilm
 *     └─ Employer branding
 *   Animation og VFX
 *   Foto
 *
 * Læg i child theme functions.php eller et lille must-use plugin.
 */

add_action( 'init', function () {

	register_taxonomy( 'produktionstype', array( 'produktion' ), array(
		'labels'            => array(
			'name'          => 'Produktionstyper',
			'singular_name' => 'Produktionstype',
			'menu_name'     => 'Typer',
		),
		'hierarchical'      => true,   // forældre = hovedkategorier
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,   // nødvendig for blokeditoren og for at kunne læse termerne via API
		'rewrite'           => array(
			// Giver /produktioner/brandingfilm/.
			// Konflikter det med siden /produktioner/, så brug 'produktioner/type'
			// og skyl permalinks (Indstillinger → Permalinks → Gem).
			'slug'         => 'produktioner',
			'with_front'   => false,
			'hierarchical' => false,
		),
	) );

} );

/**
 * Valgfrit: opret de tre hovedkategorier automatisk, så de altid findes.
 * Kør én gang, fjern derefter.
 */
add_action( 'init', function () {
	foreach ( array(
		'Video'            => 'video',
		'Animation og VFX' => 'animation-og-vfx',
		'Foto'             => 'foto',
	) as $name => $slug ) {
		if ( ! term_exists( $slug, 'produktionstype' ) ) {
			wp_insert_term( $name, 'produktionstype', array( 'slug' => $slug ) );
		}
	}
}, 11 );
