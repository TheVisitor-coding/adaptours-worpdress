<?php
/**
 * Bloc adaptours/section-practical — infos pratiques.
 *
 * Compose : header (eyebrow + titre bichrome + description) et la grille de cartes pratiques
 * (InnerBlocks adaptours/practical-card rendus dans $content). La numérotation 01..NN est
 * gérée en CSS ; ce render.php enveloppe les cartes dans la grille et ajoute le header.
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (cartes InnerBlocks rendues).
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
$description  = (string) ( $attributes['description'] ?? '' );

$wrapper = get_block_wrapper_attributes(
	array(
		'aria-label' => esc_attr__( 'Ce qu’il faut savoir', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<div class="section-practical__inner">
		<header class="section-practical__head">
			<div class="section-practical__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="section-practical__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $title ) || '' !== trim( $title_accent ) ) : ?>
					<h2 class="section-practical__title"><?php echo adaptours_bichrome( $title, $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>
			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="section-practical__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( '' !== trim( (string) $content ) ) : ?>
			<ul class="section-practical__grid" role="list">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — cartes InnerBlocks rendues/assainies par WordPress ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
