<?php
/**
 * Insère les images de la page d'accueil (récupérées du site atelier) dans les blocs de la home.
 *
 * À exécuter via WP-CLI, chemin ABSOLU obligatoire (wp eval-file résout relativement au CWD) :
 *   wp eval-file "$HOME/preprod/wp-content/themes/adaptours/tools/import-home-images.php"
 *
 * Données relues dans tools/data/home-images.php (bloc => attribut d'image => URL source).
 *
 * Idempotent : chaque image est estampillée _adaptours_import_source_url ; un 2ᵉ run réutilise
 * l'attachement existant (pas de re-téléchargement) et laisse le post_content inchangé. La home
 * est réécrite chirurgicalement (parse_blocks/serialize_blocks) : seuls les attributs d'image des
 * 4 blocs concernés sont posés, le reste du contenu (autres blocs, éditions cliente) est préservé.
 *
 * Option (variable d'environnement) :
 *   ADAPTOURS_HOME_IMAGES_FORCE  1  ré-impose l'image même si un attribut est déjà renseigné
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$adaptours_data_file = __DIR__ . '/data/home-images.php';
if ( ! file_exists( $adaptours_data_file ) ) {
	echo "ERREUR : fichier de données introuvable ({$adaptours_data_file})\n";
	return;
}

$adaptours_home_images = require $adaptours_data_file;
if ( ! is_array( $adaptours_home_images ) || ! $adaptours_home_images ) {
	echo "ERREUR : données d'images vides ou invalides\n";
	return;
}

if ( ! defined( 'ADAPTOURS_IMPORT_SOURCE_META' ) ) {
	define( 'ADAPTOURS_IMPORT_SOURCE_META', '_adaptours_import_source_url' );
}

$adaptours_force = (bool) getenv( 'ADAPTOURS_HOME_IMAGES_FORCE' );

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$adaptours_home_id = (int) get_option( 'page_on_front' );
if ( ! $adaptours_home_id || 'page' !== get_option( 'show_on_front' ) ) {
	echo "ERREUR : aucune page d'accueil statique définie (page_on_front)\n";
	return;
}

$adaptours_home = get_post( $adaptours_home_id );
if ( ! $adaptours_home ) {
	echo "ERREUR : page d'accueil #{$adaptours_home_id} introuvable\n";
	return;
}

echo "Import images home → « {$adaptours_home->post_title} » (#{$adaptours_home_id})"
	. ( $adaptours_force ? ', FORCE' : '' ) . "\n";

$adaptours_find_existing = function ( $url ) {
	$found = get_posts( array(
		'post_type'        => 'attachment',
		'post_status'      => 'inherit',
		'posts_per_page'   => 1,
		'fields'           => 'ids',
		'meta_key'         => ADAPTOURS_IMPORT_SOURCE_META,
		'meta_value'       => $url,
		'no_found_rows'    => true,
		'suppress_filters' => false,
	) );
	return $found ? (int) $found[0] : 0;
};

$adaptours_sideload = function ( $url, $home_id, $desc ) use ( $adaptours_find_existing ) {
	$existing = $adaptours_find_existing( $url );
	if ( $existing ) {
		return array( $existing, 'reused' );
	}
	$att_id = media_sideload_image( $url, $home_id, $desc, 'id' );
	if ( is_wp_error( $att_id ) ) {
		return array( $att_id, 'error' );
	}
	$att_id = (int) $att_id;
	update_post_meta( $att_id, ADAPTOURS_IMPORT_SOURCE_META, $url );
	if ( $desc ) {
		update_post_meta( $att_id, '_wp_attachment_image_alt', $desc );
	}
	return array( $att_id, 'created' );
};

$adaptours_labels = array(
	'adaptours/hero-home'            => 'Adaptours — voyage accessible',
	'adaptours/section-promise'      => 'Adaptours',
	'adaptours/content-storytelling' => 'Adaptours — équipements adaptés',
	'adaptours/team-intro'           => "Adaptours — l'équipe",
);

$adaptours_resolved = array();
$adaptours_created  = 0;
$adaptours_reused   = 0;
$adaptours_errors   = 0;

foreach ( $adaptours_home_images as $block_name => $attrs ) {
	foreach ( $attrs as $attr => $url ) {
		list( $id, $status ) = $adaptours_sideload( $url, $adaptours_home_id, $adaptours_labels[ $block_name ] ?? null );
		if ( 'error' === $status ) {
			$adaptours_errors++;
			echo "  WARN  {$block_name}.{$attr} : " . $id->get_error_message() . "\n";
			continue;
		}
		$adaptours_resolved[ $block_name ][ $attr ] = $id;
		if ( 'created' === $status ) {
			$adaptours_created++;
		} else {
			$adaptours_reused++;
		}
		echo "  {$block_name}.{$attr} → #{$id} ({$status})\n";
	}
}

$adaptours_blocks  = parse_blocks( $adaptours_home->post_content );
$adaptours_applied = 0;

foreach ( $adaptours_blocks as &$adaptours_block ) {
	$name = $adaptours_block['blockName'] ?? null;
	if ( ! $name || empty( $adaptours_resolved[ $name ] ) ) {
		continue;
	}
	foreach ( $adaptours_resolved[ $name ] as $attr => $id ) {
		if ( $adaptours_force || empty( $adaptours_block['attrs'][ $attr ] ) ) {
			$adaptours_block['attrs'][ $attr ] = (int) $id;
			$adaptours_applied++;
		}
	}
}
unset( $adaptours_block );

$adaptours_new_content = serialize_blocks( $adaptours_blocks );

if ( $adaptours_new_content !== $adaptours_home->post_content ) {
	$res = wp_update_post( wp_slash( array(
		'ID'           => $adaptours_home_id,
		'post_content' => $adaptours_new_content,
	) ), true );
	if ( is_wp_error( $res ) ) {
		echo "ERREUR : mise à jour du contenu de la home — " . $res->get_error_message() . "\n";
		return;
	}
	echo "Contenu de la home mis à jour ({$adaptours_applied} attribut(s) d'image posé(s)).\n";
} else {
	echo "Contenu de la home inchangé (déjà à jour).\n";
}

echo "Terminé : {$adaptours_created} image(s) créée(s), {$adaptours_reused} réutilisée(s), {$adaptours_errors} erreur(s).\n";
