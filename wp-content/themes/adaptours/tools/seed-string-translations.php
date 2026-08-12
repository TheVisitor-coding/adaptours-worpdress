<?php
/**
 * Pré-remplit les traductions Polylang des réglages traduisibles (Langues → Traductions des
 * chaînes), idempotent.
 *
 * À exécuter après tools/setup-polylang.php :
 *   wp eval-file wp-content/themes/adaptours/tools/seed-string-translations.php
 *
 * Les 7 réglages concernés (inc/options.php) sont des PHRASES saisies par la cliente, pas des
 * littéraux du code : elles échappent au pipeline .po/.mo et ne sont traduisibles que par la
 * fonctionnalité « chaînes » de Polylang. Sans ce seed, le front /en/ et /es/ les affiche en
 * français.
 *
 * La clé de traduction Polylang est la VALEUR française courante : si la cliente modifie un
 * réglage, sa traduction devient orpheline (fonctionnement nominal de Polylang). Ce seed est
 * donc un point de départ, pas une source de vérité — la cliente garde la main dans l'admin.
 *
 * Options (variables d'environnement) :
 *   ADAPTOURS_SEED_FORCE  1   écrase une traduction déjà saisie (défaut : remplir-si-vide)
 *
 * Ce fichier n'est PAS chargé par functions.php : c'est un outil d'admin/CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return; // Accès direct hors WordPress.
}

if ( ! function_exists( 'PLL' ) || ! PLL() || ! class_exists( 'PLL_MO' ) ) {
	echo "ERREUR : Polylang n'est pas actif.\n";
	return;
}

if ( ! function_exists( 'adaptours_translatable_option_keys' ) ) {
	echo "ERREUR : le thème Adaptours n'est pas actif.\n";
	return;
}

$adaptours_force = '1' === (string) getenv( 'ADAPTOURS_SEED_FORCE' );

/**
 * Traductions par langue, indexées par CLÉ de réglage (stable), pas par valeur source.
 * Une clé absente pour une langue est simplement ignorée.
 */
$adaptours_string_translations = array(
	'en' => array(
		'tel_horaires'      => 'Monday – Friday · 9am → 6pm',
		'email_delai'       => 'Reply within 24 business hours',
		'dest_eyebrow'      => 'CATALOG',
		'dest_title_part_1' => 'Destinations',
		'dest_title_part_2' => 'that welcome you.',
		'dest_intro'        => 'Every destination is scouted, tested and approved by our team. Each page details its accessibility conditions.',
		'dest_badge_label'  => '{n} trips ready to go',
	),
	'es' => array(
		'tel_horaires'      => 'Lunes – Viernes · 9 → 18 h',
		'email_delai'       => 'Respuesta en 24 h laborables',
		'dest_eyebrow'      => 'CATÁLOGO',
		'dest_title_part_1' => 'Destinos',
		'dest_title_part_2' => 'accesibles.',
		'dest_intro'        => 'Todos nuestros destinos han sido explorados, probados y validados por nuestro equipo. Cada ficha detalla las condiciones de accesibilidad.',
		'dest_badge_label'  => '{n} viajes listos para salir',
	),
);

$adaptours_default = pll_default_language();
$adaptours_keys    = adaptours_translatable_option_keys();

foreach ( PLL()->model->get_languages_list() as $adaptours_language ) {
	$adaptours_slug = $adaptours_language->slug;

	if ( $adaptours_slug === $adaptours_default || ! isset( $adaptours_string_translations[ $adaptours_slug ] ) ) {
		continue;
	}

	$adaptours_mo = new PLL_MO();
	$adaptours_mo->import_from_db( $adaptours_language );
	$adaptours_changed = 0;

	foreach ( $adaptours_keys as $adaptours_key ) {
		$adaptours_source = (string) adaptours_get_option( $adaptours_key );

		if ( '' === $adaptours_source ) {
			echo "  = {$adaptours_slug}/{$adaptours_key} : réglage vide côté français, ignoré\n";
			continue;
		}
		if ( ! isset( $adaptours_string_translations[ $adaptours_slug ][ $adaptours_key ] ) ) {
			continue;
		}

		$adaptours_target = $adaptours_string_translations[ $adaptours_slug ][ $adaptours_key ];

		if ( ! $adaptours_force && '' !== (string) $adaptours_mo->translate_if_any( $adaptours_source ) ) {
			echo "  = {$adaptours_slug}/{$adaptours_key} : traduction déjà saisie, conservée\n";
			continue;
		}

		$adaptours_mo->add_entry( $adaptours_mo->make_entry( $adaptours_source, $adaptours_target ) );
		++$adaptours_changed;
		echo "  + {$adaptours_slug}/{$adaptours_key}\n";
	}

	if ( $adaptours_changed ) {
		$adaptours_mo->export_to_db( $adaptours_language );
	}
	echo "{$adaptours_slug} : {$adaptours_changed} traduction(s) écrite(s)\n";
}

echo "OK\n";
