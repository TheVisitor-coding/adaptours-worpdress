<?php
/**
 * Recette multilingue : vérifie l'infrastructure (langues, traductions, formulaires, menus,
 * termes) puis le rendu réel de chaque page dans chaque langue.
 *
 * À exécuter après le provisioning :
 *   wp eval-file wp-content/themes/adaptours/tools/verify-langues.php
 *
 * Les pages sont récupérées en interne (wp_remote_get) et seules des lignes OK/KO courtes sont
 * imprimées : la sortie reste lisible et exploitable en CI comme en SSH.
 *
 * Options (variables d'environnement) :
 *   ADAPTOURS_VERIFY_BASE  http://wordpress   remplace le schéma+hôte des URLs testées.
 *                          Nécessaire sous wp-env : depuis le conteneur CLI, le port publié
 *                          sur l'hôte (localhost:8888) n'est pas joignable.
 *
 * Ce fichier n'est PAS chargé par functions.php : c'est un outil d'admin/CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return; // Accès direct hors WordPress.
}

if ( ! function_exists( 'pll_languages_list' ) ) {
	echo "ERREUR : Polylang n'est pas actif.\n";
	return;
}

$adaptours_ok = 0;
$adaptours_ko = 0;

$adaptours_check = function ( $label, $condition, $detail = '' ) use ( &$adaptours_ok, &$adaptours_ko ) {
	if ( $condition ) {
		++$adaptours_ok;
		echo "OK   $label\n";
	} else {
		++$adaptours_ko;
		echo "KO   $label" . ( '' !== $detail ? "  ($detail)" : '' ) . "\n";
	}
};

$adaptours_langs   = (array) pll_languages_list();
$adaptours_default = pll_default_language();

echo "=== 1. Langues ===\n";
echo 'langues : ' . implode( ', ', $adaptours_langs ) . ' | défaut : ' . $adaptours_default . "\n";
$adaptours_check( 'au moins 2 langues', count( $adaptours_langs ) >= 2 );

foreach ( $adaptours_langs as $adaptours_lang ) {
	$adaptours_obj = PLL()->model->get_language( $adaptours_lang );
	$adaptours_check(
		"[$adaptours_lang] locale + drapeau",
		$adaptours_obj && $adaptours_obj->locale && '' !== adaptours_flag_svg( $adaptours_obj->flag_code ),
		$adaptours_obj ? $adaptours_obj->locale . ' / ' . $adaptours_obj->flag_code : 'langue introuvable'
	);
}

echo "\n=== 2. Traductions d'interface (.mo) ===\n";
$adaptours_available = get_available_languages();
foreach ( $adaptours_langs as $adaptours_lang ) {
	$adaptours_locale = PLL()->model->get_language( $adaptours_lang )->locale;

	if ( $adaptours_default === $adaptours_lang ) {
		continue; // Langue source : les msgid sont déjà dans cette langue.
	}

	// switch_to_locale() n'accepte que les locales installées : sans le pack du cœur, il est
	// refusé en silence et tout ce qui est construit sous ce switch sort en langue source.
	$adaptours_check(
		"[$adaptours_lang] pack de langue du cœur installé",
		'en_US' === $adaptours_locale || in_array( $adaptours_locale, $adaptours_available, true ),
		$adaptours_locale
	);

	switch_to_locale( $adaptours_locale );
	$adaptours_check( "[$adaptours_lang] locale effectivement active", determine_locale() === $adaptours_locale, determine_locale() );
	$adaptours_check( "[$adaptours_lang] textdomain adaptours chargé", is_textdomain_loaded( 'adaptours' ) );
	$adaptours_check( "[$adaptours_lang] chaîne front traduite", __( 'Demander un devis', 'adaptours' ) !== 'Demander un devis' );
	restore_previous_locale();
}

echo "\n=== 3. Valeurs par défaut des blocs ===\n";
foreach ( $adaptours_langs as $adaptours_lang ) {
	if ( $adaptours_default === $adaptours_lang ) {
		continue;
	}
	switch_to_locale( PLL()->model->get_language( $adaptours_lang )->locale );

	$adaptours_block  = array( 'blockName' => 'adaptours/hero-home', 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() );
	$adaptours_parsed = apply_filters( 'render_block_data', $adaptours_block, $adaptours_block, null );
	$adaptours_attrs  = (array) $adaptours_parsed['attrs'];

	$adaptours_check(
		"[$adaptours_lang] defaults de blocs traduits",
		isset( $adaptours_attrs['cta_primary_label'] ) && 'Commencer mon voyage' !== $adaptours_attrs['cta_primary_label'],
		$adaptours_attrs['cta_primary_label'] ?? '(non injecté)'
	);
	$adaptours_check(
		"[$adaptours_lang] listes multi-lignes préservées",
		isset( $adaptours_attrs['rotator_words'] ) && 5 === count( explode( "\n", $adaptours_attrs['rotator_words'] ) )
	);
	restore_previous_locale();
}

echo "\n=== 4. Formulaires Contact Form 7 ===\n";
$adaptours_forms = array();
foreach ( array( 'contact' => 'adaptours_contact_form_id', 'devis' => 'adaptours_devis_form_id' ) as $adaptours_kind => $adaptours_base ) {
	foreach ( $adaptours_langs as $adaptours_lang ) {
		$adaptours_option = ( $adaptours_lang === $adaptours_default ) ? $adaptours_base : $adaptours_base . '_' . $adaptours_lang;
		$adaptours_id     = (int) get_option( $adaptours_option, 0 );

		$adaptours_forms[ $adaptours_kind ][ $adaptours_lang ] = $adaptours_id;

		$adaptours_check(
			"[$adaptours_lang] formulaire $adaptours_kind créé",
			$adaptours_id > 0 && 'wpcf7_contact_form' === get_post_type( $adaptours_id ),
			'#' . $adaptours_id
		);
		$adaptours_check(
			"[$adaptours_lang] formulaire $adaptours_kind dans la bonne langue",
			$adaptours_id && pll_get_post_language( $adaptours_id ) === $adaptours_lang
		);
	}

	$adaptours_titles = array();
	foreach ( $adaptours_forms[ $adaptours_kind ] as $adaptours_id ) {
		if ( $adaptours_id ) {
			$adaptours_titles[] = get_the_title( $adaptours_id );
		}
	}
	$adaptours_check(
		"titres $adaptours_kind distincts",
		count( $adaptours_titles ) === count( array_unique( $adaptours_titles ) ),
		implode( ' / ', $adaptours_titles )
	);

	$adaptours_first = $adaptours_forms[ $adaptours_kind ][ $adaptours_default ];
	$adaptours_check(
		"formulaires $adaptours_kind liés entre eux",
		$adaptours_first && count( (array) pll_get_post_translations( $adaptours_first ) ) === count( $adaptours_langs ),
		wp_json_encode( pll_get_post_translations( $adaptours_first ) )
	);
}

// Les if_value des règles conditionnelles doivent matcher au caractère près les libellés
// rendus par le [radio devis-statut] : ils sont générés dans la même locale, on le prouve.
foreach ( $adaptours_langs as $adaptours_lang ) {
	$adaptours_id = $adaptours_forms['devis'][ $adaptours_lang ];
	if ( ! $adaptours_id ) {
		continue;
	}
	$adaptours_body  = WPCF7_ContactForm::get_instance( $adaptours_id )->prop( 'form' );
	$adaptours_rules = (array) get_post_meta( $adaptours_id, 'wpcf7cf_options', true );
	$adaptours_bad   = array();

	foreach ( $adaptours_rules as $adaptours_rule ) {
		$adaptours_value = $adaptours_rule['and_rules'][0]['if_value'] ?? '';
		if ( '' === $adaptours_value || false === strpos( $adaptours_body, '"' . $adaptours_value . '"' ) ) {
			$adaptours_bad[] = $adaptours_value;
		}
	}
	$adaptours_check( "[$adaptours_lang] 5 règles conditionnelles", 5 === count( $adaptours_rules ), (string) count( $adaptours_rules ) );
	$adaptours_check( "[$adaptours_lang] if_value alignés sur les libellés radio", empty( $adaptours_bad ), implode( ' / ', $adaptours_bad ) );
}

echo "\n=== 5. Termes de taxonomie ===\n";
$adaptours_terms   = get_terms( array( 'taxonomy' => 'zone_geographique', 'hide_empty' => false ) );
$adaptours_no_lang = 0;
foreach ( (array) $adaptours_terms as $adaptours_term ) {
	if ( ! pll_get_term_language( $adaptours_term->term_id ) ) {
		++$adaptours_no_lang;
	}
}
$adaptours_check( 'aucun terme sans langue', 0 === $adaptours_no_lang, (string) $adaptours_no_lang );

foreach ( $adaptours_langs as $adaptours_lang ) {
	$adaptours_in_lang = get_terms(
		array(
			'taxonomy'   => 'zone_geographique',
			'hide_empty' => false,
			'lang'       => $adaptours_lang,
		)
	);
	$adaptours_check( "[$adaptours_lang] zones géographiques présentes", ! empty( $adaptours_in_lang ), (string) count( (array) $adaptours_in_lang ) );
}

echo "\n=== 6. Menus ===\n";
$adaptours_map = get_option( 'polylang' )['nav_menus'][ get_stylesheet() ] ?? array();
foreach ( array( 'primary', 'footer_1', 'footer_2' ) as $adaptours_location ) {
	foreach ( $adaptours_langs as $adaptours_lang ) {
		$adaptours_menu = (int) ( $adaptours_map[ $adaptours_location ][ $adaptours_lang ] ?? 0 );
		$adaptours_check(
			"[$adaptours_lang] menu $adaptours_location assigné",
			$adaptours_menu > 0 && ! empty( wp_get_nav_menu_items( $adaptours_menu ) ),
			'#' . $adaptours_menu
		);
	}
}

echo "\n=== 7. Rendu des pages ===\n";
$adaptours_pages = array();
foreach ( $adaptours_langs as $adaptours_lang ) {
	$adaptours_pages[ $adaptours_lang ] = array( 'accueil' => PLL()->links_model->home_url( $adaptours_lang ) );
}

$adaptours_front = (int) get_option( 'page_on_front' );
foreach ( array( 'template-qui-sommes-nous.php', 'template-contact.php', 'template-devis.php' ) as $adaptours_template ) {
	$adaptours_source = get_posts(
		array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $adaptours_template,
			'lang'           => $adaptours_default,
			'fields'         => 'ids',
		)
	);
	if ( ! $adaptours_source ) {
		continue;
	}
	foreach ( $adaptours_langs as $adaptours_lang ) {
		$adaptours_translated = (int) pll_get_post( (int) $adaptours_source[0], $adaptours_lang );
		if ( $adaptours_translated && $adaptours_translated !== $adaptours_front ) {
			$adaptours_pages[ $adaptours_lang ][ $adaptours_template ] = get_permalink( $adaptours_translated );
		}
	}
}

foreach ( $adaptours_langs as $adaptours_lang ) {
	$adaptours_home = rtrim( PLL()->links_model->home_url( $adaptours_lang ), '/' );
	$adaptours_pages[ $adaptours_lang ]['archive destinations'] = $adaptours_home . '/destinations/';
}

$adaptours_base = rtrim( (string) getenv( 'ADAPTOURS_VERIFY_BASE' ), '/' );
if ( '' !== $adaptours_base ) {
	echo "base de test : $adaptours_base\n";
}

$adaptours_bodies = array();
foreach ( $adaptours_pages as $adaptours_lang => $adaptours_urls ) {
	foreach ( $adaptours_urls as $adaptours_label => $adaptours_url ) {
		if ( '' !== $adaptours_base ) {
			$adaptours_url = $adaptours_base . (string) wp_parse_url( $adaptours_url, PHP_URL_PATH );
		}

		$adaptours_response = wp_remote_get( $adaptours_url, array( 'timeout' => 20, 'sslverify' => false ) );
		$adaptours_code     = is_wp_error( $adaptours_response ) ? 0 : (int) wp_remote_retrieve_response_code( $adaptours_response );
		$adaptours_body     = is_wp_error( $adaptours_response ) ? '' : (string) wp_remote_retrieve_body( $adaptours_response );

		$adaptours_bodies[ $adaptours_lang ][ $adaptours_label ] = $adaptours_body;
		$adaptours_check( "[$adaptours_lang] $adaptours_label : HTTP 200", 200 === $adaptours_code, $adaptours_code . ' ' . $adaptours_url );
	}
}

echo "\n=== 8. Balisage multilingue (page d'accueil) ===\n";
foreach ( $adaptours_langs as $adaptours_lang ) {
	$adaptours_html   = $adaptours_bodies[ $adaptours_lang ]['accueil'] ?? '';
	$adaptours_locale = PLL()->model->get_language( $adaptours_lang )->locale;

	$adaptours_check(
		"[$adaptours_lang] hreflang : une par langue + x-default",
		substr_count( $adaptours_html, 'rel="alternate"' ) >= count( $adaptours_langs ) + 1
			&& false !== strpos( $adaptours_html, 'hreflang="x-default"' ),
		substr_count( $adaptours_html, 'rel="alternate"' ) . ' alternates'
	);
	$adaptours_check(
		"[$adaptours_lang] og:locale",
		false !== strpos( $adaptours_html, 'og:locale" content="' . $adaptours_locale )
	);
	$adaptours_check(
		"[$adaptours_lang] sélecteur de langue complet",
		substr_count( $adaptours_html, 'site-header__lang-item' ) === count( $adaptours_langs ),
		substr_count( $adaptours_html, 'site-header__lang-item' ) . ' entrées'
	);
	$adaptours_check(
		"[$adaptours_lang] un seul H1",
		1 === substr_count( $adaptours_html, '<h1' ),
		substr_count( $adaptours_html, '<h1' ) . ' H1'
	);
}

echo "\n=== 9. debug.log ===\n";
$adaptours_log = WP_CONTENT_DIR . '/debug.log';
$adaptours_php = 0;
if ( file_exists( $adaptours_log ) ) {
	$adaptours_php = preg_match_all( '/PHP (Notice|Warning|Fatal|Deprecated)/', (string) file_get_contents( $adaptours_log ) );
}
$adaptours_check( 'aucune erreur PHP', 0 === $adaptours_php, $adaptours_php . ' occurrence(s)' );

echo "\n=== $adaptours_ok OK / $adaptours_ko KO ===\n";
