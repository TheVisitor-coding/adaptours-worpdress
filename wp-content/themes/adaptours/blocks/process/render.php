<?php
/**
 * Bloc adaptours/process — « Du premier mot au dernier souvenir ».
 *
 * Éditable : en-tête + 3 étapes (titre, texte, points cochés, durée, bouton optionnel).
 * Figé : numérotation par position (1..3), icône par étape, 3e card en thème sombre.
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

// Icônes figées par étape (jeu partagé inc/icons.php).
$icons = array( 'accompagnement', 'confort', 'vol' );

$cards = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$cards[] = array(
		'number'    => $i,
		'icon'      => $icons[ $i - 1 ],
		'title'     => (string) ( $attributes[ "process_{$i}_title" ] ?? '' ),
		'desc'      => (string) ( $attributes[ "process_{$i}_description" ] ?? '' ),
		'features'  => (string) ( $attributes[ "process_{$i}_features" ] ?? '' ),
		'meta'      => (string) ( $attributes[ "process_{$i}_meta" ] ?? '' ),
		'cta_label' => (string) ( $attributes[ "process_{$i}_cta_label" ] ?? '' ),
		'cta_url'   => (string) ( $attributes[ "process_{$i}_cta_url" ] ?? '' ),
		'dark'      => ( 3 === $i ), // la 3e étape bascule en thème sombre (figé).
	);
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'process' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="process__inner">
		<div class="process__head">
			<?php if ( '' !== trim( $eyebrow ) ) : ?>
				<p class="process__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( $part_1 . $part_2 ) ) : ?>
				<h2 class="process__title">
					<?php echo adaptours_bichrome( trim( $part_1 . ' ' . $part_2 ), $part_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</h2>
			<?php endif; ?>
			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="process__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<ol class="process__steps">
			<?php foreach ( $cards as $card ) : ?>
				<li class="process__step<?php echo $card['dark'] ? ' process__step--dark' : ''; ?>">
					<div class="process__step-top">
						<span class="process__step-number"><?php echo esc_html( (string) $card['number'] ); ?></span>
						<span class="process__step-icon" aria-hidden="true">
							<?php echo adaptours_icon_svg( $card['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG échappé par le helper. ?>
						</span>
					</div>

					<?php if ( '' !== trim( $card['title'] ) ) : ?>
						<h3 class="process__step-title"><?php echo esc_html( $card['title'] ); ?></h3>
					<?php endif; ?>

					<?php if ( '' !== trim( $card['desc'] ) ) : ?>
						<p class="process__step-desc"><?php echo esc_html( $card['desc'] ); ?></p>
					<?php endif; ?>

					<?php
					$features = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $card['features'] ) ) );
					if ( ! empty( $features ) ) :
						?>
						<ul class="process__step-features">
							<?php foreach ( $features as $feature ) : ?>
								<li class="process__step-feature">
									<span class="process__step-check" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true"><polyline points="5 12.5 10 17 19 7"></polyline></svg>
									</span>
									<?php echo esc_html( $feature ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( '' !== trim( $card['meta'] ) ) : ?>
						<p class="process__step-meta"><?php echo esc_html( $card['meta'] ); ?></p>
					<?php endif; ?>

					<?php if ( '' !== trim( $card['cta_label'] ) && '' !== trim( $card['cta_url'] ) ) : ?>
						<a class="process__step-cta" href="<?php echo esc_url( $card['cta_url'] ); ?>">
							<?php echo esc_html( $card['cta_label'] ); ?>
							<span aria-hidden="true">→</span>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
