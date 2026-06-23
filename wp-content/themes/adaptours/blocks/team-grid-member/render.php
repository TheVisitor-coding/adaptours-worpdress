<?php
/**
 * Bloc adaptours/team-grid-member — une personne de l'équipe.
 *
 * Photo (ou cadre coloré si vide), nom, poste et petite phrase précédée d'une puce ✦.
 * Rendu en <li> dans la grille du bloc parent adaptours/team-grid (pas de style propre).
 *
 * @var array    $attributes Attributs du bloc (photo_id, name, role, tagline).
 * @var string   $content    Contenu interne (non utilisé).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$photo_id = (int) ( $attributes['photo_id'] ?? 0 );
$name     = (string) ( $attributes['name'] ?? '' );
$role     = (string) ( $attributes['role'] ?? '' );
$tagline  = (string) ( $attributes['tagline'] ?? '' );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'team-grid__card' ) );
?>
<li <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="team-grid__photo<?php echo $photo_id > 0 ? '' : ' team-grid__photo--placeholder'; ?>">
		<?php
		if ( $photo_id > 0 ) {
			echo wp_get_attachment_image(
				$photo_id,
				'medium_large',
				false,
				array(
					'class'    => 'team-grid__img',
					'alt'      => '',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
		}
		?>
	</div>
	<div class="team-grid__card-body">
		<?php if ( '' !== trim( $name ) ) : ?>
			<h3 class="team-grid__name"><?php echo esc_html( $name ); ?></h3>
		<?php endif; ?>
		<?php if ( '' !== trim( $role ) ) : ?>
			<p class="team-grid__role"><?php echo esc_html( $role ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== trim( $tagline ) ) : ?>
			<p class="team-grid__tagline">
				<span class="team-grid__tagline-mark" aria-hidden="true">✦</span>
				<span class="team-grid__tagline-text"><?php echo esc_html( $tagline ); ?></span>
			</p>
		<?php endif; ?>
	</div>
</li>
