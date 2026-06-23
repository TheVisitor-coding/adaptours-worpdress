<?php
/**
 * Bloc adaptours/cards-numbered-card — rendu serveur (enfant de cards-numbered).
 *
 * Carte numérotée : numéro (compteur CSS, décoratif), titre, description. Pas de style
 * propre : le layout est porté par le parent adaptours/cards-numbered.
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

$card_title  = (string) ( $attributes['card_title'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'cards-numbered__card' ) );
?>
<li <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<span class="cards-numbered__num" aria-hidden="true"></span>
	<?php if ( '' !== trim( $card_title ) ) : ?>
		<h3 class="cards-numbered__card-title"><?php echo esc_html( $card_title ); ?></h3>
	<?php endif; ?>
	<?php if ( '' !== trim( $description ) ) : ?>
		<p class="cards-numbered__card-desc"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
</li>
