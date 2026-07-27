<?php
/**
 * Import des destinations depuis l'ancien site adaptours.fr → CPT destination.
 *
 * À exécuter via WP-CLI, chemin ABSOLU obligatoire (wp eval-file résout relativement au CWD) :
 *   wp eval-file "$HOME/preprod/wp-content/themes/adaptours/tools/import-destinations.php"
 *
 * Données relues dans tools/data/destinations.php. Le contenu détaillé de chaque fiche (carte,
 * accessibilité, galerie, itinéraire, avis) reste à compléter à la main.
 *
 * Idempotent : rejouable sans doublon (clé de provenance _adaptours_import_source_url puis repli
 * slug). Ne réécrit jamais une saisie cliente : remplir-si-vide sur les champs, post_content et
 * post_status jamais modifiés en mise à jour.
 *
 * Options (variables d'environnement) :
 *   ADAPTOURS_IMPORT_STATUS          publish|draft  statut à la création (défaut : publish)
 *   ADAPTOURS_IMPORT_FORCE           1              ré-impose les champs de base même non vides
 *   ADAPTOURS_IMPORT_IMAGES          1              télécharge l'image hero (media_sideload_image)
 *   ADAPTOURS_IMPORT_SKIP_UNSTAMPED  1              n'adopte pas un post existant non estampillé
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$adaptours_data_file = __DIR__ . '/data/destinations.php';
if ( ! file_exists( $adaptours_data_file ) ) {
	echo "ERREUR : fichier de données introuvable ({$adaptours_data_file})\n";
	return;
}

$adaptours_destinations = require $adaptours_data_file;
if ( ! is_array( $adaptours_destinations ) || ! $adaptours_destinations ) {
	echo "ERREUR : données de destinations vides ou invalides\n";
	return;
}

if ( ! defined( 'ADAPTOURS_IMPORT_SOURCE_META' ) ) {
	define( 'ADAPTOURS_IMPORT_SOURCE_META', '_adaptours_import_source_url' );
}

$adaptours_import_status = getenv( 'ADAPTOURS_IMPORT_STATUS' ) ?: 'publish';
if ( ! in_array( $adaptours_import_status, array( 'publish', 'draft' ), true ) ) {
	$adaptours_import_status = 'publish';
}
$adaptours_import_force  = (bool) getenv( 'ADAPTOURS_IMPORT_FORCE' );
$adaptours_import_images = (bool) getenv( 'ADAPTOURS_IMPORT_IMAGES' );
$adaptours_skip_unstamped = (bool) getenv( 'ADAPTOURS_IMPORT_SKIP_UNSTAMPED' );

// Clés ACF (écriture via update_field par CLÉ → ACF pose la référence _name => field_key).
$adaptours_acf_keys = array(
	'ville'            => 'field_adaptours_ville',
	'duree'            => 'field_adaptours_duree',
	'prix_a_partir_de' => 'field_adaptours_prix_a_partir_de',
	'temps_vol'        => 'field_adaptours_temps_vol',
	'hero_accroche'    => 'field_adaptours_hero_accroche',
	'coup_de_coeur'    => 'field_adaptours_coup_de_coeur',
);

$adaptours_default_lang = function_exists( 'pll_default_language' ) ? pll_default_language() : 'fr';

if ( $adaptours_import_images ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

echo "Import destinations — statut création : {$adaptours_import_status}"
	. ( $adaptours_import_force ? ', FORCE' : '' )
	. ( $adaptours_import_images ? ', IMAGES' : '' )
	. "\n\n";

/*
 * 1. Zones géographiques : garantir les 6 termes, poser la langue FR (sinon Polylang filtre le
 *    terme hors du <select> et du tax_query de l'archive), construire la carte slug => term_id.
 */
$adaptours_zone_names = array(
	'europe'       => 'Europe',
	'afrique'      => 'Afrique',
	'ameriques'    => 'Amériques',
	'asie'         => 'Asie',
	'oceanie'      => 'Océanie',
	'moyen-orient' => 'Moyen-Orient',
);

$adaptours_zone_ids = array();
foreach ( $adaptours_zone_names as $slug => $name ) {
	$term = term_exists( $name, 'zone_geographique' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'zone_geographique' );
		if ( is_wp_error( $term ) ) {
			echo "ERREUR zone « {$name} » : " . $term->get_error_message() . "\n";
			continue;
		}
		echo "+ zone « {$name} »\n";
	}
	$term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );

	if ( function_exists( 'pll_set_term_language' ) && function_exists( 'pll_get_term_language' ) ) {
		if ( ! pll_get_term_language( $term_id ) ) {
			pll_set_term_language( $term_id, $adaptours_default_lang );
		}
	}

	$adaptours_zone_ids[ $slug ] = $term_id;
	$obj = get_term( $term_id, 'zone_geographique' );
	if ( $obj && ! is_wp_error( $obj ) ) {
		$adaptours_zone_ids[ $obj->slug ] = $term_id;
	}
}

/*
 * 2. Helpers.
 */

/** Retrouve un post destination existant : provenance d'abord, repli slug. */
$adaptours_find_existing = function ( $slug, $source_url ) {
	$by_meta = get_posts(
		array(
			'post_type'        => 'destination',
			'post_status'      => 'any',
			'meta_key'         => ADAPTOURS_IMPORT_SOURCE_META,
			'meta_value'       => $source_url,
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'suppress_filters' => true,
			'no_found_rows'    => true,
		)
	);
	if ( $by_meta ) {
		return array( (int) $by_meta[0], 'stamped' );
	}

	$by_slug = get_page_by_path( $slug, OBJECT, 'destination' );
	if ( $by_slug ) {
		return array( (int) $by_slug->ID, 'slug' );
	}

	return array( 0, 'new' );
};

/** Écrit un champ ACF en remplir-si-vide (rien à importer si la valeur source est vide). */
$adaptours_set_field = function ( $post_id, $name, $key, $value, $force ) {
	if ( '' === $value || null === $value ) {
		return;
	}
	$current  = function_exists( 'get_field' ) ? get_field( $key, $post_id ) : get_post_meta( $post_id, $name, true );
	$is_empty = ( null === $current || '' === $current || false === $current );
	if ( ! $is_empty && ! $force ) {
		return;
	}
	if ( function_exists( 'update_field' ) ) {
		update_field( $key, $value, $post_id );
	} else {
		update_post_meta( $post_id, $name, $value );
	}
};

/*
 * 3. Boucle d'import.
 */
$adaptours_created = 0;
$adaptours_updated = 0;
$adaptours_skipped = 0;

foreach ( $adaptours_destinations as $record ) {
	try {
		$slug       = sanitize_title( $record['slug'] );
		$source_url = isset( $record['source_url'] ) ? (string) $record['source_url'] : '';
		$region     = isset( $record['region_slug'] ) ? $record['region_slug'] : '';

		list( $post_id, $match ) = $adaptours_find_existing( $slug, $source_url );

		if ( $post_id && 'slug' === $match ) {
			$stamped = get_post_meta( $post_id, ADAPTOURS_IMPORT_SOURCE_META, true );
			if ( ! $stamped ) {
				if ( $adaptours_skip_unstamped ) {
					echo "SKIP  {$slug} : post #{$post_id} existant non estampillé\n";
					++$adaptours_skipped;
					continue;
				}
				echo "WARN  {$slug} : adoption du post existant #{$post_id} (non estampillé)\n";
			}
		}

		if ( $post_id ) {
			$existing   = get_post( $post_id );
			$update_arr = array( 'ID' => $post_id );
			if ( $adaptours_import_force || '' === trim( (string) $existing->post_excerpt ) ) {
				$update_arr['post_excerpt'] = $record['excerpt'];
			}
			if ( $adaptours_import_force ) {
				$update_arr['post_title'] = $record['title'];
			}
			if ( count( $update_arr ) > 1 ) {
				wp_update_post( wp_slash( $update_arr ), true );
			}
			++$adaptours_updated;
			$verb = 'MÀJ  ';
		} else {
			$post_id = wp_insert_post(
				wp_slash(
					array(
						'post_type'    => 'destination',
						'post_status'  => $adaptours_import_status,
						'post_title'   => $record['title'],
						'post_name'    => $slug,
						'post_excerpt' => $record['excerpt'],
						'post_content' => '',
					)
				),
				true
			);
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				echo "ERREUR {$slug} : " . ( is_wp_error( $post_id ) ? $post_id->get_error_message() : '?' ) . "\n";
				continue;
			}
			$post_id = (int) $post_id;
			++$adaptours_created;
			$verb = 'CRÉÉ ';
		}

		update_post_meta( $post_id, ADAPTOURS_IMPORT_SOURCE_META, $source_url );

		$adaptours_set_field( $post_id, 'ville', $adaptours_acf_keys['ville'], $record['ville'], $adaptours_import_force );
		$adaptours_set_field( $post_id, 'duree', $adaptours_acf_keys['duree'], $record['duree'], $adaptours_import_force );
		$adaptours_set_field( $post_id, 'temps_vol', $adaptours_acf_keys['temps_vol'], $record['temps_vol'], $adaptours_import_force );
		$adaptours_set_field( $post_id, 'hero_accroche', $adaptours_acf_keys['hero_accroche'], $record['intro'], $adaptours_import_force );

		$prix = (int) preg_replace( '/[^0-9]/', '', (string) $record['prix'] );
		if ( $prix > 0 ) {
			$adaptours_set_field( $post_id, 'prix_a_partir_de', $adaptours_acf_keys['prix_a_partir_de'], $prix, $adaptours_import_force );
		}

		if ( ! empty( $record['coup_de_coeur'] ) ) {
			if ( function_exists( 'update_field' ) ) {
				update_field( $adaptours_acf_keys['coup_de_coeur'], 1, $post_id );
			} else {
				update_post_meta( $post_id, 'coup_de_coeur', 1 );
			}
		}

		if ( '' !== $region && isset( $adaptours_zone_ids[ $region ] ) ) {
			$has_terms = wp_get_object_terms( $post_id, 'zone_geographique', array( 'fields' => 'ids' ) );
			if ( $adaptours_import_force || empty( $has_terms ) || is_wp_error( $has_terms ) ) {
				wp_set_object_terms( $post_id, (int) $adaptours_zone_ids[ $region ], 'zone_geographique' );
			}
		} elseif ( '' !== $region ) {
			echo "WARN  {$slug} : région inconnue « {$region} »\n";
		}

		if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_get_post_language' ) ) {
			if ( ! pll_get_post_language( $post_id ) ) {
				pll_set_post_language( $post_id, $adaptours_default_lang );
			}
		}

		if ( $adaptours_import_images && ! empty( $record['hero_image_url'] ) && ! get_post_thumbnail_id( $post_id ) ) {
			$att_id = media_sideload_image( $record['hero_image_url'], $post_id, $record['title'], 'id' );
			if ( is_wp_error( $att_id ) ) {
				echo "WARN  {$slug} : image hero échec — " . $att_id->get_error_message() . "\n";
			} else {
				set_post_thumbnail( $post_id, (int) $att_id );
				update_post_meta( (int) $att_id, '_wp_attachment_image_alt', $record['title'] );
			}
		}

		echo sprintf( "%s %-22s #%-5d [%s] %s\n", $verb, $slug, $post_id, $region, get_permalink( $post_id ) );
	} catch ( \Throwable $e ) {
		echo 'ERREUR ' . ( isset( $record['slug'] ) ? $record['slug'] : '?' ) . ' : ' . $e->getMessage() . "\n";
	}
}

flush_rewrite_rules();

echo sprintf( "\nOK — %d créées, %d mises à jour, %d ignorées, %d au total.\n", $adaptours_created, $adaptours_updated, $adaptours_skipped, count( $adaptours_destinations ) );
