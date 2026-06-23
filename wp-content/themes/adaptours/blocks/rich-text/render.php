<?php
/**
 * Bloc adaptours/rich-text — rendu serveur.
 *
 * Prose éditoriale : en-tête (eyebrow + titre bichrome) issu des
 * attributs, puis le corps libre (paragraphes / sous-titres / listes natifs) rendu via
 * $content et enveloppé dans .rich-text__body pour la typo éditoriale et les puces.
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (InnerBlocks rendus).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow = (string) ( $attributes['eyebrow'] ?? '' );
$part_1  = (string) ( $attributes['title_part_1'] ?? '' );
$part_2  = (string) ( $attributes['title_part_2'] ?? '' );

$has_title = '' !== trim( $part_1 . $part_2 );
$has_head  = '' !== trim( $eyebrow ) || $has_title;
$has_body  = '' !== trim( (string) $content );

if ( ! $has_head && ! $has_body ) {
	return;
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'rich-text' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="rich-text__inner">
		<?php if ( $has_head ) : ?>
			<header class="rich-text__head">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="rich-text__eyebrow eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $has_title ) : ?>
					<h2 class="rich-text__title"><?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php if ( $has_body ) : ?>
			<div class="rich-text__body">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — InnerBlocks rendus/assainis par WordPress ?>
			</div>
		<?php endif; ?>
	</div>
</section>
