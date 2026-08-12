<?php
/**
 * Installe les packs de langue WordPress (cœur + extensions), idempotent.
 *
 * À exécuter via WP-CLI :
 *   wp eval-file wp-content/themes/adaptours/tools/install-language-packs.php
 *
 * Pourquoi ce script : `wp-content/languages/` vit hors du thème — ni le dépôt ni le rsync de
 * déploiement ne le portent. Et Polylang ne télécharge les packs que depuis son écran Langues :
 * la création d'une langue par l'API modèle (tools/setup-polylang.php) n'en installe aucun.
 *
 * Sans le pack du cœur, `switch_to_locale( 'es_ES' )` est REFUSÉ en silence
 * (WP_Locale_Switcher n'accepte que les locales de get_available_languages()) : les formulaires
 * CF7 construits par code sortiraient alors en français. Sans le pack de Contact Form 7, leurs
 * messages de validation seraient figés en anglais dans la base.
 *
 * Options (variables d'environnement) :
 *   ADAPTOURS_LANG_LOCALES  fr_FR,es_ES   (défaut : locales des langues Polylang)
 *
 * Ce fichier n'est PAS chargé par functions.php : c'est un outil d'admin/CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return; // Accès direct hors WordPress.
}

require_once ABSPATH . 'wp-admin/includes/translation-install.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/update.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

$adaptours_locales_env = getenv( 'ADAPTOURS_LANG_LOCALES' );

if ( $adaptours_locales_env ) {
	$adaptours_locales = array_filter( array_map( 'trim', explode( ',', $adaptours_locales_env ) ) );
} elseif ( function_exists( 'PLL' ) && PLL() && isset( PLL()->model ) ) {
	$adaptours_locales = array();
	foreach ( PLL()->model->get_languages_list() as $adaptours_lang ) {
		$adaptours_locales[] = $adaptours_lang->locale;
	}
} else {
	$adaptours_locales = array( get_locale() );
}

// en_US est la langue source des extensions et du cœur : aucun pack à installer.
$adaptours_locales = array_values( array_diff( array_unique( $adaptours_locales ), array( 'en_US' ) ) );

if ( empty( $adaptours_locales ) ) {
	echo "Aucune locale à installer.\n";
	return;
}

echo 'Locales visées : ' . implode( ', ', $adaptours_locales ) . "\n";

if ( ! wp_can_install_language_pack() ) {
	echo "ERREUR : l'installation de packs de langue est impossible (droits d'écriture ou accès réseau).\n";
	return;
}

$adaptours_installed = get_available_languages();

foreach ( $adaptours_locales as $adaptours_locale ) {
	if ( in_array( $adaptours_locale, $adaptours_installed, true ) ) {
		echo "= cœur « {$adaptours_locale} » déjà installé\n";
		continue;
	}

	$adaptours_result = wp_download_language_pack( $adaptours_locale );

	if ( $adaptours_result ) {
		echo "+ cœur « {$adaptours_locale} » installé\n";
	} else {
		echo "ERREUR : pack du cœur « {$adaptours_locale} » indisponible ou non téléchargé\n";
	}
}

// Les traductions d'extensions ne sont proposées que pour les locales déjà disponibles :
// on rafraîchit les transients d'update APRÈS l'installation des packs du cœur.
wp_clean_plugins_cache();
wp_clean_themes_cache();
wp_update_plugins();
wp_update_themes();

$adaptours_updates = wp_get_translation_updates();

if ( empty( $adaptours_updates ) ) {
	echo "= aucune traduction d'extension à installer\n";
} else {
	$adaptours_upgrader = new Language_Pack_Upgrader( new Automatic_Upgrader_Skin() );
	$adaptours_upgrader->bulk_upgrade();

	$adaptours_by_locale = array();
	foreach ( $adaptours_updates as $adaptours_update ) {
		$adaptours_by_locale[ $adaptours_update->language ][] = $adaptours_update->slug;
	}
	foreach ( $adaptours_by_locale as $adaptours_locale => $adaptours_slugs ) {
		echo "+ {$adaptours_locale} : " . implode( ', ', array_unique( $adaptours_slugs ) ) . "\n";
	}
}

echo 'Langues du cœur disponibles : ' . implode( ', ', get_available_languages() ) . "\n";

$adaptours_plugin_mo = glob( WP_LANG_DIR . '/plugins/*.mo' );
echo 'Fichiers .mo d’extensions : ' . count( (array) $adaptours_plugin_mo ) . "\n";
echo "OK\n";
