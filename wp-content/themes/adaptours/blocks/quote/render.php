<?php
/**
 * Bloc adaptours/quote — rendu serveur.
 *
 * Citation centrée : guillemet décoratif (CSS), texte de citation
 * (un extrait optionnel en orange via adaptours_bichrome) et attribution. Texte libre,
 * sans lien avec le CPT avis.
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

$quote  = (string) ( $attributes['quote'] ?? '' );
$accent = (string) ( $attributes['quote_accent'] ?? '' );
$author = (string) ( $attributes['author'] ?? '' );

if ( '' === trim( $quote ) ) {
	return;
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'quote' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<figure class="quote__inner">
		<span class="quote__mark" aria-hidden="true">&ldquo;</span>
		<blockquote class="quote__text">
			<p><?php echo adaptours_bichrome( $quote, $accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		</blockquote>
		<?php if ( '' !== trim( $author ) ) : ?>
			<figcaption class="quote__author"><?php echo esc_html( $author ); ?></figcaption>
		<?php endif; ?>
	</figure>
</section>
