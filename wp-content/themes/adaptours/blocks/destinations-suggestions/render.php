<?php
/**
 * Bloc adaptours/destinations-suggestions — « Vous aimerez aussi ».
 *
 * Compose : header (eyebrow + titre bichrome + description + bouton) et grille de cards. Les
 * destinations viennent de la relation ACF `suggestions` via adaptours_get_destination_suggestions().
 * Les cards réutilisent le partial template-parts/card-destination.php en variante « overlay ».
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

$eyebrow      = (string) ( $attributes['eyebrow'] ?? '' );
$title        = (string) ( $attributes['title'] ?? '' );
$title_accent = (string) ( $attributes['title_accent'] ?? '' );
$description  = (string) ( $attributes['description'] ?? '' );
$cta_label    = (string) ( $attributes['cta_label'] ?? '' );
$cta_url      = (string) ( $attributes['cta_url'] ?? '' );
if ( '' === trim( $cta_url ) ) {
	$cta_url = (string) get_post_type_archive_link( 'destination' );
}

// ID destination : post courant, ou aperçu éditeur (param GET `post_id`).
$destination_id = get_the_ID();
if ( ! $destination_id && isset( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$destination_id = (int) $_GET['post_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$ids = adaptours_get_destination_suggestions( (int) $destination_id );

// Aucune suggestion : on masque la section.
if ( empty( $ids ) ) {
	return;
}

$wrapper = get_block_wrapper_attributes(
	array(
		'aria-label' => esc_attr__( 'Destinations suggérées', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<div class="destinations-suggestions__inner">
		<header class="destinations-suggestions__head">
			<div class="destinations-suggestions__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="destinations-suggestions__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $title ) || '' !== trim( $title_accent ) ) : ?>
					<h2 class="destinations-suggestions__title"><?php echo adaptours_bichrome( trim( $title . ' ' . $title_accent ), $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>

			<div class="destinations-suggestions__aside">
				<?php if ( '' !== trim( $description ) ) : ?>
					<p class="destinations-suggestions__desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $cta_label ) && '' !== trim( $cta_url ) ) : ?>
					<a class="destinations-suggestions__cta" href="<?php echo esc_url( $cta_url ); ?>">
						<?php echo esc_html( $cta_label ); ?>
						<span aria-hidden="true"> →</span>
					</a>
				<?php endif; ?>
			</div>
		</header>

		<div class="destinations-suggestions__grid">
			<?php
			foreach ( $ids as $id ) {
				$card_args            = adaptours_get_destination_card_args( $id );
				$card_args['variant'] = 'overlay';
				get_template_part( 'template-parts/card-destination', null, $card_args );
			}
			?>
		</div>
	</div>
</section>
