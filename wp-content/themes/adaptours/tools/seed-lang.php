<?php
/**
 * Seed d'une langue secondaire : crée les traductions d'un échantillon de contenu pour
 * valider le pipeline multilingue sans attendre la saisie de la cliente.
 *
 * À exécuter après tools/setup-polylang.php :
 *   ADAPTOURS_SEED_LANG=es wp eval-file wp-content/themes/adaptours/tools/seed-lang.php
 *
 * Le contenu copié reste en français (la traduction éditoriale relève de la cliente) ;
 * l'objectif est seulement que chaque page existe, soit liée à son original et rende avec
 * ses blocs verrouillés. Idempotent : ne recrée pas une traduction déjà présente.
 *
 * Options (variables d'environnement) :
 *   ADAPTOURS_SEED_LANG   en|es   OBLIGATOIRE — slug Polylang de la langue à seeder
 *
 * Ce fichier n'est PAS chargé par functions.php : c'est un outil d'admin/CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return; // Accès direct hors WordPress.
}

if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) {
	echo "ERREUR : Polylang n'est pas actif.\n";
	return;
}

$adaptours_lang    = (string) getenv( 'ADAPTOURS_SEED_LANG' );
$adaptours_default = pll_default_language();
$adaptours_all     = (array) pll_languages_list();

if ( '' === $adaptours_lang ) {
	echo "ERREUR : définissez ADAPTOURS_SEED_LANG. Langues disponibles : " . implode( ', ', $adaptours_all ) . "\n";
	return;
}
if ( ! in_array( $adaptours_lang, $adaptours_all, true ) ) {
	echo "ERREUR : langue « {$adaptours_lang} » inconnue. Langues disponibles : " . implode( ', ', $adaptours_all ) . "\n";
	return;
}
if ( $adaptours_lang === $adaptours_default ) {
	echo "ERREUR : « {$adaptours_lang} » est la langue par défaut, rien à traduire.\n";
	return;
}

/** Slugs de pages par langue ; repli = slug source suffixé du slug de langue. */
$adaptours_seed_slugs = array(
	'en' => array(
		'home'    => 'home-en',
		'about'   => 'about',
		'contact' => 'contact',
		'quote'   => 'quote',
		'legal'   => 'legal-notice',
		'terms'   => 'terms',
		'privacy' => 'privacy-policy',
	),
	'es' => array(
		'home'    => 'home-es',
		'about'   => 'quienes-somos',
		'contact' => 'contacto',
		'quote'   => 'presupuesto',
		'legal'   => 'aviso-legal',
		'terms'   => 'condiciones-generales',
		'privacy' => 'politica-de-privacidad',
	),
);

/** Pages repérables par leur template. */
$adaptours_seed_templates = array(
	'about'   => 'template-qui-sommes-nous.php',
	'contact' => 'template-contact.php',
	'quote'   => 'template-devis.php',
);

/** Pages légales : même template pour les trois, on les repère donc par leur slug source. */
$adaptours_seed_by_slug = array(
	'legal'   => 'mentions-legales',
	'terms'   => 'cgv',
	'privacy' => 'politique-de-confidentialite',
);

$adaptours_slugs = isset( $adaptours_seed_slugs[ $adaptours_lang ] ) ? $adaptours_seed_slugs[ $adaptours_lang ] : array();

/**
 * Crée (ou retrouve) la traduction d'un post, copie son contenu et lie les deux.
 *
 * @param int         $src_id    ID du post source (langue par défaut).
 * @param string      $lang      Slug de langue cible.
 * @param string|null $slug      Slug souhaité (sinon dérivé du slug source).
 * @param bool        $copy_meta Copier toutes les métas (utile pour destination/avis).
 * @return int ID traduit (0 en cas d'échec).
 */
function adaptours_seed_translation( $src_id, $lang, $slug = null, $copy_meta = false ) {
	$src = get_post( $src_id );
	if ( ! $src ) {
		return 0;
	}

	$existing = (int) pll_get_post( $src_id, $lang );
	if ( $existing ) {
		return $existing;
	}

	$new_id = wp_insert_post(
		array(
			'post_type'    => $src->post_type,
			'post_status'  => 'publish',
			'post_title'   => $src->post_title,
			'post_content' => $src->post_content,
			'post_excerpt' => $src->post_excerpt,
			'post_name'    => $slug ? $slug : ( $src->post_name . '-' . $lang ),
			'menu_order'   => $src->menu_order,
		),
		true
	);

	if ( is_wp_error( $new_id ) || ! $new_id ) {
		echo "ERREUR insertion traduction de #$src_id : " . ( is_wp_error( $new_id ) ? $new_id->get_error_message() : '?' ) . "\n";
		return 0;
	}
	$new_id = (int) $new_id;

	$template = get_post_meta( $src_id, '_wp_page_template', true );
	if ( $template ) {
		update_post_meta( $new_id, '_wp_page_template', $template );
	}

	if ( $copy_meta ) {
		foreach ( get_post_meta( $src_id ) as $key => $values ) {
			if ( '_wp_page_template' === $key ) {
				continue;
			}
			delete_post_meta( $new_id, $key );
			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	$default = pll_default_language();
	if ( ! pll_get_post_language( $src_id ) ) {
		pll_set_post_language( $src_id, $default );
	}
	pll_set_post_language( $new_id, $lang );

	// pll_save_post_translations() ÉCRASE le groupe : sans fusion, seeder une 3e langue
	// délierait les traductions déjà en place.
	$translations = function_exists( 'pll_get_post_translations' )
		? (array) pll_get_post_translations( $src_id )
		: array();

	$translations[ $default ] = $src_id;
	$translations[ $lang ]    = $new_id;
	pll_save_post_translations( $translations );

	// Une fois la langue posée, Polylang autorise le même slug entre langues : on réimpose
	// le slug souhaité (l'insertion l'avait suffixé car la langue n'était pas encore connue).
	if ( $slug && get_post_field( 'post_name', $new_id ) !== $slug ) {
		wp_update_post( array( 'ID' => $new_id, 'post_name' => $slug ) );
	}

	return $new_id;
}

/** Première page utilisant un template donné, dans la langue par défaut. */
function adaptours_seed_page_by_template( $template, $lang ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
			'lang'           => $lang,
			'fields'         => 'ids',
		)
	);
	return $pages ? (int) $pages[0] : 0;
}

$adaptours_report = array();

// Page d'accueil.
$adaptours_front = (int) get_option( 'page_on_front' );
if ( $adaptours_front ) {
	$adaptours_report['home (front-page)'] = adaptours_seed_translation(
		$adaptours_front,
		$adaptours_lang,
		isset( $adaptours_slugs['home'] ) ? $adaptours_slugs['home'] : null
	);
}

// Pages à template (Qui sommes-nous, Contact, Devis).
foreach ( $adaptours_seed_templates as $adaptours_key => $adaptours_tpl ) {
	$adaptours_src = adaptours_seed_page_by_template( $adaptours_tpl, $adaptours_default );
	if ( $adaptours_src ) {
		$adaptours_report[ "page $adaptours_key" ] = adaptours_seed_translation(
			$adaptours_src,
			$adaptours_lang,
			isset( $adaptours_slugs[ $adaptours_key ] ) ? $adaptours_slugs[ $adaptours_key ] : null
		);
	}
}

// Pages légales (repérées par slug source).
foreach ( $adaptours_seed_by_slug as $adaptours_key => $adaptours_src_slug ) {
	$adaptours_page = get_page_by_path( $adaptours_src_slug );
	if ( ! $adaptours_page ) {
		continue;
	}
	$adaptours_report[ "page $adaptours_key" ] = adaptours_seed_translation(
		(int) $adaptours_page->ID,
		$adaptours_lang,
		isset( $adaptours_slugs[ $adaptours_key ] ) ? $adaptours_slugs[ $adaptours_key ] : null
	);
}

// Une destination + un avis (avec leurs métas).
foreach ( array( 'destination', 'avis' ) as $adaptours_cpt ) {
	$adaptours_posts = get_posts(
		array(
			'post_type'      => $adaptours_cpt,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'lang'           => $adaptours_default,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);
	if ( $adaptours_posts ) {
		$adaptours_report[ $adaptours_cpt ] = adaptours_seed_translation( (int) $adaptours_posts[0], $adaptours_lang, null, true );
	}
}

flush_rewrite_rules();

foreach ( $adaptours_report as $adaptours_label => $adaptours_id ) {
	$adaptours_url = $adaptours_id ? get_permalink( $adaptours_id ) : '—';
	echo sprintf( "%-22s %s #%-4d %s\n", $adaptours_label, strtoupper( $adaptours_lang ), $adaptours_id, $adaptours_url );
}
echo "OK\n";
