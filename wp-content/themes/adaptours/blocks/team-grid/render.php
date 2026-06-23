<?php
/**
 * Bloc adaptours/team-grid — section équipe (parent InnerBlocks).
 *
 * En-tête (surtitre + titre bichrome + description) issu des attributs, grille de membres
 * (blocs enfants adaptours/team-grid-member rendus via $content), puis bande basse
 * « on recrute ? » + bouton. Ancre #equipe (cible du bouton du hero).
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (membres InnerBlocks rendus).
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
$band_text   = (string) ( $attributes['band_text'] ?? '' );
$cta_label   = (string) ( $attributes['cta_label'] ?? '' );
$cta_url     = (string) ( $attributes['cta_url'] ?? '' );

if ( '' === trim( $cta_url ) ) {
	$cta_url = '#recrutement';
}

$has_title = '' !== trim( $part_1 . $part_2 );

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'team-grid',
		'id'    => 'equipe',
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="team-grid__inner">
		<header class="team-grid__head">
			<div class="team-grid__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="team-grid__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $has_title ) : ?>
					<h2 class="team-grid__title"><?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>
			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="team-grid__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<ul class="team-grid__grid">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — membres InnerBlocks rendus/assainis par WordPress ?>
		</ul>

		<?php if ( '' !== trim( $band_text ) || '' !== trim( $cta_label ) ) : ?>
			<div class="team-grid__band">
				<?php if ( '' !== trim( $band_text ) ) : ?>
					<p class="team-grid__band-text"><?php echo esc_html( $band_text ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $cta_label ) ) : ?>
					<a class="button button--secondary team-grid__band-cta" href="<?php echo esc_url( $cta_url ); ?>">
						<?php echo esc_html( $cta_label ); ?>
						<span aria-hidden="true">→</span>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
