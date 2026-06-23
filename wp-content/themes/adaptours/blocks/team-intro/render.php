<?php
/**
 * Bloc adaptours/team-intro — présentation de l'équipe.
 *
 * Éditable : surtitre, titre bichrome, 2 paragraphes, bouton, visuel principal.
 * Figé : forme « arche » du visuel (CSS).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow   = (string) ( $attributes['eyebrow'] ?? '' );
$part_1    = (string) ( $attributes['title_part_1'] ?? '' );
$part_2    = (string) ( $attributes['title_part_2'] ?? '' );
$para_1    = (string) ( $attributes['paragraph_1'] ?? '' );
$para_2    = (string) ( $attributes['paragraph_2'] ?? '' );
$cta_label = (string) ( $attributes['cta_label'] ?? '' );
$cta_url   = (string) ( $attributes['cta_url'] ?? '' );
$image_id  = (int) ( $attributes['main_image'] ?? 0 );

if ( '' === trim( $cta_url ) ) {
	$cta_url = home_url( '/qui-sommes-nous/' );
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'team-intro' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="team-intro__inner">
		<figure class="team-intro__media<?php echo $image_id > 0 ? '' : ' team-intro__media--placeholder'; ?>">
			<?php
			if ( $image_id > 0 ) {
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'    => 'team-intro__img',
						'alt'      => '',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width: 768px) 100vw, 40vw',
					)
				);
			}
			?>
		</figure>

		<div class="team-intro__body">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="team-intro__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
				<h2 class="team-intro__title">
					<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</h2>
			<?php endif; ?>

			<?php if ( '' !== trim( $para_1 ) ) : ?>
				<p class="team-intro__text"><?php echo esc_html( $para_1 ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $para_2 ) ) : ?>
				<p class="team-intro__text"><?php echo esc_html( $para_2 ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $cta_label ) ) : ?>
				<a class="button button--primary team-intro__cta" href="<?php echo esc_url( $cta_url ); ?>">
					<?php echo esc_html( $cta_label ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
