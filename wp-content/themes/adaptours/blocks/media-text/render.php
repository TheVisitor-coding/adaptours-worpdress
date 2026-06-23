<?php
/**
 * Bloc adaptours/media-text — rendu serveur.
 *
 * Deux colonnes : texte (eyebrow + titre bichrome + corps + 2 boutons)
 * et image. La position de l'image (droite par défaut / gauche) est portée par un
 * modificateur de classe. Fond surface-alt full-bleed.
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

$eyebrow  = (string) ( $attributes['eyebrow'] ?? '' );
$part_1   = (string) ( $attributes['title_part_1'] ?? '' );
$part_2   = (string) ( $attributes['title_part_2'] ?? '' );
$body     = (string) ( $attributes['body'] ?? '' );
$image_id = (int) ( $attributes['image_id'] ?? 0 );
$alt      = (string) ( $attributes['image_alt'] ?? '' );
$position = ( 'left' === ( $attributes['media_position'] ?? 'right' ) ) ? 'left' : 'right';

$cta_primary_label   = (string) ( $attributes['cta_primary_label'] ?? '' );
$cta_primary_url     = (string) ( $attributes['cta_primary_url'] ?? '' );
$cta_secondary_label = (string) ( $attributes['cta_secondary_label'] ?? '' );
$cta_secondary_url   = (string) ( $attributes['cta_secondary_url'] ?? '' );

$has_title = '' !== trim( $part_1 . $part_2 );
$has_cta   = '' !== trim( $cta_primary_label ) || '' !== trim( $cta_secondary_label );

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'media-text media-text--media-' . $position )
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="media-text__inner">
		<div class="media-text__body">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="media-text__eyebrow eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( $has_title ) : ?>
				<h2 class="media-text__title"><?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php endif; ?>

			<?php if ( '' !== trim( wp_strip_all_tags( $body ) ) ) : ?>
				<div class="media-text__text"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>

			<?php if ( $has_cta ) : ?>
				<div class="media-text__cta">
					<?php if ( '' !== trim( $cta_primary_label ) ) : ?>
						<a class="button button--primary" href="<?php echo esc_url( '' !== trim( $cta_primary_url ) ? $cta_primary_url : '#' ); ?>"><?php echo esc_html( $cta_primary_label ); ?></a>
					<?php endif; ?>
					<?php if ( '' !== trim( $cta_secondary_label ) ) : ?>
						<a class="button button--secondary" href="<?php echo esc_url( '' !== trim( $cta_secondary_url ) ? $cta_secondary_url : '#' ); ?>"><?php echo esc_html( $cta_secondary_label ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<figure class="media-text__media<?php echo $image_id > 0 ? '' : ' media-text__media--placeholder'; ?>">
			<?php
			if ( $image_id > 0 ) {
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'    => 'media-text__img',
						'alt'      => esc_attr( $alt ),
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width: 768px) 100vw, 45vw',
					)
				);
			}
			?>
		</figure>
	</div>
</section>
