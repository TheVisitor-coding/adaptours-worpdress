<?php
/**
 * Bloc adaptours/destination-gallery — galerie photos d'une destination.
 *
 * Compose : header (eyebrow + titre bichrome + accroche manuscrite) et grille de photos
 * (1 grande tuile en arche + 3 tuiles), depuis la galerie du CPT destination.
 *
 * Chaque tuile est un lien `.adaptours-glightbox` vers l'image pleine taille (GLightbox,
 * assets/js/gallery-lightbox.js). Les 4 premières images forment les tuiles ; les suivantes
 * restent navigables dans la lightbox via des liens masqués.
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
$tagline      = (string) ( $attributes['tagline'] ?? '' );

// ID destination : post courant, ou aperçu éditeur (param GET `post_id`).
$destination_id = get_the_ID();
if ( ! $destination_id && isset( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$destination_id = (int) $_GET['post_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$items = adaptours_get_destination_gallery_items( (int) $destination_id );

// Aucune image : on masque la section.
if ( empty( $items ) ) {
	return;
}

$tiles = array_slice( $items, 0, 4 ); // tuiles visibles
$extra = array_slice( $items, 4 );    // images supplémentaires (lightbox uniquement)

$wrapper = get_block_wrapper_attributes(
	array(
		'aria-label' => esc_attr__( 'Galerie', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<div class="destination-gallery__inner">
		<header class="destination-gallery__head">
			<div class="destination-gallery__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="destination-gallery__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $title ) || '' !== trim( $title_accent ) ) : ?>
					<h2 class="destination-gallery__title"><?php echo adaptours_bichrome( $title, $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>
			<?php if ( '' !== trim( $tagline ) ) : ?>
				<p class="destination-gallery__tagline"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
		</header>

		<div class="destination-gallery__grid">
			<?php foreach ( $tiles as $i => $item ) : ?>
				<?php
				$caption = (string) $item['caption'];
				$aria    = $caption ? $caption : __( 'Photo de la galerie', 'adaptours' );
				?>
				<a
					class="adaptours-glightbox destination-gallery__tile destination-gallery__tile--<?php echo (int) ( $i + 1 ); ?>"
					href="<?php echo esc_url( $item['full'] ); ?>"
					data-gallery="dest-gallery"
					<?php if ( '' !== trim( $caption ) ) : ?>data-glightbox="<?php echo esc_attr( 'title: ' . $caption ); ?>"<?php endif; ?>
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: légende de la photo. */ __( 'Agrandir : %s', 'adaptours' ), $aria ) ); ?>"
				>
					<img
						class="destination-gallery__img"
						src="<?php echo esc_url( $item['thumb'] ); ?>"
						alt="<?php echo esc_attr( $item['alt'] ); ?>"
						loading="lazy"
					/>
					<span class="destination-gallery__overlay" aria-hidden="true"></span>
					<?php if ( '' !== trim( (string) $item['jour'] ) ) : ?>
						<span class="destination-gallery__chip"><?php echo esc_html( $item['jour'] ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== trim( $caption ) ) : ?>
						<span class="destination-gallery__caption"><?php echo esc_html( $caption ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $extra ) ) : ?>
			<div class="destination-gallery__more screen-reader-text">
				<?php foreach ( $extra as $item ) : ?>
					<?php $caption = (string) $item['caption']; ?>
					<a
						class="adaptours-glightbox"
						href="<?php echo esc_url( $item['full'] ); ?>"
						data-gallery="dest-gallery"
						<?php if ( '' !== trim( $caption ) ) : ?>data-glightbox="<?php echo esc_attr( 'title: ' . $caption ); ?>"<?php endif; ?>
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: légende de la photo. */ __( 'Agrandir : %s', 'adaptours' ), $caption ? $caption : __( 'Photo de la galerie', 'adaptours' ) ) ); ?>"
					><?php echo esc_html( $caption ? $caption : __( 'Voir la photo', 'adaptours' ) ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
