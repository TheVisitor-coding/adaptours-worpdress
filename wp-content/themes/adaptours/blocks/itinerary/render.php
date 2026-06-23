<?php
/**
 * Bloc adaptours/itinerary — programme jour par jour.
 *
 * Compose : header (eyebrow + titre bichrome + description) et la liste d'étapes
 * (InnerBlocks adaptours/itinerary-step rendus dans $content). La numérotation « Jour N »
 * est gérée en CSS ; la dernière étape porte une pastille pleine.
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (étapes InnerBlocks rendues).
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
		'aria-label' => esc_attr__( 'Programme du voyage', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<div class="itinerary__inner">
		<header class="itinerary__head">
			<div class="itinerary__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="itinerary__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $title ) || '' !== trim( $title_accent ) ) : ?>
					<h2 class="itinerary__title"><?php echo adaptours_bichrome( $title, $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>
			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="itinerary__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( '' !== trim( (string) $content ) ) : ?>
			<ol class="itinerary__list">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — étapes InnerBlocks rendues/assainies par WordPress ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
