<?php
/**
 * Bloc adaptours/hero-devis — en-tête de la page Devis.
 *
 * Contenu éditable : surtitre, titre bichrome, description, photo de fond. Les 3 trust
 * points et les tirets décoratifs du surtitre sont figés.
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
$image_id    = (int) ( $attributes['hero_image'] ?? 0 );

// Photo de fond : média choisi, sinon asset thème par défaut (à fournir par la cliente).
$image_html = '';
if ( $image_id > 0 ) {
	$image_html = wp_get_attachment_image(
		$image_id,
		'full',
		false,
		array(
			'class'   => 'hero-devis__img',
			'alt'     => '',
			'loading' => 'eager',
		)
	);
}
if ( '' === $image_html ) {
	$default = get_theme_file_uri( 'assets/img/devis-hero-default.jpg' );
	$image_html = '<img class="hero-devis__img" src="' . esc_url( $default ) . '" alt="" />';
}

// 3 trust points figés (icône décorative + libellé). SVG inline, style Lucide, currentColor.
$trust = array(
	array(
		'label' => __( 'Réponse sous 48h', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>',
	),
	array(
		'label' => __( 'Gratuit, sans engagement', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"></circle><polyline points="8.5 12 11 14.5 15.5 9.5"></polyline></svg>',
	),
	array(
		'label' => __( 'Données protégées', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l7 3v5c0 4.2-2.8 7.4-7 8.6-4.2-1.2-7-4.4-7-8.6V6l7-3z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>',
	),
);

$wrapper = get_block_wrapper_attributes( array( 'class' => 'hero-devis' ) );
?>
<header <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="hero-devis__media" aria-hidden="true">
		<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput -- markup d'image contrôlé. ?>
	</div>

	<div class="hero-devis__inner">
		<?php if ( '' !== trim( $eyebrow ) ) : ?>
			<p class="hero-devis__eyebrow"><span class="hero-devis__eyebrow-text"><?php echo esc_html( $eyebrow ); ?></span></p>
		<?php endif; ?>

		<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
			<h1 class="hero-devis__title">
				<?php echo adaptours_bichrome_parts( $part_1, $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</h1>
		<?php endif; ?>

		<?php if ( '' !== trim( $description ) ) : ?>
			<p class="hero-devis__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<ul class="hero-devis__trust">
			<?php foreach ( $trust as $item ) : ?>
				<li class="hero-devis__trust-item">
					<span class="hero-devis__trust-icon">
						<?php echo wp_kses( $item['svg'], adaptours_svg_allowed_tags() ); ?>
					</span>
					<?php echo esc_html( $item['label'] ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</header>
