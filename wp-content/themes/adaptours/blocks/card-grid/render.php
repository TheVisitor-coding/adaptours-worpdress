<?php
/**
 * Bloc adaptours/card-grid — rendu serveur (parent InnerBlocks).
 *
 * En-tête centré (eyebrow + titre bichrome) issu des attributs, puis les cartes (blocs
 * enfants adaptours/card-grid-card) rendues via $content et enveloppées dans une grille
 * de 2 / 3 / 4 colonnes (attribut columns).
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

$eyebrow = (string) ( $attributes['eyebrow'] ?? '' );
$part_1  = (string) ( $attributes['title_part_1'] ?? '' );
$part_2  = (string) ( $attributes['title_part_2'] ?? '' );
$columns = (int) ( $attributes['columns'] ?? 3 );
$columns = ( $columns >= 2 && $columns <= 4 ) ? $columns : 3;

$has_title = '' !== trim( $part_1 . $part_2 );
$has_cards = '' !== trim( (string) $content );

if ( ! $has_cards ) {
	return;
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'card-grid is-cols-' . $columns,
		'style' => '--adaptours-card-grid-cols:' . $columns . ';',
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="card-grid__inner">
		<?php if ( '' !== trim( $eyebrow ) || $has_title ) : ?>
			<header class="card-grid__head">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="card-grid__eyebrow eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $has_title ) : ?>
					<h2 class="card-grid__title"><?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<ul class="card-grid__grid" role="list">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — cartes InnerBlocks rendues/assainies par WordPress ?>
		</ul>
	</div>
</section>
