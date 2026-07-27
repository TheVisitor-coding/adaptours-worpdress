<?php
/**
 * Provisioning de la structure du site — pages, page d'accueil, menus, termes, idempotent.
 *
 * À exécuter après tools/setup-polylang.php, via WP-CLI (chemin ABSOLU obligatoire — wp eval-file
 * résout relativement au CWD) :
 *   wp eval-file "$HOME/preprod/wp-content/themes/adaptours/tools/setup-site.php"
 *
 * Ne provisionne que la STRUCTURE : les pages du site avec leur template et le squelette de blocs
 * verrouillés (miroir de adaptours_lock_map()), la page d'accueil, les emplacements de menus et les
 * zones géographiques. Le contenu éditorial, les médias et la page « Coordonnées & liens » restent
 * à la charge de la cliente.
 *
 * Idempotent : les pages déjà éditées (contenu non vide) ne sont jamais écrasées ; les menus sont
 * reconstruits proprement ; langues, termes et front page sont posés sans doublon.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Blocs parents à InnerBlocks : sérialisés en balise ouvrante + fermante (pas auto-fermante),
 * pour que l'éditeur affiche l'appendeur d'éléments enfants.
 */
$adaptours_innerblocks_parents = array(
	'adaptours/team-grid',
	'adaptours/recruitment',
	'adaptours/itinerary',
	'adaptours/section-practical',
	'adaptours/rich-text',
	'adaptours/cards-numbered',
	'adaptours/card-grid',
);

/**
 * Sérialise une entrée de adaptours_lock_map() en commentaire de bloc Gutenberg.
 *
 * JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES => aucun backslash dans la source ; wp_slash à
 * l'insertion se charge des guillemets (pitfall connu : wp_insert_post déslashe le post_content).
 */
$adaptours_block_markup = function ( $entry ) use ( $adaptours_innerblocks_parents ) {
	$name  = $entry[0];
	$attrs = ( isset( $entry[1] ) && is_array( $entry[1] ) ) ? $entry[1] : array();
	$json  = $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';

	if ( in_array( $name, $adaptours_innerblocks_parents, true ) ) {
		return '<!-- wp:' . $name . $json . ' -->' . "\n" . '<!-- /wp:' . $name . ' -->';
	}
	return '<!-- wp:' . $name . $json . ' /-->';
};

/** Construit le post_content d'une page depuis la structure verrouillée d'un contexte. */
$adaptours_content_from_lockmap = function ( $key ) use ( $adaptours_block_markup ) {
	$map = function_exists( 'adaptours_lock_map' ) ? adaptours_lock_map() : array();
	if ( empty( $map[ $key ]['template'] ) ) {
		return '';
	}
	return implode( "\n\n", array_map( $adaptours_block_markup, $map[ $key ]['template'] ) );
};

/*
 * 1. Pages FR.
 */
$adaptours_pages = array(
	array( 'slug' => 'accueil',                       'title' => 'Accueil',                          'template' => '',                            'lockmap' => 'front-page',               'front' => true ),
	array( 'slug' => 'qui-sommes-nous',               'title' => 'Qui sommes-nous',                  'template' => 'template-qui-sommes-nous.php', 'lockmap' => 'template-qui-sommes-nous' ),
	array( 'slug' => 'contact',                       'title' => 'Contact',                          'template' => 'template-contact.php',        'lockmap' => 'template-contact' ),
	array( 'slug' => 'devis',                         'title' => 'Devis',                            'template' => 'template-devis.php',          'lockmap' => 'template-devis' ),
	array( 'slug' => 'mentions-legales',              'title' => 'Mentions légales',                 'template' => 'template-page-modulaire.php', 'lockmap' => 'template-page-modulaire' ),
	array( 'slug' => 'cgv',                           'title' => 'Conditions générales de vente',    'template' => 'template-page-modulaire.php', 'lockmap' => 'template-page-modulaire' ),
	array( 'slug' => 'politique-de-confidentialite',  'title' => 'Politique de confidentialité',     'template' => 'template-page-modulaire.php', 'lockmap' => 'template-page-modulaire' ),
);

$adaptours_ids = array();

foreach ( $adaptours_pages as $page ) {
	$existing = get_page_by_path( $page['slug'] );
	$content  = $adaptours_content_from_lockmap( $page['lockmap'] );

	$postarr = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $page['title'],
		'post_name'    => $page['slug'],
		'post_content' => $content,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		// Ne jamais écraser une page déjà éditée : on ne (re)pose le squelette que si elle est vide.
		if ( '' !== trim( (string) $existing->post_content ) ) {
			unset( $postarr['post_content'] );
		}
		$id = wp_update_post( wp_slash( $postarr ), true );
	} else {
		$id = wp_insert_post( wp_slash( $postarr ), true );
	}

	if ( is_wp_error( $id ) || ! $id ) {
		echo "ERREUR page « {$page['slug']} » : " . ( is_wp_error( $id ) ? $id->get_error_message() : '?' ) . "\n";
		continue;
	}
	$id = (int) $id;
	$adaptours_ids[ $page['slug'] ] = $id;

	if ( $page['template'] ) {
		update_post_meta( $id, '_wp_page_template', $page['template'] );
	}

	// Rattache la page à la langue par défaut (FR) pour que seed-en.php la retrouve.
	if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_get_post_language' ) ) {
		if ( ! pll_get_post_language( $id ) ) {
			$default = function_exists( 'pll_default_language' ) ? pll_default_language() : 'fr';
			pll_set_post_language( $id, $default );
		}
	}

	echo sprintf( "page %-30s #%-5d %s\n", $page['slug'], $id, get_permalink( $id ) );
}

/*
 * 2. Page d'accueil statique.
 */
if ( ! empty( $adaptours_ids['accueil'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $adaptours_ids['accueil'] );
	echo 'page_on_front = ' . get_option( 'page_on_front' ) . "\n";
}

/*
 * 3. Zones géographiques (continents), idempotent.
 */
$adaptours_zones = array( 'Europe', 'Afrique', 'Amériques', 'Asie', 'Océanie', 'Moyen-Orient' );
foreach ( $adaptours_zones as $zone ) {
	if ( ! term_exists( $zone, 'zone_geographique' ) ) {
		$term = wp_insert_term( $zone, 'zone_geographique' );
		echo is_wp_error( $term ) ? "ERREUR zone « $zone » : " . $term->get_error_message() . "\n" : "+ zone « $zone »\n";
	} else {
		echo "= zone « $zone » déjà présente\n";
	}
}

/*
 * 4. Menus + affectation aux emplacements (inc/menus.php : primary, footer_1, footer_2).
 */
$adaptours_ensure_menu = function ( $name ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
		return is_wp_error( $menu_id ) ? 0 : (int) $menu_id;
	}
	$menu_id = (int) $menu->term_id;
	// Purge des items pour une reconstruction idempotente (pas de doublons au rejeu).
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	return $menu_id;
};

$adaptours_add_page_item = function ( $menu_id, $page_id, $title ) {
	if ( ! $menu_id || ! $page_id ) {
		return;
	}
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $title,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => (int) $page_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
};

$adaptours_add_link_item = function ( $menu_id, $url, $title ) {
	if ( ! $menu_id ) {
		return;
	}
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'  => $title,
			'menu-item-url'    => $url,
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		)
	);
};

// Un menu par langue et par emplacement : Polylang (frontend-nav-menu.php) remplace, sur le
// front, l'emplacement par nav_menus[theme][emplacement][langue courante]. Assigner uniquement
// le theme_mod ne suffit pas (l'interception admin de Polylang ne se déclenche pas en CLI) :
// on écrit directement la carte par langue de Polylang. Les items de page utilisent la
// traduction de la page dans chaque langue (pll_get_post) ; si elle n'existe pas encore, l'item
// est ignoré (le menu se complète au rejeu, après création des traductions).
$theme        = get_stylesheet();
$default_lang = function_exists( 'pll_default_language' ) ? pll_default_language() : 'fr';
$langs        = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : array( $default_lang );

$adaptours_tr = function ( $base_id, $lang ) {
	if ( ! $base_id ) {
		return 0;
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$t = pll_get_post( (int) $base_id, $lang );
		return $t ? (int) $t : 0;
	}
	return (int) $base_id;
};

$footer_2_pages = array(
	'qui-sommes-nous'              => 'Qui sommes-nous',
	'contact'                      => 'Contact',
	'mentions-legales'             => 'Mentions légales',
	'cgv'                          => 'CGV',
	'politique-de-confidentialite' => 'Politique de confidentialité',
);

$menu_map = array();

foreach ( $langs as $lang ) {
	$suffix   = strtoupper( $lang );
	$dest_url = ( $lang === $default_lang ) ? home_url( '/destinations/' ) : home_url( '/' . $lang . '/destinations/' );

	$primary = $adaptours_ensure_menu( "Menu principal ($suffix)" );
	$adaptours_add_link_item( $primary, $dest_url, 'Destinations' );
	$adaptours_add_page_item( $primary, $adaptours_tr( $adaptours_ids['qui-sommes-nous'] ?? 0, $lang ), 'Qui sommes-nous' );
	$adaptours_add_page_item( $primary, $adaptours_tr( $adaptours_ids['contact'] ?? 0, $lang ), 'Contact' );

	$footer_1 = $adaptours_ensure_menu( "Footer — Destinations ($suffix)" );
	$adaptours_add_link_item( $footer_1, $dest_url, 'Toutes les destinations' );

	$footer_2 = $adaptours_ensure_menu( "Footer — À propos ($suffix)" );
	foreach ( $footer_2_pages as $slug => $title ) {
		$adaptours_add_page_item( $footer_2, $adaptours_tr( $adaptours_ids[ $slug ] ?? 0, $lang ), $title );
	}

	$menu_map['primary'][ $lang ]  = $primary;
	$menu_map['footer_1'][ $lang ] = $footer_1;
	$menu_map['footer_2'][ $lang ] = $footer_2;

	echo "menus [$lang] : primary #$primary, footer_1 #$footer_1, footer_2 #$footer_2\n";
}

// Carte par langue de Polylang (source de vérité du front).
$pll_opt = get_option( 'polylang' );
if ( is_array( $pll_opt ) ) {
	$pll_opt['nav_menus'][ $theme ] = $menu_map;
	update_option( 'polylang', $pll_opt );
	echo "carte Polylang nav_menus[$theme] écrite\n";
}

// Base theme_mod (langue par défaut) : utilisée si Polylang est absent ou hors contexte de langue.
set_theme_mod(
	'nav_menu_locations',
	array(
		'primary'  => $menu_map['primary'][ $default_lang ] ?? 0,
		'footer_1' => $menu_map['footer_1'][ $default_lang ] ?? 0,
		'footer_2' => $menu_map['footer_2'][ $default_lang ] ?? 0,
	)
);

/*
 * 5. Réécritures (archive /destinations/, taxonomie /zone/).
 */
flush_rewrite_rules();

echo "OK\n";
