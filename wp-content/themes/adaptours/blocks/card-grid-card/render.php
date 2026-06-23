<?php
/**
 * Bloc adaptours/card-grid-card — rendu serveur (enfant de card-grid).
 *
 * Carte illustrée : image de fond, titre + texte superposés en bas, lien facultatif
 * (carte cliquable via stretched-link). Pas de style propre : layout porté par le parent
 * adaptours/card-grid.
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

$image_id   = (int) ( $attributes['image_id'] ?? 0 );
$alt        = (string) ( $attributes['image_alt'] ?? '' );
$card_title = (string) ( $attributes['card_title'] ?? '' );
$text       = (string) ( $attributes['text'] ?? '' );
$url        = (string) ( $attributes['url'] ?? '' );

$has_image = $image_id > 0;
$has_link  = '' !== trim( $url );
$has_title = '' !== trim( $card_title );

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'card-grid__card' . ( $has_link ? ' card-grid__card--linked' : '' ) )
);
?>
<li <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="card-grid__media<?php echo $has_image ? '' : ' card-grid__media--placeholder'; ?>">
		<?php
		if ( $has_image ) {
			echo wp_get_attachment_image(
				$image_id,
				'large',
				false,
				array(
					'class'    => 'card-grid__img',
					'alt'      => esc_attr( $alt ),
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw',
				)
			);
		}
		?>
		<span class="card-grid__scrim" aria-hidden="true"></span>

		<div class="card-grid__caption">
			<?php if ( $has_title ) : ?>
				<h3 class="card-grid__card-title">
					<?php if ( $has_link ) : ?>
						<a class="card-grid__link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $card_title ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $card_title ); ?>
					<?php endif; ?>
				</h3>
			<?php endif; ?>
			<?php if ( '' !== trim( $text ) ) : ?>
				<p class="card-grid__card-text"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</li>
