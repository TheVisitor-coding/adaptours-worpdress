<?php
/**
 * Bloc adaptours/section-accessibility — conditions d'accessibilité.
 *
 * Compose : header (eyebrow + titre bichrome + intro à gauche) et grille de 4 cartes
 * (icône + titre + description). Intro et cartes viennent du CPT destination via
 * adaptours_get_destination_accessibility_args() ; le bloc reste agnostique.
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

$eyebrow      = (string) ( $attributes['eyebrow'] ?? '' );
$title        = (string) ( $attributes['title'] ?? '' );
$title_accent = (string) ( $attributes['title_accent'] ?? '' );

// ID destination : post courant, ou aperçu éditeur (param GET `post_id`).
$destination_id = get_the_ID();
if ( ! $destination_id && isset( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$destination_id = (int) $_GET['post_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$data = adaptours_get_destination_accessibility_args( (int) $destination_id );

$wrapper = get_block_wrapper_attributes(
	array(
		'aria-label' => esc_attr__( 'Accessibilité', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<div class="section-accessibility__inner">
		<div class="section-accessibility__intro">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="section-accessibility__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( $title ) || '' !== trim( $title_accent ) ) : ?>
				<h2 class="section-accessibility__title"><?php echo adaptours_bichrome( $title, $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php endif; ?>
			<?php if ( '' !== trim( $data['intro'] ) ) : ?>
				<p class="section-accessibility__lead"><?php echo esc_html( $data['intro'] ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $data['cards'] ) ) : ?>
			<ul class="section-accessibility__cards" role="list">
				<?php foreach ( $data['cards'] as $card ) : ?>
					<li class="section-accessibility__card">
						<?php
						$icon_svg = '' !== $card['icon'] ? adaptours_icon_svg( $card['icon'] ) : '';
						if ( '' !== $icon_svg ) :
							?>
							<span class="section-accessibility__card-icon" aria-hidden="true"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<?php endif; ?>
						<div class="section-accessibility__card-body">
							<?php if ( '' !== trim( $card['title'] ) ) : ?>
								<p class="section-accessibility__card-title"><?php echo esc_html( $card['title'] ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== trim( $card['description'] ) ) : ?>
								<p class="section-accessibility__card-desc"><?php echo esc_html( $card['description'] ); ?></p>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
