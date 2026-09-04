<?php
/**
 * Albummets egen side.
 *
 * Layoutet følger produktionssiden: brødkrumme, titel, faktalinje,
 * derefter galleriet. Klasserne .fa-page-* er kun til denne skabelon.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$album_id = get_the_ID();
$prod_id  = (int) get_post_meta( $album_id, FA_META_PROD, true );
$antal    = count( fa_get_image_ids( $album_id ) );
$klient   = $prod_id ? get_post_meta( $prod_id, 'klient', true ) : '';
?>

<div class="fa-page">

	<a class="fa-page-back" href="<?php echo esc_url( home_url( '/produktioner/' ) ); ?>">
		Alle produktioner
	</a>

	<div class="fa-page-head">
		<p class="fa-page-kicker">Fotoalbum</p>
		<h1 class="fa-page-title notranslate"><?php the_title(); ?></h1>
	</div>

	<div class="fa-page-facts">
		<?php if ( $klient ) : ?>
			<div class="fa-fact">
				<span class="fa-fact-lab">Klient</span>
				<span class="fa-fact-val notranslate"><?php echo esc_html( $klient ); ?></span>
			</div>
		<?php endif; ?>

		<div class="fa-fact">
			<span class="fa-fact-lab">Billeder</span>
			<span class="fa-fact-val"><?php echo (int) $antal; ?></span>
		</div>

		<?php if ( $prod_id ) : ?>
			<div class="fa-fact">
				<span class="fa-fact-lab">Produktion</span>
				<a class="fa-fact-val" href="<?php echo esc_url( get_permalink( $prod_id ) ); ?>">
					<?php echo esc_html( get_the_title( $prod_id ) ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>

	<?php echo fa_render_album( $album_id ); // markup er escaped i fa_render_album() ?>

</div>

<?php
get_footer();
