<?php
/**
 * Single destination — coque figée : hero + bande méta (rendu PHP), puis zone Gutenberg
 * modulaire (the_content) restreinte aux blocs adaptours/*.
 *
 * La classe <body> `has-transparent-header` (posée par inc/template-hooks.php) rend le
 * header transparent au-dessus du hero, solide au scroll. Header/footer via parts/.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'parts/header' );

while ( have_posts() ) :
	the_post();

	$destination_id = get_the_ID();
	$hero           = adaptours_get_destination_hero_args( $destination_id );
	$band           = adaptours_get_destination_meta_band( $destination_id );

	// Fil d'ariane : Accueil / Destinations / {titre courant}.
	$home_url    = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	$archive_url = get_post_type_archive_link( 'destination' );

	// Ligne lieu : {titre} · {ville} · {coordonnées} (segments vides ignorés).
	$location_parts = array_filter(
		array( $hero['titre'], $hero['ville'], $hero['coordonnees'] ),
		static function ( $part ) {
			return '' !== trim( (string) $part );
		}
	);

	// Pin SVG (cercle orange habillé en CSS ; glyphe blanc, point orange = « trou »).
	$pin_svg = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false" role="img">'
		. '<path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7z"/>'
		. '<circle cx="12" cy="9" r="2.4" fill="#E8775F"/>'
		. '</svg>';
	?>
	<article <?php post_class( 'single-destination' ); ?>>

		<section class="single-destination__hero">
			<div class="single-destination__hero-media" aria-hidden="true">
				<?php
				if ( $hero['image_id'] > 0 ) {
					echo wp_get_attachment_image(
						$hero['image_id'],
						'full',
						false,
						array(
							'class'    => 'single-destination__hero-img',
							'loading'  => 'eager',
							'decoding' => 'async',
							'alt'      => '',
						)
					);
				}
				?>
				<span class="single-destination__hero-overlay"></span>
			</div>

			<div class="single-destination__hero-inner container">
				<nav class="single-destination__breadcrumb" aria-label="<?php esc_attr_e( 'Fil d’Ariane', 'adaptours' ); ?>">
					<a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Accueil', 'adaptours' ); ?></a>
					<span class="single-destination__breadcrumb-sep" aria-hidden="true">/</span>
					<?php if ( $archive_url ) : ?>
						<a href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Destinations', 'adaptours' ); ?></a>
						<span class="single-destination__breadcrumb-sep" aria-hidden="true">/</span>
					<?php endif; ?>
					<span class="single-destination__breadcrumb-current" aria-current="page"><?php echo esc_html( $hero['titre'] ); ?></span>
				</nav>

				<?php if ( ! empty( $location_parts ) ) : ?>
					<p class="single-destination__location">
						<span class="single-destination__pin"><?php echo wp_kses( $pin_svg, adaptours_svg_allowed_tags() ); ?></span>
						<span class="single-destination__location-text"><?php echo esc_html( implode( ' · ', $location_parts ) ); ?></span>
					</p>
				<?php endif; ?>

				<div class="single-destination__title-row">
					<h1 class="single-destination__title"><?php echo esc_html( $hero['titre'] ); ?></h1>
					<?php if ( '' !== $hero['accroche_manuscrite'] ) : ?>
						<span class="single-destination__handwritten"><?php echo esc_html( $hero['accroche_manuscrite'] ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( '' !== $hero['hero_accroche'] ) : ?>
					<p class="single-destination__lead"><?php echo esc_html( $hero['hero_accroche'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="single-destination__meta" aria-label="<?php esc_attr_e( 'Informations clés', 'adaptours' ); ?>">
			<div class="single-destination__meta-inner container">
				<?php foreach ( $band['cells'] as $cell ) : ?>
					<div class="single-destination__meta-cell">
						<p class="single-destination__meta-eyebrow"><?php echo esc_html( $cell['eyebrow'] ); ?></p>
						<p class="single-destination__meta-value"><?php echo esc_html( $cell['value'] ); ?></p>
						<?php if ( '' !== $cell['sub'] ) : ?>
							<p class="single-destination__meta-sub"><?php echo esc_html( $cell['sub'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>

				<?php if ( '' !== $band['prix'] ) : ?>
					<div class="single-destination__meta-cell single-destination__meta-cell--price">
						<p class="single-destination__meta-eyebrow"><?php esc_html_e( 'À partir de', 'adaptours' ); ?></p>
						<p class="single-destination__meta-value"><?php echo esc_html( $band['prix'] ); ?></p>
						<p class="single-destination__meta-sub"><?php echo esc_html( $band['prix_sub'] ); ?></p>
					</div>
				<?php endif; ?>

				<div class="single-destination__meta-cta">
					<a class="button button--primary" href="<?php echo esc_url( $band['cta_url'] ); ?>">
						<?php echo esc_html( $band['cta_label'] ); ?>
					</a>
				</div>
			</div>
		</section>

		<main id="main" class="site-main container">
			<?php the_content(); ?>
		</main>

	</article>
	<?php
endwhile;

get_template_part( 'parts/footer' );
