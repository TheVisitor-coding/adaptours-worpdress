<?php
/**
 * Bloc adaptours/destinations-grid — « Des destinations variées ».
 *
 * Éditable : surtitre, titre bichrome, texte, bouton, et 4 destinations choisies. La 1re
 * destination est une grande card « arche » sur 2 rangs, les 3 autres en grille.
 *
 * Les données viennent de la source unique adaptours_get_destination_card_args() ; le rendu
 * visuel (layout arche, prix « dès » inline) est propre au bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow     = (string) ( $attributes['eyebrow'] ?? '' );
$part_1      = (string) ( $attributes['title_part_1'] ?? '' );
$part_2      = (string) ( $attributes['title_part_2'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );
$cta_label   = (string) ( $attributes['cta_label'] ?? '' );
$cta_url     = (string) ( $attributes['cta_url'] ?? '' );
$ids         = array_map( 'intval', (array) ( $attributes['destinations'] ?? array() ) );

// Repli CTA → archive destinations.
$archive_url = get_post_type_archive_link( 'destination' );
if ( '' === trim( $cta_url ) ) {
	$cta_url = $archive_url ? $archive_url : home_url( '/destinations/' );
}

// 4 emplacements figés : 0 = grande arche, 1 & 2 = grille haute, 3 = card large basse.
$slots = array( 'hero', 'top-a', 'top-b', 'wide' );

/**
 * Rend une card destination (markup propre au bloc, données mutualisées).
 *
 * @param int    $id   ID destination (0 = emplacement vide).
 * @param string $slot Modificateur d'emplacement.
 */
$render_card = static function ( $id, $slot ) {
	$base = 'destinations-grid__card';
	if ( $id <= 0 || 'publish' !== get_post_status( $id ) ) {
		// Emplacement vide : conserve la trame de la grille (utile en édition).
		printf(
			'<div class="%1$s %1$s--%2$s %1$s--empty" aria-hidden="true"></div>',
			esc_attr( $base ),
			esc_attr( $slot )
		);
		return;
	}

	$args = adaptours_get_destination_card_args( $id );
	$img  = (int) $args['image_id'] > 0
		? wp_get_attachment_image(
			(int) $args['image_id'],
			'large',
			false,
			array(
				'class'    => $base . '-img',
				'alt'      => '',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => '(max-width: 768px) 100vw, 50vw',
			)
		)
		: '';
	?>
	<article class="<?php echo esc_attr( $base . ' ' . $base . '--' . $slot ); ?>">
		<a class="<?php echo esc_attr( $base . '-link' ); ?>" href="<?php echo esc_url( $args['permalink'] ); ?>">
			<span class="<?php echo esc_attr( $base . '-media' . ( '' === $img ? ' ' . $base . '-media--placeholder' : '' ) ); ?>">
				<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput -- markup d'image WP. ?>
			</span>
			<span class="<?php echo esc_attr( $base . '-overlay' ); ?>" aria-hidden="true"></span>
			<span class="<?php echo esc_attr( $base . '-foot' ); ?>">
				<span class="<?php echo esc_attr( $base . '-title' ); ?>"><?php echo esc_html( $args['titre'] ); ?></span>
				<?php if ( '' !== trim( (string) $args['prix'] ) ) : ?>
					<span class="<?php echo esc_attr( $base . '-price' ); ?>">
						<?php
						/* translators: %s = prix formaté, ex. « 2 490€ ». */
						echo esc_html( sprintf( __( 'dès %s', 'adaptours' ), $args['prix'] ) );
						?>
					</span>
				<?php endif; ?>
			</span>
		</a>
	</article>
	<?php
};

$wrapper = get_block_wrapper_attributes( array( 'class' => 'destinations-grid' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="destinations-grid__inner">
		<div class="destinations-grid__head">
			<div class="destinations-grid__head-left">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="destinations-grid__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
					<h2 class="destinations-grid__title">
						<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</h2>
				<?php endif; ?>
			</div>
			<div class="destinations-grid__head-right">
				<?php if ( '' !== trim( $description ) ) : ?>
					<p class="destinations-grid__desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $cta_label ) ) : ?>
					<a class="destinations-grid__cta" href="<?php echo esc_url( $cta_url ); ?>">
						<?php echo esc_html( $cta_label ); ?>
						<span aria-hidden="true">→</span>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="destinations-grid__grid">
			<?php
			foreach ( $slots as $i => $slot ) {
				$render_card( $ids[ $i ] ?? 0, $slot );
			}
			?>
		</div>
	</div>
</section>
