<?php
/**
 * Rôles et capabilities du thème.
 *
 * Capability custom `manage_adaptours_options` (page d'options) + rôle « Cliente » basé
 * sur Éditeur, sans accès aux menus Extensions / Thèmes / Mises à jour. Création
 * idempotente à l'activation, doublée d'un masquage des menus sensibles en admin.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ADAPTOURS_ROLE_CLIENTE = 'adaptours_cliente';
const ADAPTOURS_CAP_OPTIONS  = 'manage_adaptours_options';

/**
 * (Re)crée le rôle Cliente et attribue la capability d'options à l'administrateur.
 *
 * Idempotent : le rôle est retiré puis recréé pour refléter toute évolution des
 * capabilities entre deux activations (les utilisateurs gardent le slug de rôle).
 */
function adaptours_register_roles() {
	$editor = get_role( 'editor' );

	// Base = capabilities de l'Éditeur (fallback minimal si Éditeur indisponible).
	$caps = $editor ? $editor->capabilities : array(
		'read'         => true,
		'edit_posts'   => true,
		'edit_pages'   => true,
		'upload_files' => true,
	);

	// Filet défensif : on retire explicitement toute capability donnant accès aux
	// menus Extensions / Thèmes / Mises à jour (l'Éditeur ne les a pas, mais on
	// verrouille au cas où la base évoluerait).
	$forbidden = array(
		'install_plugins',
		'activate_plugins',
		'edit_plugins',
		'delete_plugins',
		'update_plugins',
		'install_themes',
		'switch_themes',
		'edit_themes',
		'delete_themes',
		'update_themes',
		'edit_theme_options',
		'update_core',
	);
	foreach ( $forbidden as $cap ) {
		unset( $caps[ $cap ] );
	}

	// Capability custom pilotant la page d'options « Coordonnées & liens ».
	$caps[ ADAPTOURS_CAP_OPTIONS ] = true;

	remove_role( ADAPTOURS_ROLE_CLIENTE );
	add_role( ADAPTOURS_ROLE_CLIENTE, __( 'Cliente', 'adaptours' ), $caps );

	// L'administrateur conserve l'accès à la page d'options.
	$admin = get_role( 'administrator' );
	if ( $admin && ! $admin->has_cap( ADAPTOURS_CAP_OPTIONS ) ) {
		$admin->add_cap( ADAPTOURS_CAP_OPTIONS );
	}
}
add_action( 'after_switch_theme', 'adaptours_register_roles' );

/**
 * Masque les menus Extensions / Thèmes / Mises à jour pour les non-administrateurs.
 *
 * Filet d'affichage doublant la restriction par capabilities : même si une extension
 * tierce élargissait les droits, les entrées de menu restent cachées.
 */
function adaptours_hide_admin_menus() {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}

	remove_menu_page( 'plugins.php' );                  // Extensions
	remove_menu_page( 'themes.php' );                   // Apparence > Thèmes
	remove_submenu_page( 'themes.php', 'themes.php' );
	remove_submenu_page( 'index.php', 'update-core.php' ); // Tableau de bord > Mises à jour
}
add_action( 'admin_menu', 'adaptours_hide_admin_menus', 999 );
