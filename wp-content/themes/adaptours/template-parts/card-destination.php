<?php
/**
 * Card destination — source unique du rendu d'une card.
 *
 * Réutilisée par la grille home, l'archive et les suggestions. Le partial ne lit ni le post
 * global ni get_field() : il reçoit un tableau d'arguments normalisé, préparé par
 * adaptours_get_destination_card_args(). Appel :
 *   get_template_part( 'template-parts/card-destination', null, $args )
 *
 * Card entièrement cliquable via « stretched link » : le lien porte le titre, un ::after
 * l'étend sur toute la card (focus géré en :focus-within).
 *
 * @param array $args {
 *     @type string $titre         Titre de la destination (post_title).
 *     @type string $permalink     URL du single.
 *     @type int    $image_id      ID de l'image à la une (0 => placeholder).
 *     @type string $ville         Ville (méta, traduite).
 *     @type string $duree         Durée (méta, traduite), ex. « 10 JOURS ».
 *     @type string $prix          Prix formaté en string d'affichage, ex. « 2 490€ ».
 *     @type string $description   Accroche/extrait court ('' => non rendu).
 *     @type array  $zones         Termes zone_geographique (réservé, non affiché ici).
 *     @type bool   $coup_de_coeur Badge conditionnel.
 *     @type int    $heading_level Niveau du titre (1..6), défaut 3.
 *     @type string $variant       '' = card pleine (image + corps) ;
 *                                  'overlay' = card compacte tout-image (suggestions).
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
		'titre'         => '',
		'permalink'     => '',
		'image_id'      => 0,
		'ville'         => '',
		'duree'         => '',
		'prix'          => '',
		'description'   => '',
		'zones'         => array(),
		'coup_de_coeur' => false,
		'heading_level' => 3,
		'variant'       => '',
	)
);

$is_overlay = 'overlay' === $args['variant'];

// Niveau de titre paramétrable, borné à h1..h6 (défaut h3).
$level = (int) $args['heading_level'];
$level = ( $level >= 1 && $level <= 6 ) ? $level : 3;
$tag   = 'h' . $level;

// Sous-ligne « ville · durée » : on n'affiche que les segments présents.
$meta_parts = array_filter(
	array( $args['ville'], $args['duree'] ),
	static function ( $v ) {
		return '' !== trim( (string) $v );
	}
);

$has_image = (int) $args['image_id'] > 0;
?>
<article class="card-destination<?php echo $is_overlay ? ' card-destination--overlay' : ''; ?>">
	<div class="card-destination__media<?php echo $has_image ? '' : ' card-destination__media--placeholder'; ?>">
		<?php
		if ( $has_image ) {
			echo wp_get_attachment_image(
				(int) $args['image_id'],
				'large',
				false,
				array(
					'class'    => 'card-destination__img',
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw',
				)
			);
		}
		?>
		<span class="card-destination__overlay" aria-hidden="true"></span>

		<div class="card-destination__heading">
			<<?php echo esc_html( $tag ); ?> class="card-destination__title">
				<a class="card-destination__link" href="<?php echo esc_url( $args['permalink'] ); ?>">
					<?php echo esc_html( $args['titre'] ); ?>
				</a>
			</<?php echo esc_html( $tag ); ?>>

			<?php if ( $is_overlay ) : ?>
				<div class="card-destination__overlay-foot">
					<?php if ( '' !== trim( (string) $args['duree'] ) ) : ?>
						<span class="card-destination__duration"><?php echo esc_html( $args['duree'] ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== trim( (string) $args['prix'] ) ) : ?>
						<p class="card-destination__price-inline">
							<span class="card-destination__price-pre"><?php esc_html_e( 'À partir de', 'adaptours' ); ?></span>
							<span class="card-destination__price-amount"><?php echo esc_html( $args['prix'] ); ?></span>
							<span class="card-destination__price-unit"><?php esc_html_e( '/ pers', 'adaptours' ); ?></span>
						</p>
					<?php endif; ?>
				</div>
			<?php elseif ( ! empty( $meta_parts ) ) : ?>
				<p class="card-destination__meta"><?php echo esc_html( implode( ' · ', $meta_parts ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! $is_overlay ) : ?>
		<div class="card-destination__body">
			<?php if ( $args['coup_de_coeur'] ) : ?>
				<p class="card-destination__badge"><?php esc_html_e( 'Coup de cœur', 'adaptours' ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( (string) $args['description'] ) ) : ?>
				<p class="card-destination__desc"><?php echo esc_html( $args['description'] ); ?></p>
			<?php endif; ?>

			<div class="card-destination__footer">
				<?php if ( '' !== trim( (string) $args['prix'] ) ) : ?>
					<p class="card-destination__price">
						<span class="card-destination__price-label"><?php esc_html_e( 'À partir de', 'adaptours' ); ?></span>
						<span class="card-destination__price-value"><?php echo esc_html( $args['prix'] ); ?></span>
					</p>
				<?php endif; ?>

				<span class="card-destination__arrow" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" focusable="false" aria-hidden="true">
						<line x1="5" y1="12" x2="19" y2="12" />
						<path d="M13 6l6 6-6 6" />
					</svg>
				</span>
			</div>
		</div>
	<?php endif; ?>
</article>
