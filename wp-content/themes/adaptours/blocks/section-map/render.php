<?php
/**
 * Bloc adaptours/section-map — carte du voyage.
 *
 * Compose : header (eyebrow + titre bichrome + description de vol + distance) et carte
 * (image `carte_image` + bande d'infos 4 colonnes). Les données viennent du CPT destination
 * via adaptours_get_destination_map_args() ; le bloc reste agnostique.
 *
 * L'ID destination vient du post courant (front) ou du paramètre `post_id` injecté par
 * l'éditeur (aperçu SSR).
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (non utilisé).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow        = (string) ( $attributes['eyebrow'] ?? '' );
$title          = (string) ( $attributes['title'] ?? '' );
$title_accent   = (string) ( $attributes['title_accent'] ?? '' );
$description    = (string) ( $attributes['description'] ?? '' );
$distance_label = (string) ( $attributes['distance_label'] ?? '' );

// ID destination : post courant, ou aperçu éditeur (param GET `post_id`).
$destination_id = get_the_ID();
if ( ! $destination_id && isset( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$destination_id = (int) $_GET['post_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$data = adaptours_get_destination_map_args( (int) $destination_id );

// Ligne distance : libellé libre prioritaire, sinon valeur auto depuis les km.
$distance = '' !== trim( $distance_label ) ? $distance_label : $data['distance_auto'];

$wrapper = get_block_wrapper_attributes(
	array(
		'aria-label' => esc_attr__( 'Carte du voyage', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<div class="section-map__inner">
		<header class="section-map__head">
			<div class="section-map__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="section-map__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $title ) || '' !== trim( $title_accent ) ) : ?>
					<h2 class="section-map__title"><?php echo adaptours_bichrome( trim( $title . ' ' . $title_accent ), $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>

			<div class="section-map__aside">
				<?php
				if ( '' !== trim( $description ) ) {
					foreach ( preg_split( '/\r\n|\r|\n/', $description ) as $line ) {
						$line = trim( $line );
						if ( '' !== $line ) {
							echo '<p class="section-map__desc">' . esc_html( $line ) . '</p>';
						}
					}
				}
				?>
				<?php if ( '' !== trim( $distance ) ) : ?>
					<p class="section-map__distance"><?php echo esc_html( $distance ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="section-map__card">
			<div class="section-map__media<?php echo $data['image_id'] ? '' : ' section-map__media--placeholder'; ?>">
				<?php
				if ( $data['image_id'] ) {
					echo wp_get_attachment_image(
						$data['image_id'],
						'large',
						false,
						array(
							'class'   => 'section-map__img',
							'loading' => 'lazy',
							'sizes'   => '(max-width: 1280px) 100vw, 1200px',
						)
					);
				}
				?>
			</div>

			<?php if ( ! empty( $data['cells'] ) ) : ?>
				<dl class="section-map__band">
					<?php foreach ( $data['cells'] as $cell ) : ?>
						<div class="section-map__cell">
							<dt class="section-map__cell-label"><?php echo esc_html( $cell['label'] ); ?></dt>
							<dd class="section-map__cell-value"><?php echo esc_html( $cell['value'] ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>
	</div>
</section>
