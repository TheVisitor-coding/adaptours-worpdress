<?php
/**
 * Bloc adaptours/practical-card — une carte d'info pratique.
 *
 * Icône (liste fermée, adaptours_icon_svg), numéro 01..NN (compteur CSS de la grille
 * parente), titre et description. Rendu en <li> dans la grille du bloc parent.
 *
 * @var array    $attributes Attributs (icon, card_title, description).
 * @var string   $content    Contenu interne (non utilisé).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon       = (string) ( $attributes['icon'] ?? '' );
$card_title = (string) ( $attributes['card_title'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );

$icon_svg = '' !== $icon ? adaptours_icon_svg( $icon ) : '';

$wrapper = get_block_wrapper_attributes( array( 'class' => 'section-practical__card' ) );
?>
<li <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="section-practical__card-top">
		<span class="section-practical__card-icon" aria-hidden="true"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<span class="section-practical__card-num" aria-hidden="true"></span>
	</div>

	<?php if ( '' !== trim( $card_title ) ) : ?>
		<h3 class="section-practical__card-title"><?php echo esc_html( $card_title ); ?></h3>
	<?php endif; ?>
	<?php if ( '' !== trim( $description ) ) : ?>
		<p class="section-practical__card-desc"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
</li>
