<?php
/**
 * Bloc adaptours/itinerary-step — une étape du programme.
 *
 * Pastille « Jour N » (numérotée en CSS via le compteur de la liste parente), titre
 * bichrome, description et vignette. Rendu en <li> dans la liste du bloc parent.
 *
 * @var array    $attributes Attributs du bloc (title, title_accent, description, thumbnail_id).
 * @var string   $content    Contenu interne (non utilisé).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title        = (string) ( $attributes['title'] ?? '' );
$title_accent = (string) ( $attributes['title_accent'] ?? '' );
$description  = (string) ( $attributes['description'] ?? '' );
$thumbnail_id = (int) ( $attributes['thumbnail_id'] ?? 0 );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'itinerary__step' ) );
?>
<li <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<span class="itinerary__badge" aria-hidden="true">
		<span class="itinerary__badge-label"><?php esc_html_e( 'Jour', 'adaptours' ); ?></span>
		<span class="itinerary__badge-num"></span>
	</span>

	<div class="itinerary__content">
		<?php if ( '' !== trim( $title ) || '' !== trim( $title_accent ) ) : ?>
			<h3 class="itinerary__step-title"><?php echo adaptours_bichrome( trim( $title . ' ' . $title_accent ), $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
		<?php endif; ?>
		<?php if ( '' !== trim( $description ) ) : ?>
			<p class="itinerary__step-desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $thumbnail_id > 0 ) : ?>
		<div class="itinerary__thumb">
			<?php echo wp_get_attachment_image( $thumbnail_id, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	<?php endif; ?>
</li>
