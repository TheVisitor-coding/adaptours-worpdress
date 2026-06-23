<?php
/**
 * Bloc adaptours/page-header — rendu serveur.
 *
 * En-tête de page modulaire : eyebrow + titre bichrome + description
 * (italique/lien) + 2 boutons. Fond dégradé full-bleed (le décor est en CSS).
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

$eyebrow     = (string) ( $attributes['eyebrow'] ?? '' );
$part_1      = (string) ( $attributes['title_part_1'] ?? '' );
$part_2      = (string) ( $attributes['title_part_2'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );

$cta_primary_label   = (string) ( $attributes['cta_primary_label'] ?? '' );
$cta_primary_url     = (string) ( $attributes['cta_primary_url'] ?? '' );
$cta_secondary_label = (string) ( $attributes['cta_secondary_label'] ?? '' );
$cta_secondary_url   = (string) ( $attributes['cta_secondary_url'] ?? '' );

$has_title = '' !== trim( $part_1 . $part_2 );
$has_cta   = '' !== trim( $cta_primary_label ) || '' !== trim( $cta_secondary_label );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'page-header' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="page-header__inner">
		<?php if ( '' !== trim( $eyebrow ) ) : ?>
			<p class="page-header__eyebrow eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $has_title ) : ?>
			<h1 class="page-header__title">
				<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</h1>
		<?php endif; ?>

		<?php if ( '' !== trim( wp_strip_all_tags( $description ) ) ) : ?>
			<p class="page-header__desc"><?php echo wp_kses_post( $description ); ?></p>
		<?php endif; ?>

		<?php if ( $has_cta ) : ?>
			<div class="page-header__cta">
				<?php if ( '' !== trim( $cta_primary_label ) ) : ?>
					<a class="button button--primary" href="<?php echo esc_url( '' !== trim( $cta_primary_url ) ? $cta_primary_url : '#' ); ?>">
						<?php echo esc_html( $cta_primary_label ); ?>
					</a>
				<?php endif; ?>
				<?php if ( '' !== trim( $cta_secondary_label ) ) : ?>
					<a class="button button--secondary" href="<?php echo esc_url( '' !== trim( $cta_secondary_url ) ? $cta_secondary_url : '#' ); ?>">
						<?php echo esc_html( $cta_secondary_label ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
