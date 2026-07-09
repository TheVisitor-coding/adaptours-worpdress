<?php
/**
 * Bloc adaptours/section-promise — « Le voyage, pensé autour de vous ».
 *
 * Éditable : surtitre, titre bichrome, texte de présentation, 2 images du collage.
 * Figé : 4 atouts (icône + titre + description).
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

// Collage décoratif (colonne aria-hidden) : chaque carte affiche l'image choisie,
// sinon le dégradé de repli porté par le CSS.
$image_main  = (int) ( $attributes['image_main'] ?? 0 );
$image_inset = (int) ( $attributes['image_inset'] ?? 0 );

$img_atts  = array( 'class' => 'section-promise__img', 'alt' => '', 'loading' => 'lazy' );
$main_img  = $image_main > 0 ? wp_get_attachment_image( $image_main, 'large', false, $img_atts ) : '';
$inset_img = $image_inset > 0 ? wp_get_attachment_image( $image_inset, 'large', false, $img_atts ) : '';

// 4 atouts — figés (traductions .po). Icônes du jeu partagé (inc/icons.php).
$features = array(
	array(
		'icon'  => 'accompagnement',
		'title' => __( 'L’humain d’abord', 'adaptours' ),
		'desc'  => __( 'Un interlocuteur dédié du devis au retour.', 'adaptours' ),
	),
	array(
		'icon'  => 'accessibilite',
		'title' => __( 'Vérifié sur le terrain', 'adaptours' ),
		'desc'  => __( 'Chaque hébergement testé par notre équipe.', 'adaptours' ),
	),
	array(
		'icon'  => 'sante',
		'title' => __( 'Matériel inclus', 'adaptours' ),
		'desc'  => __( 'Fauteuil de plage, lève-personne, matériel médical.', 'adaptours' ),
	),
	array(
		'icon'  => 'rythme',
		'title' => __( 'Présents 24/7', 'adaptours' ),
		'desc'  => __( 'Une équipe joignable, même à l’autre bout du monde.', 'adaptours' ),
	),
);

$wrapper = get_block_wrapper_attributes( array( 'class' => 'section-promise' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<span class="section-promise__blob" aria-hidden="true"></span>

	<div class="section-promise__inner">
		<div class="section-promise__media" aria-hidden="true">
			<!-- <span class="section-promise__scribble">vraiment pour tous ↓</span> -->
			<span class="section-promise__photo section-promise__photo--main">
				<?php echo $main_img; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image() échappé. ?>
				<span class="section-promise__stamp">
					<span class="section-promise__stamp-top">Depuis</span>
					<span class="section-promise__stamp-year">2011</span>
					<span class="section-promise__stamp-name">Adaptours</span>
				</span>
			</span>
			<span class="section-promise__photo section-promise__photo--inset">
				<?php echo $inset_img; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image() échappé. ?>
			</span>
		</div>

		<div class="section-promise__body">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="section-promise__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
				<h2 class="section-promise__title">
					<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</h2>
			<?php endif; ?>

			<?php if ( '' !== trim( $description ) ) : ?>
				<div class="section-promise__text">
					<?php echo wpautop( esc_html( $description ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- esc_html avant wpautop. ?>
				</div>
			<?php endif; ?>

			<ul class="section-promise__features">
				<?php foreach ( $features as $feature ) : ?>
					<li class="section-promise__feature">
						<span class="section-promise__feature-icon" aria-hidden="true">
							<?php echo adaptours_icon_svg( $feature['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG échappé par le helper. ?>
						</span>
						<span class="section-promise__feature-text">
							<span class="section-promise__feature-title"><?php echo esc_html( $feature['title'] ); ?></span>
							<span class="section-promise__feature-desc"><?php echo esc_html( $feature['desc'] ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
