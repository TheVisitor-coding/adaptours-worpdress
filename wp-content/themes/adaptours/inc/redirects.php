<?php
/**
 * Redirections 301 des anciennes URL de production.
 *
 * La refonte change l'arborescence : les fiches pays passent de /voyages-mobilite-reduite/<slug>/
 * à /destinations/<slug>/. Sans redirection permanente, les 50 URL déjà indexées renvoient une
 * erreur et l'ancienneté accumulée est perdue. La table ci-dessous couvre l'intégralité du plan
 * de site de l'ancien site.
 *
 * Volontairement en code plutôt qu'en extension : la table est versionnée avec le thème et
 * déployée par deploy/deploy-theme.sh, ce qui garde l'historique des arbitrages.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ancien préfixe des fiches destination.
 */
const ADAPTOURS_LEGACY_DEST_PREFIX = 'voyages-mobilite-reduite';

/**
 * Fiches dont le slug change, et pages hors destinations.
 *
 * Clé : ancien chemin, sans slash initial ni final. Valeur : nouveau chemin relatif à la racine.
 * Les slugs inchangés ne figurent pas ici, ils sont traités par la règle de préfixe.
 *
 * @return array<string,string>
 */
function adaptours_legacy_redirect_map() {
	return array(
		// Fiches renommées.
		ADAPTOURS_LEGACY_DEST_PREFIX . '/safari-kenya'      => '/destinations/kenya/',
		ADAPTOURS_LEGACY_DEST_PREFIX . '/pays-bas-2'        => '/destinations/pays-bas/',
		ADAPTOURS_LEGACY_DEST_PREFIX . '/portugal-madere-2' => '/destinations/portugal-madere/',

		// Pages hors destinations, équivalent direct.
		'contact-adaptours'                                 => '/contact/',
		'demande-de-devis'                                  => '/devis/',
		'gite-adaptours-location-vacances-pmr'              => '/gites-adaptours/',
		ADAPTOURS_LEGACY_DEST_PREFIX                        => '/destinations/',
		'tour-category/destinations'                        => '/destinations/',

		// Fiches sans équivalent : redirigées vers le plus proche par sujet.
		ADAPTOURS_LEGACY_DEST_PREFIX . '/croisiere-new-york' => '/destinations/new-york/',
		ADAPTOURS_LEGACY_DEST_PREFIX . '/france'            => '/destinations/corse/',
		ADAPTOURS_LEGACY_DEST_PREFIX . '/bordeaux'          => '/destinations/',
		ADAPTOURS_LEGACY_DEST_PREFIX . '/californie'        => '/destinations/',
		ADAPTOURS_LEGACY_DEST_PREFIX . '/israel'            => '/destinations/',
		ADAPTOURS_LEGACY_DEST_PREFIX . '/morzine'           => '/destinations/',

		// Pages de contenu et boutique sans équivalent.
		'croisieres'                                        => '/destinations/',
		'en-petit-groupe'                                   => '/destinations/',
		'sejours-haut-de-gamme'                             => '/destinations/',
		'voyages-longues-durees'                            => '/destinations/',
		'organiser-son-voyage'                              => '/devis/',
		'recherche-accompagnateurs'                         => '/contact/',
		'liens-utiles'                                      => '/qui-sommes-nous/',
		'shop'                                              => '/destinations/',
		'categorie-produit/voyages'                         => '/destinations/',
	);
}

/**
 * Normalise un chemin d'URL pour la comparaison.
 *
 * Retire les slashs de bord et passe en minuscules, afin de tolérer les variantes rencontrées
 * dans les liens entrants (slash final absent, casse hétérogène).
 *
 * @param string $path Chemin brut.
 * @return string
 */
function adaptours_normalize_legacy_path( $path ) {
	return strtolower( trim( (string) $path, '/' ) );
}

/**
 * Résout la cible d'une ancienne URL.
 *
 * @param string $path Chemin demandé, déjà normalisé.
 * @return string Chemin cible relatif, ou '' si aucune redirection ne s'applique.
 */
function adaptours_resolve_legacy_redirect( $path ) {
	if ( '' === $path ) {
		return '';
	}

	$map = adaptours_legacy_redirect_map();
	if ( isset( $map[ $path ] ) ) {
		return $map[ $path ];
	}

	// Règle de préfixe : les fiches dont le slug est inchangé basculent telles quelles.
	$prefix = ADAPTOURS_LEGACY_DEST_PREFIX . '/';
	if ( 0 === strpos( $path, $prefix ) ) {
		$slug = substr( $path, strlen( $prefix ) );

		// Une fiche est un segment unique : on ignore les sous-niveaux et la pagination.
		if ( '' !== $slug && false === strpos( $slug, '/' ) ) {
			return '/destinations/' . $slug . '/';
		}
	}

	return '';
}

/**
 * Redirige les anciennes URL de production vers leur équivalent actuel.
 *
 * Priorité 4 pour passer avant adaptours_seo_canonicalize_front_permalink() (priorité 5),
 * qui n'a pas à traiter ces chemins.
 */
function adaptours_legacy_redirects() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$requested = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( '' === $requested ) {
		return;
	}

	$path = adaptours_normalize_legacy_path( (string) wp_parse_url( $requested, PHP_URL_PATH ) );
	$target = adaptours_resolve_legacy_redirect( $path );

	if ( '' === $target ) {
		return;
	}

	$destination = home_url( $target );

	// Préserve les paramètres d'URL (campagnes, suivi).
	$query = (string) wp_parse_url( $requested, PHP_URL_QUERY );
	if ( '' !== $query ) {
		$destination = add_query_arg( wp_parse_args( $query ), $destination );
	}

	// Garde-fou : ne jamais rediriger une URL vers elle-même.
	if ( adaptours_normalize_legacy_path( (string) wp_parse_url( $destination, PHP_URL_PATH ) ) === $path ) {
		return;
	}

	wp_safe_redirect( $destination, 301 );
	exit;
}
add_action( 'template_redirect', 'adaptours_legacy_redirects', 4 );
