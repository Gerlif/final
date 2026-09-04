<?php
/**
 * 2 · Adminskærmen
 *
 * Tre metabokse, som på mockuppet:
 *   Billeder    — træk-og-slip / vælg fra mediebiblioteket, træk for at sortere
 *   Udgiv       — kontakten "Vis som egen side" + permalink
 *   Tilknytning — hvilken produktion, og om albummet vises derinde
 *
 * Billedvælgeren bruger wp.media, som allerede findes i WordPress.
 * Sortering bruger jQuery UI Sortable, som WordPress også har med.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'fa-images', 'Billeder', 'fa_box_images', FA_CPT, 'normal', 'high' );
	add_meta_box( 'fa-link', 'Tilknytning', 'fa_box_link', FA_CPT, 'side', 'default' );
	add_meta_box( 'fa-shortcode', 'Indsæt manuelt', 'fa_box_shortcode', FA_CPT, 'side', 'low' );
} );

function fa_box_images( $post ) {
	wp_nonce_field( 'fa_save', 'fa_nonce' );
	$ids = fa_get_image_ids( $post->ID );
	?>
	<div class="fa-admin">
		<div class="fa-drop" id="fa-drop">
			<b>Træk billeder hertil</b>
			eller <button type="button" class="button" id="fa-pick">vælg fra mediebiblioteket</button>
		</div>

		<ul class="fa-grid" id="fa-grid">
			<?php foreach ( $ids as $i => $id ) :
				$src = wp_get_attachment_image_url( $id, 'thumbnail' );
				if ( ! $src ) {
					continue; // billedet er slettet fra mediebiblioteket
				}
				?>
				<li class="fa-cell" data-id="<?php echo esc_attr( $id ); ?>">
					<img src="<?php echo esc_url( $src ); ?>" alt="">
					<span class="fa-handle" title="Træk for at flytte">⋮⋮</span>
					<button type="button" class="fa-remove" aria-label="Fjern">×</button>
					<span class="fa-num"><?php echo (int) $i + 1; ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="description">
			Træk i håndtaget for at ændre rækkefølgen. Rækkefølgen her er den, billederne vises i.
		</p>

		<input type="hidden" name="fa_image_ids" id="fa-ids"
		       value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
	</div>
	<?php
}

function fa_box_link( $post ) {
	$prod = (int) get_post_meta( $post->ID, FA_META_PROD, true );
	$page = get_post_meta( $post->ID, FA_META_PAGE, true );
	$show = get_post_meta( $post->ID, FA_META_SHOW, true );

	$productions = get_posts( array(
		'post_type'      => 'produktion',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	?>
	<p>
		<label for="fa-prod"><strong>Produktion</strong></label>
		<select name="fa_production" id="fa-prod" class="widefat">
			<option value="0">— ingen —</option>
			<?php foreach ( $productions as $p ) : ?>
				<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( $prod, $p->ID ); ?>>
					<?php echo esc_html( get_the_title( $p ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>

	<p>
		<label>
			<input type="checkbox" name="fa_in_production" value="1" <?php checked( $show, '1' ); ?>>
			Vis i produktionen
		</label>
	</p>

	<p>
		<label>
			<input type="checkbox" name="fa_public_page" value="1" <?php checked( $page, '1' ); ?>>
			Vis som egen side
		</label>
	</p>

	<p class="description">
		<?php echo esc_html( get_permalink( $post ) ); ?><br>
		Slås den fra, findes URL'en ikke — albummet virker stadig inde i produktionen.
	</p>
	<?php
}

function fa_box_shortcode( $post ) {
	?>
	<p class="description">Vil du placere albummet et andet sted:</p>
	<code>[final_album id="<?php echo (int) $post->ID; ?>"]</code>
	<?php
}

add_action( 'save_post_' . FA_CPT, function ( $post_id ) {

	if ( ! isset( $_POST['fa_nonce'] ) || ! wp_verify_nonce( $_POST['fa_nonce'], 'fa_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* Billed-ID'erne kommer som en kommasepareret streng fra det skjulte felt.
	   Absint på hvert element — der må ikke kunne skrives andet end tal. */
	$raw = isset( $_POST['fa_image_ids'] ) ? wp_unslash( $_POST['fa_image_ids'] ) : '';
	$ids = array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
	update_post_meta( $post_id, FA_META_IDS, implode( ',', $ids ) );

	update_post_meta( $post_id, FA_META_PROD, isset( $_POST['fa_production'] ) ? absint( $_POST['fa_production'] ) : 0 );
	update_post_meta( $post_id, FA_META_PAGE, isset( $_POST['fa_public_page'] ) ? '1' : '' );
	update_post_meta( $post_id, FA_META_SHOW, isset( $_POST['fa_in_production'] ) ? '1' : '' );
} );

/**
 * Adminens egne assets. wp.media og jQuery UI Sortable følger med WordPress.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post_type;
	if ( FA_CPT !== $post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'fa-admin',
		plugins_url( 'assets/admin.css', dirname( __FILE__ ) . '/plugin.php' ),
		array(),
		'1.0.0'
	);

	wp_enqueue_script(
		'fa-admin',
		plugins_url( 'assets/admin.js', dirname( __FILE__ ) . '/plugin.php' ),
		array( 'jquery', 'jquery-ui-sortable' ),
		'1.0.0',
		true
	);
} );
