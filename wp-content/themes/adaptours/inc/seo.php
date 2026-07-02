<?php
/**
 * SEO maison (§7) : hreflang, canonical, OpenGraph et schema.org générés par le thème.
 *
 * Pas de plugin SEO (Yoast/Rank Math exclus). Ciblage par langue (fr/en/es), pas par pays.
 * Tout appel pll_* est gardé par function_exists() : sans Polylang, le thème reste fonctionnel.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Racine de langue fiable (ex. /en/).
 *
 * pll_home_url() renvoie le permalink de la page d'accueil traduite (/en/home-en/) au lieu de la
 * racine de langue : bug de cache documenté (MULTILINGUE.md). On passe par le links_model de Polylang,
 * qui donne la bonne valeur, avec repli sur une construction manuelle (structure en répertoire).
 *
 * @param string $slug Slug de langue (ex. 'fr', 'en').
 * @return string
 */
function adaptours_language_home_url( $slug ) {
	if ( function_exists( 'PLL' ) && PLL() && isset( PLL()->links_model, PLL()->model ) ) {
		$lang = PLL()->model->get_language( $slug );
		if ( $lang ) {
			return PLL()->links_model->home_url( $lang );
		}
	}

	$default = function_exists( 'pll_default_language' ) ? pll_default_language() : '';
	return ( $slug && $slug !== $default ) ? home_url( '/' . $slug . '/' ) : home_url( '/' );
}

/**
 * URL canonique du contexte courant.
 *
 * La page d'accueil traduite (ex. /en/) est servie à la racine de langue mais son permalink
 * est /en/home-en/ : on force la racine de langue pour éviter un canonical divergent de l'URL servie.
 *
 * @return string URL canonique, ou '' si le contexte ne doit pas en porter (search, 404, pagination).
 */
function adaptours_seo_current_url() {
	if ( is_front_page()
		|| ( is_page() && adaptours_is_front_page_in_any_language( get_queried_object_id() ) ) ) {
		$slug = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
		return $slug ? adaptours_language_home_url( $slug ) : home_url( '/' );
	}

	if ( is_singular() ) {
		$id = get_queried_object_id();
		return $id ? (string) get_permalink( $id ) : '';
	}

	if ( is_post_type_archive( 'destination' ) ) {
		return (string) get_post_type_archive_link( 'destination' );
	}

	return '';
}

/**
 * Corrige et complète les balises hreflang de Polylang.
 *
 * Sur une page d'accueil traduite, Polylang pointe le hreflang vers le permalink (/en/home-en/)
 * au lieu de la racine de langue (/en/) : on réaligne sur pll_home_url(). On ajoute aussi x-default
 * vers la langue par défaut (recommandation Google pour le fallback).
 *
 * @param array<string,string> $hreflangs Map slug de langue => URL, fournie par Polylang.
 * @return array<string,string>
 */
function adaptours_seo_hreflang_attributes( $hreflangs ) {
	if ( ! function_exists( 'pll_home_url' ) || ! is_array( $hreflangs ) ) {
		return $hreflangs;
	}

	if ( adaptours_is_front_page_in_any_language( get_queried_object_id() ) ) {
		foreach ( array_keys( $hreflangs ) as $slug ) {
			$hreflangs[ $slug ] = adaptours_language_home_url( $slug );
		}
	}

	if ( function_exists( 'pll_default_language' ) ) {
		$default = pll_default_language();
		if ( $default && isset( $hreflangs[ $default ] ) ) {
			$hreflangs['x-default'] = $hreflangs[ $default ];
		}
	}

	return $hreflangs;
}
add_filter( 'pll_rel_hreflang_attributes', 'adaptours_seo_hreflang_attributes' );

/**
 * Balise canonical du thème (remplace celle du core, absente sur les archives et divergente
 * sur la home traduite).
 */
function adaptours_seo_canonical() {
	$url = adaptours_seo_current_url();
	if ( '' !== $url ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
	}
}
remove_action( 'wp_head', 'rel_canonical' );
add_action( 'wp_head', 'adaptours_seo_canonical', 10 );

/**
 * Meta description du contexte courant, dérivée du contenu éditorial.
 *
 * On n'utilise que du texte saisi (accroche, extrait manuel, chapô) et jamais l'extrait
 * auto-généré à partir des blocs (illisible). Repli sur le slogan du site.
 *
 * @return string Description nettoyée, bornée à ~160 caractères.
 */
function adaptours_seo_description() {
	$desc = '';

	if ( is_singular( 'destination' ) ) {
		$id   = get_queried_object_id();
		$desc = (string) adaptours_get_destination_meta( $id, 'hero_accroche' );
		if ( '' === trim( $desc ) && has_excerpt( $id ) ) {
			$desc = (string) get_the_excerpt( $id );
		}
	} elseif ( is_post_type_archive( 'destination' ) ) {
		$desc = (string) adaptours_get_option( 'dest_intro' );
	} elseif ( is_singular() && has_excerpt( get_queried_object_id() ) ) {
		$desc = (string) get_the_excerpt( get_queried_object_id() );
	}

	if ( '' === trim( $desc ) ) {
		$desc = (string) get_bloginfo( 'description' );
	}

	$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $desc ) ) );
	if ( mb_strlen( $desc ) > 160 ) {
		$desc = rtrim( mb_substr( $desc, 0, 157 ) ) . '…';
	}

	return $desc;
}

/**
 * Meta description + OpenGraph (dont og:locale / og:locale:alternate pour le multilingue).
 */
function adaptours_seo_meta() {
	$is_front = is_front_page()
		|| ( is_page() && adaptours_is_front_page_in_any_language( get_queried_object_id() ) );

	$description = adaptours_seo_description();
	$url         = adaptours_seo_current_url();
	$type        = is_singular( 'destination' ) ? 'article' : 'website';

	$image = '';
	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$image = (string) get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
	}
	if ( '' === $image ) {
		$image = (string) get_site_icon_url( 512 );
	}

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( wp_get_document_title() ) );
	if ( '' !== $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	if ( '' !== $url ) {
		printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	}
	if ( '' !== $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
	}

	$current_locale = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'locale' ) : get_locale();
	if ( '' !== $current_locale ) {
		printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( $current_locale ) );
	}
	if ( function_exists( 'pll_the_languages' ) ) {
		$langs = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
		foreach ( (array) $langs as $lang ) {
			if ( empty( $lang['current_lang'] ) && ! empty( $lang['locale'] ) ) {
				// pll_the_languages renvoie la locale au format W3C (en-US) ; OpenGraph exige le format WP (en_US).
				$alt_locale = str_replace( '-', '_', (string) $lang['locale'] );
				printf( '<meta property="og:locale:alternate" content="%s" />' . "\n", esc_attr( $alt_locale ) );
			}
		}
	}
}
add_action( 'wp_head', 'adaptours_seo_meta', 5 );

/**
 * Nœud schema.org de l'agence (identité de marque, réseaux, coordonnées).
 *
 * @param string $locale Locale WP courante.
 * @return array<string,mixed>
 */
function adaptours_seo_jsonld_organization( $locale ) {
	$slug = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	$node = array(
		'@type' => 'TravelAgency',
		'name'  => get_bloginfo( 'name' ),
		'url'   => $slug ? adaptours_language_home_url( $slug ) : home_url( '/' ),
	);

	$logo = get_site_icon_url( 512 );
	if ( $logo ) {
		$node['logo'] = $logo;
	}
	$email = (string) adaptours_get_option( 'email' );
	if ( '' !== $email ) {
		$node['email'] = $email;
	}
	$tel = (string) adaptours_get_option( 'tel_link' );
	if ( '' !== $tel ) {
		$node['telephone'] = $tel;
	}
	$address = (string) adaptours_get_option( 'adresse' );
	if ( '' !== trim( $address ) ) {
		$node['address'] = array(
			'@type'         => 'PostalAddress',
			'streetAddress' => trim( preg_replace( '/\s+/', ' ', $address ) ),
		);
	}
	$same_as = array_values( array_filter( array(
		(string) adaptours_get_option( 'url_facebook' ),
		(string) adaptours_get_option( 'url_instagram' ),
		(string) adaptours_get_option( 'url_linkedin' ),
	) ) );
	if ( $same_as ) {
		$node['sameAs'] = $same_as;
	}
	if ( $locale ) {
		$node['inLanguage'] = $locale;
	}

	return $node;
}

/**
 * Nœud schema.org du site (pour le nom de site dans les SERP).
 *
 * @param string $locale Locale WP courante.
 * @return array<string,mixed>
 */
function adaptours_seo_jsonld_website( $locale ) {
	$slug = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	$node = array(
		'@type' => 'WebSite',
		'name'  => get_bloginfo( 'name' ),
		'url'   => $slug ? adaptours_language_home_url( $slug ) : home_url( '/' ),
	);
	if ( $locale ) {
		$node['inLanguage'] = $locale;
	}

	return $node;
}

/**
 * Nœud schema.org d'une destination.
 *
 * Type TouristTrip (point ouvert #42 : TouristTrip vs Place/TouristDestination) — isolé ici
 * pour faciliter la bascule si la cliente tranche autrement.
 *
 * @param int    $id     ID de la destination.
 * @param string $locale Locale WP courante.
 * @return array<string,mixed>
 */
function adaptours_seo_jsonld_destination( $id, $locale ) {
	$node = array(
		'@type' => 'TouristTrip',
		'name'  => get_the_title( $id ),
		'url'   => get_permalink( $id ),
	);

	$desc = adaptours_seo_description();
	if ( '' !== $desc ) {
		$node['description'] = $desc;
	}
	if ( has_post_thumbnail( $id ) ) {
		$img = (string) get_the_post_thumbnail_url( $id, 'large' );
		if ( '' !== $img ) {
			$node['image'] = $img;
		}
	}
	$price = adaptours_get_destination_meta( $id, 'prix_a_partir_de' );
	if ( '' !== (string) $price && (float) $price > 0 ) {
		$node['offers'] = array(
			'@type'         => 'Offer',
			'price'         => (string) (float) $price,
			'priceCurrency' => 'EUR',
			'availability'  => 'https://schema.org/InStock',
		);
	}
	if ( $locale ) {
		$node['inLanguage'] = $locale;
	}

	return $node;
}

/**
 * Émet le JSON-LD schema.org du contexte (agence + site sur la home, TouristTrip sur une destination).
 */
function adaptours_seo_jsonld() {
	$is_front = is_front_page()
		|| ( is_page() && adaptours_is_front_page_in_any_language( get_queried_object_id() ) );
	$locale = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'locale' ) : get_locale();

	$nodes = array();
	if ( $is_front ) {
		$nodes[] = adaptours_seo_jsonld_organization( $locale );
		$nodes[] = adaptours_seo_jsonld_website( $locale );
	} elseif ( is_singular( 'destination' ) ) {
		$nodes[] = adaptours_seo_jsonld_destination( get_queried_object_id(), $locale );
	}

	$nodes = array_filter( $nodes );
	if ( empty( $nodes ) ) {
		return;
	}

	if ( 1 === count( $nodes ) ) {
		$data = array_merge( array( '@context' => 'https://schema.org' ), reset( $nodes ) );
	} else {
		$data = array(
			'@context' => 'https://schema.org',
			'@graph'   => array_values( $nodes ),
		);
	}

	echo '<script type="application/ld+json">'
		. wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG )
		. '</script>' . "\n";
}
add_action( 'wp_head', 'adaptours_seo_jsonld', 20 );

/**
 * Redirige (301) le permalink d'une page d'accueil vers sa racine de langue.
 *
 * page_on_front (et ses traductions) reste accessible à son permalink (/accueil/, /en/home-en/) :
 * le core ne redirige pas /en/home-en/, et le filtre redirect_canonical est neutralisé pour /en/.
 * On supprime ces doublons en pointant vers la racine de langue servie (/, /en/).
 */
function adaptours_seo_canonicalize_front_permalink() {
	if ( is_admin() || wp_doing_ajax() || is_preview() || is_customize_preview() ) {
		return;
	}
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' !== strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) ) {
		return;
	}
	if ( ! ( is_page() && adaptours_is_front_page_in_any_language( get_queried_object_id() ) ) ) {
		return;
	}

	$slug = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	if ( ! $slug ) {
		return;
	}

	$home      = adaptours_language_home_url( $slug );
	$home_path = untrailingslashit( (string) wp_parse_url( $home, PHP_URL_PATH ) );
	$req_uri   = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$req_path  = untrailingslashit( (string) wp_parse_url( $req_uri, PHP_URL_PATH ) );

	if ( $req_path !== $home_path ) {
		wp_safe_redirect( $home, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'adaptours_seo_canonicalize_front_permalink', 5 );
