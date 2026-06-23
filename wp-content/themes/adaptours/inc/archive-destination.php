<?php
/**
 * Couche données de l'archive Destinations.
 *
 * Archive rendue côté serveur, filtres au rechargement de page via un formulaire GET (pas
 * d'AJAX). Ce fichier porte la logique de requête (paliers budget, WP_Query, extension de la
 * recherche au champ « ville ») et la liste des filtres actifs. Le rendu des cards passe par
 * le partial card-destination.php et son helper (inc/helpers.php).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paliers du filtre « Budget ».
 *
 * Source unique consommée par le menu déroulant, le meta_query et le libellé du chip actif.
 * Paliers sans recouvrement : < 2 000 · 2 000–3 000 · > 3 000.
 *
 * @return array<string,array> slug => [ label, compare, value ].
 */
function adaptours_destination_budget_buckets() {
	return array(
		'moins-2000' => array(
			'label'   => __( 'Moins de 2 000 €', 'adaptours' ),
			'compare' => '<',
			'value'   => 2000,
		),
		'2000-3000'  => array(
			'label'   => __( '2 000 – 3 000 €', 'adaptours' ),
			'compare' => 'BETWEEN',
			'value'   => array( 2000, 3000 ),
		),
		'plus-3000'  => array(
			'label'   => __( 'Plus de 3 000 €', 'adaptours' ),
			'compare' => '>',
			'value'   => 3000,
		),
	);
}

/**
 * Construit et exécute la requête de l'archive à partir des filtres GET.
 *
 * Lit `recherche`, `zone` et `budget` dans $_GET (sanitisés), et renvoie un WP_Query.
 * `posts_per_page = -1` : tout afficher, sans pagination tant que le catalogue est petit.
 *
 * @return WP_Query
 */
function adaptours_get_destinations_query() {
	$args = array(
		'post_type'           => 'destination',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
	);

	// Filtre « Continent » → taxonomie zone_geographique (par slug).
	$zone = isset( $_GET['zone'] ) ? sanitize_title( wp_unslash( $_GET['zone'] ) ) : '';
	if ( '' !== $zone ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'zone_geographique',
				'field'    => 'slug',
				'terms'    => $zone,
			),
		);
	}

	// Filtre « Budget » → meta_query NUMERIC sur prix_a_partir_de.
	$budget  = isset( $_GET['budget'] ) ? sanitize_key( wp_unslash( $_GET['budget'] ) ) : '';
	$buckets = adaptours_destination_budget_buckets();
	if ( '' !== $budget && isset( $buckets[ $budget ] ) ) {
		$bucket             = $buckets[ $budget ];
		$args['meta_query'] = array(
			array(
				'key'     => 'prix_a_partir_de',
				'type'    => 'NUMERIC',
				'compare' => $bucket['compare'],
				'value'   => $bucket['value'],
			),
		);
	}

	// Recherche plein texte → titre (natif `s`) + champ « ville » (extension scopée).
	$search = isset( $_GET['recherche'] ) ? sanitize_text_field( wp_unslash( $_GET['recherche'] ) ) : '';
	if ( '' !== $search ) {
		$args['s']                       = $search;
		$args['adaptours_search_ville']  = true;
	}

	add_filter( 'posts_search', 'adaptours_search_include_ville', 10, 2 );
	$query = new WP_Query( $args );
	remove_filter( 'posts_search', 'adaptours_search_include_ville', 10 );

	return $query;
}

/**
 * Étend la recherche WP native (titre/contenu/extrait) au champ méta « ville ».
 *
 * La recherche WordPress n'inclut pas les métas. On greffe un OR sur une sous-requête
 * postmeta `ville` dans le groupe de recherche : une destination remonte si le terme matche
 * son titre OU sa ville. Filtre scopé au drapeau `adaptours_search_ville`.
 *
 * @param string   $search   Clause SQL de recherche (« AND (...) »).
 * @param WP_Query $wp_query Requête courante.
 * @return string Clause éventuellement étendue.
 */
function adaptours_search_include_ville( $search, $wp_query ) {
	global $wpdb;

	if ( '' === $search || ! $wp_query->get( 'adaptours_search_ville' ) ) {
		return $search;
	}

	$term = (string) $wp_query->get( 's' );
	if ( '' === $term ) {
		return $search;
	}

	$like     = '%' . $wpdb->esc_like( $term ) . '%';
	$subquery = $wpdb->prepare(
		" OR ( {$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'ville' AND meta_value LIKE %s ) )",
		$like
	);

	// Insère le OR avant la parenthèse fermante du groupe de recherche : « AND (clause OR sous-requête) ».
	return preg_replace( '/\)\s*$/', $subquery . ' )', $search, 1 );
}

/**
 * Données du chapô de l'archive, prêtes à afficher.
 *
 * Lit les réglages éditables (page d'options) avec valeurs par défaut, applique la
 * traduction Polylang si actif, et substitue {n} par le nombre de destinations publiées.
 *
 * @return array{eyebrow:string,title_part_1:string,title_part_2:string,intro:string,badge:string}
 */
function adaptours_get_destinations_chapo() {
	$translate = static function ( $value ) {
		return ( '' !== $value && function_exists( 'pll__' ) ) ? pll__( $value ) : $value;
	};

	$count     = (int) wp_count_posts( 'destination' )->publish;
	$badge_raw = $translate( adaptours_get_option( 'dest_badge_label', __( '{n} voyages prêts à partir', 'adaptours' ) ) );

	return array(
		'eyebrow'      => $translate( adaptours_get_option( 'dest_eyebrow', __( 'CATALOGUE', 'adaptours' ) ) ),
		'title_part_1' => $translate( adaptours_get_option( 'dest_title_part_1', __( 'Destinations', 'adaptours' ) ) ),
		'title_part_2' => $translate( adaptours_get_option( 'dest_title_part_2', __( 'accessibles.', 'adaptours' ) ) ),
		'intro'        => $translate( adaptours_get_option( 'dest_intro', __( 'Toutes nos destinations sont repérées, testées et validées par notre équipe. Chaque fiche détaille les conditions d’accessibilité.', 'adaptours' ) ) ),
		'badge'        => str_replace( '{n}', number_format_i18n( $count ), $badge_raw ),
	);
}

/**
 * Liste des filtres actifs, pour les « chips » (puces) + leur lien de suppression unitaire.
 *
 * Chaque chip retire SON paramètre de l'URL courante (remove_query_arg) ; les autres
 * filtres restent appliqués. Source des libellés : nom du terme (zone), label de palier
 * (budget, via adaptours_destination_budget_buckets), terme saisi (recherche).
 *
 * @return array<int,array{label:string,remove_url:string}>
 */
function adaptours_destination_filter_chips() {
	$chips = array();

	$zone = isset( $_GET['zone'] ) ? sanitize_title( wp_unslash( $_GET['zone'] ) ) : '';
	if ( '' !== $zone ) {
		$term = get_term_by( 'slug', $zone, 'zone_geographique' );
		if ( $term && ! is_wp_error( $term ) ) {
			$chips[] = array(
				'label'      => $term->name,
				'remove_url' => remove_query_arg( 'zone' ),
			);
		}
	}

	$budget  = isset( $_GET['budget'] ) ? sanitize_key( wp_unslash( $_GET['budget'] ) ) : '';
	$buckets = adaptours_destination_budget_buckets();
	if ( '' !== $budget && isset( $buckets[ $budget ] ) ) {
		$chips[] = array(
			'label'      => $buckets[ $budget ]['label'],
			'remove_url' => remove_query_arg( 'budget' ),
		);
	}

	$search = isset( $_GET['recherche'] ) ? sanitize_text_field( wp_unslash( $_GET['recherche'] ) ) : '';
	if ( '' !== $search ) {
		$chips[] = array(
			/* translators: %s = terme recherché. */
			'label'      => sprintf( __( '« %s »', 'adaptours' ), $search ),
			'remove_url' => remove_query_arg( 'recherche' ),
		);
	}

	return $chips;
}
