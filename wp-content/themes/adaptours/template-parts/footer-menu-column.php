<?php
/**
 * Colonne de liens du footer : titre + menu d'un emplacement WP.
 *
 * Réutilisé pour les colonnes « Destinations » et « À propos » (la colonne « Nous suivre »
 * affiche des icônes sociales, voir footer.php). Si aucun menu n'est assigné à l'emplacement,
 * seul le titre est rendu.
 *
 * @param array $args {
 *     @type string $location Emplacement de menu (ex. « footer_1 »).
 *     @type string $title    Titre de colonne (déjà traduit).
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
		'location' => '',
		'title'    => '',
	)
);
?>
<div class="site-footer__col">
	<?php if ( '' !== $args['title'] ) : ?>
		<h2 class="site-footer__col-title"><?php echo esc_html( $args['title'] ); ?></h2>
	<?php endif; ?>

	<?php if ( has_nav_menu( $args['location'] ) ) : ?>
		<nav class="site-footer__nav" aria-label="<?php echo esc_attr( $args['title'] ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => $args['location'],
					'container'      => false,
					'menu_class'     => 'site-footer__menu',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	<?php endif; ?>
</div>
