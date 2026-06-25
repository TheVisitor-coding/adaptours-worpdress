<?php
/**
 * Provisioning Polylang — crée les langues du site, idempotent.
 *
 * À exécuter via WP-CLI après chaque (re)création de l'environnement wp-env :
 *   wp eval-file wp-content/themes/adaptours/tools/setup-polylang.php
 *
 * Stratégie d'URL (options Polylang gratuites, valeurs par défaut conservées) :
 *   - force_lang = 1   → la langue est portée par le répertoire (/en/…).
 *   - hide_default = 1 → la langue par défaut (FR) reste sans préfixe.
 *
 * Pour ajouter l'espagnol (2e passe) : décommenter l'entrée « es » ci-dessous,
 * puis relancer le script (les langues déjà créées sont ignorées).
 *
 * Ce fichier n'est PAS chargé par functions.php : c'est un outil d'admin/CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return; // Accès direct hors WordPress.
}

if ( ! function_exists( 'PLL' ) || ! PLL() || ! isset( PLL()->model ) ) {
	echo "ERREUR : Polylang n'est pas actif.\n";
	return;
}

/**
 * Langues du site, dans l'ordre d'affichage.
 * La 1re créée devient la langue par défaut si aucune n'est encore définie.
 */
$adaptours_languages = array(
	array( 'name' => 'Français', 'slug' => 'fr', 'locale' => 'fr_FR', 'rtl' => 0, 'term_group' => 0, 'flag' => 'fr' ),
	array( 'name' => 'English',  'slug' => 'en', 'locale' => 'en_US', 'rtl' => 0, 'term_group' => 1, 'flag' => 'us' ),
	// array( 'name' => 'Español', 'slug' => 'es', 'locale' => 'es_ES', 'rtl' => 0, 'term_group' => 2, 'flag' => 'es' ),
);
$adaptours_default_lang = 'fr';

$model = PLL()->model;

// Slugs déjà présents (idempotence).
$existing = array();
foreach ( $model->get_languages_list() as $lang ) {
	$existing[] = $lang->slug;
}

foreach ( $adaptours_languages as $args ) {
	if ( in_array( $args['slug'], $existing, true ) ) {
		echo "= langue « {$args['slug']} » déjà présente, ignorée\n";
		continue;
	}

	$result = $model->languages->add( $args );

	if ( is_wp_error( $result ) ) {
		echo "ERREUR ajout « {$args['slug']} » : " . $result->get_error_message() . "\n";
	} else {
		echo "+ langue « {$args['slug']} » créée\n";
	}
}

// Langue par défaut = FR (idempotent : no-op si déjà le cas).
if ( method_exists( $model->languages, 'update_default' ) ) {
	$model->languages->update_default( $adaptours_default_lang );
} elseif ( isset( PLL()->options ) ) {
	PLL()->options->set( 'default_lang', $adaptours_default_lang );
}

// Assigne la langue par défaut (FR) à tout le contenu existant sans langue
// (pages, destinations, avis, termes). Idempotent : ne touche que ce qui manque.
if ( method_exists( $model, 'set_language_in_mass' ) ) {
	$model->set_language_in_mass();
}

// Persiste les options et purge les caches de langues.
if ( isset( PLL()->options ) && method_exists( PLL()->options, 'save_all' ) ) {
	PLL()->options->save_all();
}
if ( method_exists( $model, 'clean_languages_cache' ) ) {
	$model->clean_languages_cache();
}

// Les règles de réécriture dépendent des langues : on les régénère.
flush_rewrite_rules();

echo "default_lang = " . ( PLL()->options->get( 'default_lang' ) ?: '?' ) . "\n";
echo "Langues actuelles : ";
$slugs = array();
foreach ( $model->get_languages_list() as $lang ) {
	$slugs[] = $lang->slug;
}
echo implode( ', ', $slugs ) . "\n";
echo "OK\n";
