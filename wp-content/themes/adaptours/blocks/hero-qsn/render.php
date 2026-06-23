<?php
/**
 * Bloc adaptours/hero-qsn — en-tête de la page Qui sommes-nous.
 *
 * Deux colonnes : texte (surtitre + titre bichrome + ligne manuscrite + description + bouton)
 * à gauche, grande photo en arche à droite. Section full-bleed (dégradé crème → pêche).
 * Si aucune photo n'est choisie, un cadre décoratif CSS prend sa place.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow     = (string) ( $attributes['eyebrow'] ?? '' );
$part_1      = (string) ( $attributes['title_part_1'] ?? '' );
$part_2      = (string) ( $attributes['title_part_2'] ?? '' );
$script_line = (string) ( $attributes['title_script'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );
$cta_label   = (string) ( $attributes['cta_label'] ?? '' );
$cta_url     = (string) ( $attributes['cta_url'] ?? '' );
$image_id    = (int) ( $attributes['main_image'] ?? 0 );

if ( '' === trim( $cta_url ) ) {
	$cta_url = '#equipe';
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'hero-qsn' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="hero-qsn__inner">
		<div class="hero-qsn__body">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="hero-qsn__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $part_1 . $part_2 . $script_line ) ) : ?>
				<h1 class="hero-qsn__title">
					<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
						<span class="hero-qsn__title-lead">
							<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</span>
					<?php endif; ?>
					<?php if ( '' !== trim( $script_line ) ) : ?>
						<span class="hero-qsn__title-script"><?php echo esc_html( $script_line ); ?></span>
					<?php endif; ?>
				</h1>
			<?php endif; ?>

			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="hero-qsn__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $cta_label ) ) : ?>
				<div class="hero-qsn__cta">
					<a class="button button--primary" href="<?php echo esc_url( $cta_url ); ?>">
						<?php echo esc_html( $cta_label ); ?>
						<span class="hero-qsn__cta-arrow" aria-hidden="true">↓</span>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<figure class="hero-qsn__media<?php echo $image_id > 0 ? '' : ' hero-qsn__media--placeholder'; ?>">
			<?php
			if ( $image_id > 0 ) {
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'    => 'hero-qsn__img',
						'alt'      => '',
						'loading'  => 'eager',
						'decoding' => 'async',
						'sizes'    => '(max-width: 768px) 100vw, 533px',
					)
				);
			}
			?>
		</figure>
	</div>
</section>
