<?php
/**
 * Bloc adaptours/cards-numbered — rendu serveur (parent InnerBlocks).
 *
 * En-tête (eyebrow + titre bichrome + description) issu des attributs, puis les cartes
 * (blocs enfants adaptours/cards-numbered-card) rendues via $content et enveloppées dans
 * une <ol> pour la numérotation 01..NN (compteur CSS).
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

$eyebrow     = (string) ( $attributes['eyebrow'] ?? '' );
$part_1      = (string) ( $attributes['title_part_1'] ?? '' );
$part_2      = (string) ( $attributes['title_part_2'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );

$has_title = '' !== trim( $part_1 . $part_2 );
$has_cards = '' !== trim( (string) $content );

if ( ! $has_cards ) {
	return;
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'cards-numbered' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="cards-numbered__inner">
		<header class="cards-numbered__head">
			<div class="cards-numbered__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="cards-numbered__eyebrow eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $has_title ) : ?>
					<h2 class="cards-numbered__title"><?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>
			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="cards-numbered__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<ol class="cards-numbered__grid">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — cartes InnerBlocks rendues/assainies par WordPress ?>
		</ol>
	</div>
</section>
