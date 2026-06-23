<?php
/**
 * Bloc adaptours/content-storytelling — texte + 3 polaroïds.
 *
 * Bloc éditorial réutilisable. Éditable : surtitre (facultatif), titre bichrome, texte, 3 photos.
 * Figé : layout texte/polaroïds et rotations décoratives (CSS).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow = (string) ( $attributes['eyebrow'] ?? '' );
$part_1  = (string) ( $attributes['title_part_1'] ?? '' );
$part_2  = (string) ( $attributes['title_part_2'] ?? '' );
$body    = (string) ( $attributes['body'] ?? '' );

$photos = array(
	(int) ( $attributes['photo_1'] ?? 0 ),
	(int) ( $attributes['photo_2'] ?? 0 ),
	(int) ( $attributes['photo_3'] ?? 0 ),
);

$wrapper = get_block_wrapper_attributes( array( 'class' => 'content-storytelling' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="content-storytelling__inner">
		<div class="content-storytelling__body">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="content-storytelling__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
				<h2 class="content-storytelling__title">
					<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</h2>
			<?php endif; ?>

			<?php if ( '' !== trim( $body ) ) : ?>
				<div class="content-storytelling__text">
					<?php echo wpautop( esc_html( $body ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_html avant wpautop. ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="content-storytelling__gallery" aria-hidden="true">
			<?php foreach ( $photos as $index => $photo_id ) : ?>
				<figure class="content-storytelling__polaroid content-storytelling__polaroid--<?php echo (int) ( $index + 1 ); ?>">
					<span class="content-storytelling__tape" aria-hidden="true"></span>
					<?php
					if ( $photo_id > 0 ) {
						echo wp_get_attachment_image(
							$photo_id,
							'medium_large',
							false,
							array(
								'class'    => 'content-storytelling__photo',
								'alt'      => '',
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
					} else {
						echo '<span class="content-storytelling__photo content-storytelling__photo--placeholder"></span>';
					}
					?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
