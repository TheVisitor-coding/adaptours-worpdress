<?php
/**
 * Bloc adaptours/avis-spotlight — témoignage mis en avant (single destination).
 *
 * Section sombre dérivée du champ `avis_mis_en_avant` de la destination courante (1 avis
 * max). Le bloc n'a aucun attribut : tout son contenu vient du CPT avis lié.
 *
 * - Polaroïd = `photo_voyageur` (placeholder CSS si vide).
 * - Étoiles / nom / méta = `note` / `nom_voyageur` / `contexte` de l'avis.
 * - Si le champ est vide ou pointe un avis dépublié, le bloc est masqué.
 *
 * @var array    $attributes Attributs du bloc (aucun).
 * @var string   $content    Contenu interne (non utilisé).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$destination_id = get_the_ID();
if ( ! $destination_id ) {
	$destination_id = get_queried_object_id();
}

$avis_id = adaptours_get_spotlight_avis_id( $destination_id );
if ( ! $avis_id ) {
	return; // Aucun avis sélectionné/publié : section masquée silencieusement.
}

$avis      = adaptours_get_avis_card_args( $avis_id );
$has_photo = (int) $avis['photo_id'] > 0;

// Avatar : initiale du nom (la photo réelle est dans le polaroïd).
$initiale = '' !== trim( (string) $avis['nom'] )
	? mb_strtoupper( mb_substr( trim( (string) $avis['nom'] ), 0, 1 ) )
	: '';

$wrapper = get_block_wrapper_attributes(
	array(
		'aria-label' => esc_attr__( 'Témoignage en avant', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<span class="avis-spotlight__decor avis-spotlight__decor--warm" aria-hidden="true"></span>
	<span class="avis-spotlight__decor avis-spotlight__decor--cool" aria-hidden="true"></span>

	<div class="avis-spotlight__inner">
		<figure class="avis-spotlight__media<?php echo $has_photo ? '' : ' avis-spotlight__media--placeholder'; ?>">
			<?php
			if ( $has_photo ) {
				echo wp_get_attachment_image(
					(int) $avis['photo_id'],
					'large',
					false,
					array(
						'class'    => 'avis-spotlight__photo',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'alt'      => '',
					)
				);
			} else {
				echo '<span class="avis-spotlight__photo-label" aria-hidden="true">' . esc_html__( 'PHOTO', 'adaptours' ) . '</span>';
			}
			?>
		</figure>

		<figure class="avis-spotlight__quote-block">
			<span class="avis-spotlight__quote-mark" aria-hidden="true">&ldquo;</span>

			<?php if ( '' !== trim( (string) $avis['temoignage'] ) ) : ?>
				<blockquote class="avis-spotlight__quote">
					<p><?php echo esc_html( $avis['temoignage'] ); ?></p>
				</blockquote>
			<?php endif; ?>

			<?php echo adaptours_avis_stars_markup( $avis['note'], 'avis-spotlight__stars' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

			<figcaption class="avis-spotlight__author">
				<span class="avis-spotlight__avatar" aria-hidden="true"><?php echo esc_html( $initiale ); ?></span>
				<span class="avis-spotlight__identity">
					<span class="avis-spotlight__name"><?php echo esc_html( $avis['nom'] ); ?></span>
					<?php if ( '' !== trim( (string) $avis['contexte'] ) ) : ?>
						<span class="avis-spotlight__meta"><?php echo esc_html( $avis['contexte'] ); ?></span>
					<?php endif; ?>
				</span>
			</figcaption>
		</figure>
	</div>
</section>
