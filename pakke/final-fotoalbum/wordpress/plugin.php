<?php
/**
 * Plugin Name: Final Film — Fotoalbum
 * Description: Fotoalbums til produktioner, med lightbox man kan bladre i og valgfri egen side.
 * Version:     1.0.0
 * Author:      Final Film
 * Text Domain: final-album
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FA_DIR', plugin_dir_path( __FILE__ ) );
define( 'FA_URL', plugin_dir_url( __FILE__ ) );

/* Rækkefølgen betyder noget: 01 definerer konstanter og hjælpere,
   som de øvrige bruger. 04 skal ligge før 03, fordi 03 kalder
   fa_need_assets(). */
require_once FA_DIR . '01-register-cpt.php';
require_once FA_DIR . '02-admin.php';
require_once FA_DIR . '04-enqueue.php';
require_once FA_DIR . '03-shortcode.php';
require_once FA_DIR . '05-single-template.php';
require_once FA_DIR . '06-listekort.php';

/* Flytningen fra Photo Gallery WD køres én gang og kan derefter
   udkommenteres. */
require_once FA_DIR . '07-migrering.php';

/* Permalinks skal skylles, når pluginnet slås til — ellers giver
   /album/xxx/ et 404, indtil man gemmer permalink-indstillingerne. */
register_activation_hook( __FILE__, function () {
	require_once FA_DIR . '01-register-cpt.php';
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
