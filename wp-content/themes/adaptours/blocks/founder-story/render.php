<?php
/**
 * Bloc adaptours/founder-story — récit de la fondatrice (Caroline).
 *
 * Cousin éditorial de content-storytelling, enrichi d'une citation et d'une signature.
 * Éditable : surtitre (facultatif), titre bichrome, intro, citation (2 champs), conclusion,
 * signature, 3 photos. Figé : layout texte/polaroïds, rotations et ruban (CSS).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow    = (string) ( $attributes['eyebrow'] ?? '' );
$part_1     = (string) ( $attributes['title_part_1'] ?? '' );
$part_2     = (string) ( $attributes['title_part_2'] ?? '' );
$intro      = (string) ( $attributes['intro'] ?? '' );
$quote_1    = (string) ( $attributes['quote_part_1'] ?? '' );
$quote_2    = (string) ( $attributes['quote_part_2'] ?? '' );
$outro      = (string) ( $attributes['outro'] ?? '' );
$sign_name  = (string) ( $attributes['signature_name'] ?? '' );
$sign_role  = (string) ( $attributes['signature_role'] ?? '' );

$photos = array(
	(int) ( $attributes['photo_1'] ?? 0 ),
	(int) ( $attributes['photo_2'] ?? 0 ),
	(int) ( $attributes['photo_3'] ?? 0 ),
);

$wrapper = get_block_wrapper_attributes( array( 'class' => 'founder-story' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="founder-story__inner">
		<div class="founder-story__body">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="founder-story__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
				<h2 class="founder-story__title">
					<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</h2>
			<?php endif; ?>

			<?php if ( '' !== trim( $intro ) ) : ?>
				<p class="founder-story__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $quote_1 . $quote_2 ) ) : ?>
				<blockquote class="founder-story__quote">
					<p><?php echo adaptours_bichrome( trim( $quote_1 . ' ' . $quote_2 ), $quote_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</blockquote>
			<?php endif; ?>

			<?php if ( '' !== trim( $outro ) ) : ?>
				<p class="founder-story__outro"><?php echo esc_html( $outro ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $sign_name ) || '' !== trim( $sign_role ) ) : ?>
				<div class="founder-story__signature">
					<?php if ( '' !== trim( $sign_name ) ) : ?>
						<p class="founder-story__signature-name"><?php echo esc_html( $sign_name ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== trim( $sign_role ) ) : ?>
						<p class="founder-story__signature-role"><?php echo esc_html( $sign_role ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="founder-story__gallery" aria-hidden="true">
			<?php foreach ( $photos as $index => $photo_id ) : ?>
				<figure class="founder-story__polaroid founder-story__polaroid--<?php echo (int) ( $index + 1 ); ?>">
					<span class="founder-story__tape" aria-hidden="true"></span>
					<?php
					if ( $photo_id > 0 ) {
						echo wp_get_attachment_image(
							$photo_id,
							'medium_large',
							false,
							array(
								'class'    => 'founder-story__photo',
								'alt'      => '',
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
					} else {
						echo '<span class="founder-story__photo founder-story__photo--placeholder"></span>';
					}
					?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
