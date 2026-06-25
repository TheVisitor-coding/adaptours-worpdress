<?php
/**
 * Intégration Polylang (multilingue FR/EN/ES).
 *
 * Déclare les CPT et taxonomies traduisibles et définit les métas copiées entre
 * traductions. Tout passe par des filtres Polylang : sans le plugin, aucun effet.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Déclare destination et avis comme types de contenu traduisibles.
 *
 * @param string[] $post_types Types de contenu gérés par Polylang.
 * @return string[]
 */
function adaptours_pll_post_types( $post_types ) {
	$post_types['destination'] = 'destination';
	$post_types['avis']        = 'avis';
	// Nécessaire pour que pll_get_post() renvoie le formulaire de la langue courante.
	$post_types['wpcf7_contact_form'] = 'wpcf7_contact_form';
	return $post_types;
}
add_filter( 'pll_get_post_types', 'adaptours_pll_post_types' );

/**
 * Déclare zone_geographique comme taxonomie traduisible.
 *
 * @param string[] $taxonomies Taxonomies gérées par Polylang.
 * @return string[]
 */
function adaptours_pll_taxonomies( $taxonomies ) {
	$taxonomies['zone_geographique'] = 'zone_geographique';
	return $taxonomies;
}
add_filter( 'pll_get_taxonomies', 'adaptours_pll_taxonomies' );

/**
 * Métas copiées à l'identique entre traductions.
 *
 * On ne copie que les données indépendantes de la langue (prix, images, codes, notes).
 * Tout ce qui contient du texte localisable est saisi par langue et n'est pas listé ici.
 *
 * @param string[] $metas Clés de méta copiées par Polylang.
 * @return string[]
 */
function adaptours_pll_copy_post_metas( $metas ) {
	// Destination.
	$metas[] = 'prix_a_partir_de';
	$metas[] = '_adaptours_gallery_ids';
	$metas[] = 'coup_de_coeur';
	$metas[] = 'coordonnees';
	$metas[] = 'nuits_sur_place';
	$metas[] = 'voyageurs_min';
	$metas[] = 'voyageurs_max';
	$metas[] = 'carte_image';
	$metas[] = 'distance_km';
	$metas[] = 'monnaie_code';
	for ( $i = 1; $i <= 4; $i++ ) {
		$metas[] = "accessibility_card_{$i}_icon";
	}

	// Avis.
	$metas[] = 'note';
	$metas[] = 'mois_voyage';
	$metas[] = 'photo_voyageur';
	$metas[] = 'is_featured';

	// Le template de page conditionne le verrou (adaptours_block_context) : la traduction
	// doit hériter du même template que l'original.
	$metas[] = '_wp_page_template';
	return $metas;
}
add_filter( 'pll_copy_post_metas', 'adaptours_pll_copy_post_metas' );

/**
 * Empêche la redirection canonique de la page d'accueil traduite vers son permalink.
 *
 * Pour une page d'accueil statique traduite, get_permalink() de la traduction renvoie son
 * permalink (/en/home-en/) et non la racine de langue (/en/) ; redirect_canonical redirige
 * alors /en/ vers ce permalink. La page reste servie à /en/.
 *
 * @param string|false $redirect_url  URL de redirection calculée par le core.
 * @param string       $requested_url URL demandée.
 * @return string|false
 */
function adaptours_keep_translated_front_page_url( $redirect_url, $requested_url ) {
	if ( is_page()
		&& function_exists( 'adaptours_is_front_page_in_any_language' )
		&& adaptours_is_front_page_in_any_language( get_queried_object_id() ) ) {
		return false;
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'adaptours_keep_translated_front_page_url', 10, 2 );
