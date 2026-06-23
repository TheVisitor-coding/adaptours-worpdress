<?php
/**
 * Card avis — source unique du rendu d'un témoignage.
 *
 * Réutilisée par le bloc adaptours/avis-grid (grande card « avis du mois » + mini-cards) ;
 * avis-spotlight a son propre layout et ne réutilise pas ce partial. Le partial ne lit ni
 * le post global ni get_field() : il reçoit un tableau d'arguments normalisé, préparé par
 * adaptours_get_avis_card_args(). Appel :
 *   get_template_part( 'template-parts/card-avis', null, $args )
 *
 * Les variantes `featured` (sombre, gros guillemet) et `mini` (claire, badge destination ·
 * mois) partagent les mêmes données et la même structure figure/blockquote/figcaption.
 *
 * @param array $args {
 *     @type string $titre_admin       Titre admin du post (jamais affiché front).
 *     @type int    $note              Note 1..5 (rendue en étoiles), défaut 5.
 *     @type string $temoignage        Texte de l'avis (échappé, pas de richtext).
 *     @type string $nom               Nom affiché (« Claire M. »).
 *     @type string $contexte          Court texte sous le nom.
 *     @type int    $photo_id          ID image voyageur (0 => placeholder « PHOTO »).
 *     @type string $destination_label Pays/destination liée (badge mini, '' => masqué).
 *     @type string $mois_label        Mois « mm/aaaa » (badge mini, '' => masqué).
 *     @type string $variant           'featured' | 'mini' (défaut 'mini').
 *     @type int    $heading_level     Niveau du titre porté par le nom (1..6), défaut 3.
 * }
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'titre_admin'       => '',
		'note'              => 5,
		'temoignage'        => '',
		'nom'               => '',
		'contexte'          => '',
		'photo_id'          => 0,
		'destination_label' => '',
		'mois_label'        => '',
		'variant'           => 'mini',
		'heading_level'     => 3,
	)
);

$variant      = in_array( $args['variant'], array( 'featured', 'mini' ), true ) ? $args['variant'] : 'mini';
$is_featured  = ( 'featured' === $variant );

// Niveau de titre paramétrable, borné à h1..h6 (défaut h3).
$level = (int) $args['heading_level'];
$level = ( $level >= 1 && $level <= 6 ) ? $level : 3;
$tag   = 'h' . $level;

// Étoiles : markup accessible partagé (helper, scopé via la classe BEM).
$stars_html = adaptours_avis_stars_markup( $args['note'], 'card-avis__stars' );

$has_photo = (int) $args['photo_id'] > 0;

// Badge mini : « destination · mois » — segments présents uniquement.
$badge_parts = array_filter(
	array( $args['destination_label'], $args['mois_label'] ),
	static function ( $v ) {
		return '' !== trim( (string) $v );
	}
);
?>
<figure class="card-avis card-avis--<?php echo esc_attr( $variant ); ?>">
	<?php if ( $is_featured ) : ?>
		<span class="card-avis__quote" aria-hidden="true">&ldquo;</span>
		<p class="card-avis__eyebrow"><?php esc_html_e( '— Avis du mois', 'adaptours' ); ?></p>
	<?php else : ?>
		<div class="card-avis__head">
			<?php echo $stars_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( ! empty( $badge_parts ) ) : ?>
				<p class="card-avis__badge"><?php echo esc_html( implode( ' · ', $badge_parts ) ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== trim( (string) $args['temoignage'] ) ) : ?>
		<blockquote class="card-avis__quote-text">
			<p><?php echo esc_html( $args['temoignage'] ); ?></p>
		</blockquote>
	<?php endif; ?>

	<figcaption class="card-avis__footer">
		<span class="card-avis__avatar<?php echo $has_photo ? '' : ' card-avis__avatar--placeholder'; ?>">
			<?php
			if ( $has_photo ) {
				echo wp_get_attachment_image(
					(int) $args['photo_id'],
					'thumbnail',
					false,
					array(
						'class'    => 'card-avis__avatar-img',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'alt'      => '',
					)
				);
			} else {
				echo '<span class="card-avis__avatar-label" aria-hidden="true">' . esc_html__( 'PHOTO', 'adaptours' ) . '</span>';
			}
			?>
		</span>

		<span class="card-avis__author">
			<<?php echo esc_html( $tag ); ?> class="card-avis__name"><?php echo esc_html( $args['nom'] ); ?></<?php echo esc_html( $tag ); ?>>
			<?php if ( '' !== trim( (string) $args['contexte'] ) ) : ?>
				<span class="card-avis__context"><?php echo esc_html( $args['contexte'] ); ?></span>
			<?php endif; ?>
		</span>

		<?php if ( $is_featured ) : ?>
			<?php echo $stars_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>
	</figcaption>
</figure>
