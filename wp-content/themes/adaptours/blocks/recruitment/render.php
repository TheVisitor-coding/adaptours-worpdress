<?php
/**
 * Bloc adaptours/recruitment — section recrutement (parent InnerBlocks).
 *
 * Colonne gauche : surtitre + titre bichrome + texte + carte « envoyez votre CV à » (email
 * en mailto). Colonne droite : carte des conditions (titre bichrome + sous-titre + liste des
 * conditions, blocs enfants adaptours/recruitment-condition rendus via $content, numérotés
 * 01..NN en CSS). Ancre #recrutement.
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (conditions InnerBlocks rendues).
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
$cv_email    = trim( (string) ( $attributes['cv_email'] ?? '' ) );

$cond_eyebrow  = (string) ( $attributes['conditions_eyebrow'] ?? '' );
$cond_title_1  = (string) ( $attributes['conditions_title_1'] ?? '' );
$cond_title_2  = (string) ( $attributes['conditions_title_2'] ?? '' );
$cond_subtitle = (string) ( $attributes['conditions_subtitle'] ?? '' );

// Repli : l'adresse e-mail générale du site (réglages du thème).
if ( '' === $cv_email && function_exists( 'adaptours_get_option' ) ) {
	$cv_email = trim( (string) adaptours_get_option( 'email' ) );
}

$has_title      = '' !== trim( $part_1 . $part_2 );
$has_cond_title = '' !== trim( $cond_title_1 . $cond_title_2 );

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'recruitment',
		'id'    => 'recrutement',
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="recruitment__inner">
		<div class="recruitment__intro">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="recruitment__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $has_title ) : ?>
				<h2 class="recruitment__title"><?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php endif; ?>
			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="recruitment__text"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $cv_email ) : ?>
				<div class="recruitment__cv">
					<span class="recruitment__cv-tab" aria-hidden="true"></span>
					<p class="recruitment__cv-label"><?php esc_html_e( 'Merci d’envoyer votre CV à', 'adaptours' ); ?></p>
					<a class="recruitment__cv-email" href="<?php echo esc_url( 'mailto:' . $cv_email ); ?>"><?php echo esc_html( $cv_email ); ?></a>
				</div>
			<?php endif; ?>
		</div>

		<div class="recruitment__conditions-card">
			<?php if ( '' !== trim( $cond_eyebrow ) ) : ?>
				<p class="recruitment__eyebrow"><?php echo esc_html( $cond_eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $has_cond_title ) : ?>
				<h3 class="recruitment__conditions-title"><?php echo adaptours_bichrome( trim( $cond_title_1 . ' ' . $cond_title_2 ), $cond_title_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
			<?php endif; ?>
			<?php if ( '' !== trim( $cond_subtitle ) ) : ?>
				<p class="recruitment__conditions-subtitle"><?php echo esc_html( $cond_subtitle ); ?></p>
			<?php endif; ?>

			<ol class="recruitment__conditions">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — conditions InnerBlocks rendues/assainies par WordPress ?>
			</ol>
		</div>
	</div>
</section>
