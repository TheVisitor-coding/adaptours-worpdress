<?php
/**
 * Seed de recette EN : crée les traductions anglaises d'un échantillon de contenu pour
 * valider le pipeline multilingue sans attendre la saisie de la cliente.
 *
 * À exécuter après tools/setup-polylang.php :
 *   wp eval-file wp-content/themes/adaptours/tools/seed-en.php
 *
 * Le contenu copié reste en français (la traduction éditoriale relève de la cliente) ;
 * l'objectif est seulement que chaque page EN existe, soit liée à son original et rende
 * avec ses blocs verrouillés. Idempotent : ne recrée pas une traduction déjà présente.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) {
	echo "ERREUR : Polylang n'est pas actif.\n";
	return;
}

/**
 * Crée (ou retrouve) la traduction EN d'un post, copie son contenu et lie les deux.
 *
 * @param int         $fr_id    ID du post source (FR).
 * @param string|null $slug     Slug EN souhaité (sinon dérivé du slug FR).
 * @param bool        $copy_meta Copier toutes les métas (utile pour destination/avis).
 * @return int ID EN (0 en cas d'échec).
 */
function adaptours_seed_translation( $fr_id, $slug = null, $copy_meta = false ) {
	$fr = get_post( $fr_id );
	if ( ! $fr ) {
		return 0;
	}

	$existing = (int) pll_get_post( $fr_id, 'en' );
	if ( $existing ) {
		return $existing;
	}

	$en_id = wp_insert_post(
		array(
			'post_type'    => $fr->post_type,
			'post_status'  => 'publish',
			'post_title'   => $fr->post_title,
			'post_content' => $fr->post_content,
			'post_excerpt' => $fr->post_excerpt,
			'post_name'    => $slug ? $slug : ( $fr->post_name . '-en' ),
			'menu_order'   => $fr->menu_order,
		),
		true
	);

	if ( is_wp_error( $en_id ) || ! $en_id ) {
		echo "ERREUR insertion traduction de #$fr_id : " . ( is_wp_error( $en_id ) ? $en_id->get_error_message() : '?' ) . "\n";
		return 0;
	}
	$en_id = (int) $en_id;

	$template = get_post_meta( $fr_id, '_wp_page_template', true );
	if ( $template ) {
		update_post_meta( $en_id, '_wp_page_template', $template );
	}

	if ( $copy_meta ) {
		foreach ( get_post_meta( $fr_id ) as $key => $values ) {
			if ( '_wp_page_template' === $key ) {
				continue;
			}
			delete_post_meta( $en_id, $key );
			foreach ( $values as $value ) {
				add_post_meta( $en_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	pll_set_post_language( $fr_id, 'fr' );
	pll_set_post_language( $en_id, 'en' );
	pll_save_post_translations( array( 'fr' => $fr_id, 'en' => $en_id ) );

	// Une fois la langue posée, Polylang autorise le même slug entre langues : on réimpose
	// le slug souhaité (l'insertion l'avait suffixé car la langue n'était pas encore connue).
	if ( $slug && get_post_field( 'post_name', $en_id ) !== $slug ) {
		wp_update_post( array( 'ID' => $en_id, 'post_name' => $slug ) );
	}

	return $en_id;
}

/** Première page utilisant un template donné. */
function adaptours_seed_page_by_template( $template ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
			'lang'           => 'fr',
			'fields'         => 'ids',
		)
	);
	return $pages ? (int) $pages[0] : 0;
}

$report = array();

// Page d'accueil.
$front = (int) get_option( 'page_on_front' );
if ( $front ) {
	$report['home (front-page)'] = adaptours_seed_translation( $front, 'home-en' );
}

// Pages à template (Contact, Devis, Qui sommes-nous).
$templates = array(
	'about'   => 'template-qui-sommes-nous.php',
	'contact' => 'template-contact.php',
	'quote'   => 'template-devis.php',
);
foreach ( $templates as $slug => $tpl ) {
	$fr_id = adaptours_seed_page_by_template( $tpl );
	if ( $fr_id ) {
		$report[ "page $slug" ] = adaptours_seed_translation( $fr_id, $slug );
	}
}

// Une destination + un avis (avec leurs métas).
foreach ( array( 'destination', 'avis' ) as $cpt ) {
	$posts = get_posts(
		array(
			'post_type'      => $cpt,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'lang'           => 'fr',
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);
	if ( $posts ) {
		$report[ $cpt ] = adaptours_seed_translation( (int) $posts[0], null, true );
	}
}

flush_rewrite_rules();

foreach ( $report as $label => $id ) {
	$url = $id ? get_permalink( $id ) : '—';
	echo sprintf( "%-22s EN #%-4d %s\n", $label, $id, $url );
}
echo "OK\n";
