<?php
/**
 * Bloc adaptours/dual-cta — deux cartes d'appel à l'action.
 *
 * Carte 1 (sombre) : voyager → devis. Carte 2 (pêche) : nous rejoindre → e-mail des CV.
 * Remplace le prefooter sur la page Qui sommes-nous. Nombre de cartes figé à 2.
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

// Carte 1 (devis)
$c1_eyebrow = (string) ( $attributes['card_1_eyebrow'] ?? '' );
$c1_t1      = (string) ( $attributes['card_1_title_1'] ?? '' );
$c1_t2      = (string) ( $attributes['card_1_title_2'] ?? '' );
$c1_desc    = (string) ( $attributes['card_1_description'] ?? '' );
$c1_label   = (string) ( $attributes['card_1_cta_label'] ?? '' );
$c1_url     = trim( (string) ( $attributes['card_1_cta_url'] ?? '' ) );
if ( '' === $c1_url ) {
	$c1_url = home_url( '/devis/' );
}

// Carte 2 (recrutement / e-mail)
$c2_eyebrow = (string) ( $attributes['card_2_eyebrow'] ?? '' );
$c2_t1      = (string) ( $attributes['card_2_title_1'] ?? '' );
$c2_t2      = (string) ( $attributes['card_2_title_2'] ?? '' );
$c2_desc    = (string) ( $attributes['card_2_description'] ?? '' );
$c2_label   = trim( (string) ( $attributes['card_2_cta_label'] ?? '' ) );
$c2_url     = trim( (string) ( $attributes['card_2_cta_url'] ?? '' ) );

// Repli : e-mail général du site, rendu en mailto:.
$site_email = function_exists( 'adaptours_get_option' ) ? trim( (string) adaptours_get_option( 'email' ) ) : '';
if ( '' === $c2_url ) {
	$c2_url = '' !== $site_email ? 'mailto:' . $site_email : '';
}
if ( '' === $c2_label ) {
	$c2_label = '' !== $site_email ? $site_email : __( 'Nous écrire', 'adaptours' );
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'dual-cta' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="dual-cta__inner">
		<div class="dual-cta__card dual-cta__card--dark">
			<?php if ( '' !== trim( $c1_eyebrow ) ) : ?>
				<p class="dual-cta__eyebrow"><?php echo esc_html( $c1_eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( $c1_t1 . $c1_t2 ) ) : ?>
				<h2 class="dual-cta__title"><?php echo adaptours_bichrome( trim( $c1_t1 . ' ' . $c1_t2 ), $c1_t2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php endif; ?>
			<?php if ( '' !== trim( $c1_desc ) ) : ?>
				<p class="dual-cta__desc"><?php echo esc_html( $c1_desc ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( $c1_label ) ) : ?>
				<a class="button button--primary dual-cta__cta" href="<?php echo esc_url( $c1_url ); ?>">
					<?php echo esc_html( $c1_label ); ?>
					<span aria-hidden="true">→</span>
				</a>
			<?php endif; ?>
		</div>

		<div class="dual-cta__card dual-cta__card--peach">
			<?php if ( '' !== trim( $c2_eyebrow ) ) : ?>
				<p class="dual-cta__eyebrow"><?php echo esc_html( $c2_eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( $c2_t1 . $c2_t2 ) ) : ?>
				<h2 class="dual-cta__title"><?php echo adaptours_bichrome( trim( $c2_t1 . ' ' . $c2_t2 ), $c2_t2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php endif; ?>
			<?php if ( '' !== trim( $c2_desc ) ) : ?>
				<p class="dual-cta__desc"><?php echo esc_html( $c2_desc ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( $c2_label ) && '' !== $c2_url ) : ?>
				<a class="button dual-cta__cta dual-cta__cta--ink" href="<?php echo esc_url( $c2_url ); ?>">
					<?php echo esc_html( $c2_label ); ?>
					<span aria-hidden="true">→</span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
