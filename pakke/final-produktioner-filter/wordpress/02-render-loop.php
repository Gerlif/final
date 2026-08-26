<?php
/**
 * Trin 2 — ét loop i stedet for de fire håndbyggede faner
 *
 * VIGTIGT: markup inde i .produktioner-grid-item er kopieret fra den
 * nuværende side og skal blive som det er. Det eneste nye er de tre
 * data-attributter på selve grid-item'et.
 *
 * De felter, der hentes med get_field(), skal mappes til jeres faktiske
 * ACF-feltnavne — se README, afsnittet "Felter der skal mappes".
 */

$produktioner = new WP_Query( array(
	'post_type'      => 'produktion',
	'posts_per_page' => -1,          // alle; JS viser 24 ad gangen
	'orderby'        => 'date',
	'order'          => 'DESC',
	'no_found_rows'  => true,
) );

if ( $produktioner->have_posts() ) : ?>

<div class="produktioner-grid">

	<?php while ( $produktioner->have_posts() ) : $produktioner->the_post();

		$termer = wp_get_post_terms( get_the_ID(), 'produktionstype' );
		if ( is_wp_error( $termer ) ) { $termer = array(); }

		// forældre = hovedkategori, børn = typer
		$hoved = '';
		$typer = array();
		foreach ( $termer as $t ) {
			if ( 0 === (int) $t->parent ) {
				$hoved = $t->slug;
			} else {
				$typer[] = $t->slug;
				if ( ! $hoved ) {
					$foraelder = get_term( $t->parent, 'produktionstype' );
					if ( $foraelder && ! is_wp_error( $foraelder ) ) { $hoved = $foraelder->slug; }
				}
			}
		}

		// ↓ MAP TIL JERES EGNE FELTNAVNE ↓
		$video_url  = get_field( 'video_fil' );   // fx https://final.dk/wp-content/uploads/…/film.mp4
		$klient     = get_field( 'klient' );      // fx "Aalborg Convention Bureau"
		$thumb      = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		?>

		<div class="produktioner-grid-item aos-fade"
			 data-aos="fade"
			 data-main="<?php echo esc_attr( $hoved ); ?>"
			 data-types="<?php echo esc_attr( implode( ',', $typer ) ); ?>"
			 data-search="<?php echo esc_attr( get_the_title() . ' ' . $klient ); ?>">

			<?php /* ---- herfra og ned: uændret i forhold til i dag ---- */ ?>
			<a href="<?php echo esc_url( $video_url ); ?>?controls=0" data-lity>
				<div class="final-thumbnail-container" data-video-src="<?php echo esc_url( $video_url ); ?>">
					<img decoding="async" src="<?php echo esc_url( $thumb ); ?>" alt="Thumbnail" loading="lazy" />
					<video muted loop preload="none" loading="lazy" playsinline>
						<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4" />
					</video>
					<div class="video-spinner"></div>
				</div>
			</a>

			<p style="line-height: 1; text-align: left;">
				<a style="color: #d9d9d9; text-decoration: none;" href="<?php the_permalink(); ?>">
					<span style="font-family:REM-Light; text-align: left;" class="notranslate"><?php the_title(); ?></span>
				</a><br>
				<?php if ( $klient ) : ?>
					<a style="color: #76777f; text-decoration: none; font-size: small;" href="<?php the_permalink(); ?>">
						<b>Klient:</b> <span class="notranslate"><?php echo esc_html( $klient ); ?></span>
					</a>
				<?php endif; ?>
			</p>

		</div>

	<?php endwhile; ?>

</div>

<?php
wp_reset_postdata();
endif;
