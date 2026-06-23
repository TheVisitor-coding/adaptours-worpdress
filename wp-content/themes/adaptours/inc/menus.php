<?php
/**
 * Emplacements de menus : menu principal + deux colonnes de footer.
 *
 * La 3e colonne du footer affiche des icônes sociales issues des options, pas un menu.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Déclare les emplacements de menus assignables.
 */
function adaptours_register_menus() {
	register_nav_menus(
		array(
			'primary'  => __( 'Menu principal', 'adaptours' ),
			'footer_1' => __( 'Footer — Colonne 1', 'adaptours' ),
			'footer_2' => __( 'Footer — Colonne 2', 'adaptours' ),
		)
	);
}
add_action( 'after_setup_theme', 'adaptours_register_menus' );
