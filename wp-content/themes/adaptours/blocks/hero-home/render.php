<?php
/**
 * Bloc adaptours/hero-home — en-tête de la page d'accueil.
 *
 * Contenu éditable : surtitre, titre bichrome (+ ligne manuscrite), description, 2 CTA.
 * Figé : bande de réassurance (4 badges) et collage décoratif (CSS).
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

$cta_primary_label   = (string) ( $attributes['cta_primary_label'] ?? '' );
$cta_primary_url     = (string) ( $attributes['cta_primary_url'] ?? '' );
$cta_secondary_label = (string) ( $attributes['cta_secondary_label'] ?? '' );
$cta_secondary_url   = (string) ( $attributes['cta_secondary_url'] ?? '' );

// Repli d'URL utile (le bouton reste fonctionnel même sans saisie cliente).
$archive_url = get_post_type_archive_link( 'destination' );
if ( '' === trim( $cta_primary_url ) ) {
	$cta_primary_url = home_url( '/devis/' );
}
if ( '' === trim( $cta_secondary_url ) ) {
	$cta_secondary_url = $archive_url ? $archive_url : home_url( '/destinations/' );
}

// Bande de réassurance figée (4 badges, icônes SVG inline).
$trust = array(
	array(
		'label' => __( '15 ans d’expérience', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="9" r="6"></circle><path d="M9 14.5 8 22l4-2.5 4 2.5-1-7.5"></path></svg>',
	),
	array(
		'label' => __( 'Hébergements vérifiés', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l7 3v5c0 4.2-2.8 7.4-7 8.6-4.2-1.2-7-4.4-7-8.6V6l7-3z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>',
	),
	array(
		'label' => __( 'Matériel médical inclus', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="6" width="18" height="13" rx="2"></rect><line x1="12" y1="10" x2="12" y2="15"></line><line x1="9.5" y1="12.5" x2="14.5" y2="12.5"></line></svg>',
	),
	array(
		'label' => __( '100% sur mesure', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l2.2 5.5L20 9.8l-4.2 3.7L17 20l-5-3-5 3 1.2-6.5L4 9.8l5.8-1.3z"></path></svg>',
	),
);

$wrapper = get_block_wrapper_attributes( array( 'class' => 'hero-home' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="hero-home__decor" aria-hidden="true">
		<span class="hero-home__polaroid hero-home__polaroid--1"></span>
		<span class="hero-home__polaroid hero-home__polaroid--2"></span>
		<span class="hero-home__polaroid hero-home__polaroid--3"></span>
		<span class="hero-home__polaroid hero-home__polaroid--4"></span>
	</div>

	<div class="hero-home__inner">
		<?php if ( '' !== trim( $eyebrow ) ) : ?>
			<p class="hero-home__eyebrow"><span class="hero-home__eyebrow-text"><?php echo esc_html( $eyebrow ); ?></span></p>
		<?php endif; ?>

		<?php if ( '' !== trim( $part_1 . $part_2 . $script_line ) ) : ?>
			<h1 class="hero-home__title">
				<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
					<span class="hero-home__title-lead">
						<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
				<?php endif; ?>
				<?php if ( '' !== trim( $script_line ) ) : ?>
					<span class="hero-home__title-script"><?php echo esc_html( $script_line ); ?></span>
				<?php endif; ?>
			</h1>
		<?php endif; ?>

		<?php if ( '' !== trim( $description ) ) : ?>
			<p class="hero-home__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== trim( $cta_primary_label ) || '' !== trim( $cta_secondary_label ) ) : ?>
			<div class="hero-home__cta">
				<?php if ( '' !== trim( $cta_primary_label ) ) : ?>
					<a class="button button--primary" href="<?php echo esc_url( $cta_primary_url ); ?>">
						<?php echo esc_html( $cta_primary_label ); ?>
						<span class="hero-home__cta-arrow" aria-hidden="true">→</span>
					</a>
				<?php endif; ?>
				<?php if ( '' !== trim( $cta_secondary_label ) ) : ?>
					<a class="button button--secondary" href="<?php echo esc_url( $cta_secondary_url ); ?>">
						<?php echo esc_html( $cta_secondary_label ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<ul class="hero-home__trust">
			<?php foreach ( $trust as $item ) : ?>
				<li class="hero-home__trust-item">
					<span class="hero-home__trust-icon"><?php echo wp_kses( $item['svg'], adaptours_svg_allowed_tags() ); ?></span>
					<span class="hero-home__trust-label"><?php echo esc_html( $item['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
