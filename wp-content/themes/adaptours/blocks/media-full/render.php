<?php
/**
 * Bloc adaptours/media-full — rendu serveur.
 *
 * Grande image, pleine largeur (défaut) ou cadrée à 1280px, avec une
 * légende facultative superposée. Image chargée en lazy + responsive (srcset WordPress).
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

$image_id = (int) ( $attributes['image_id'] ?? 0 );
$alt      = (string) ( $attributes['image_alt'] ?? '' );
$caption  = (string) ( $attributes['caption'] ?? '' );
$width    = ( 'boxed' === ( $attributes['width'] ?? 'full-bleed' ) ) ? 'boxed' : 'full-bleed';

// Rien à afficher tant qu'aucune image n'est choisie (évite un bloc vide en front).
if ( $image_id <= 0 ) {
	return;
}

$has_caption = '' !== trim( $caption );

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'media-full media-full--' . $width )
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<figure class="media-full__figure">
		<?php
		echo wp_get_attachment_image(
			$image_id,
			'full',
			false,
			array(
				'class'    => 'media-full__img',
				'alt'      => esc_attr( $alt ),
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => 'boxed' === $width ? '(max-width: 1280px) 100vw, 1280px' : '100vw',
			)
		);
		?>
		<?php if ( $has_caption ) : ?>
			<figcaption class="media-full__caption"><?php echo esc_html( $caption ); ?></figcaption>
		<?php endif; ?>
	</figure>
</section>
