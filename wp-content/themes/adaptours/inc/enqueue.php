<?php
/**
 * Chargement des assets front.
 *
 * - Styles globaux : assets/build/main.css (compilé depuis assets/src/scss/main.scss).
 * - Styles des blocs : chargés à la demande via la clé "style" de chaque block.json
 *   (should_load_separate_core_block_assets), donc PAS enqueués ici.
 * - Polices : déclarées en @font-face via theme.json et self-hosted (assets/fonts/), donc
 *   aucun enqueue manuel ni appel à Google Fonts (RGPD).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Active le chargement séparé des styles de blocs (un fichier CSS par bloc présent).
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Enqueue des styles/scripts front globaux.
 */
function adaptours_enqueue_assets() {
	$css = '/assets/build/main.css';
	$css_path = ADAPTOURS_DIR . $css;

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'adaptours-main',
			ADAPTOURS_URI . $css,
			array(),
			(string) filemtime( $css_path ) // Cache-busting basé sur le build.
		);
	}

	// Interactions du header (burger + variante transparente), présentes partout.
	// Vanilla hors build, chargé en pied de page.
	$js      = '/assets/js/header.js';
	$js_path = ADAPTOURS_DIR . $js;

	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'adaptours-header',
			ADAPTOURS_URI . $js,
			array(),
			(string) filemtime( $js_path ),
			true // in_footer
		);
	}
}
add_action( 'wp_enqueue_scripts', 'adaptours_enqueue_assets' );

/**
 * Charge la lightbox de la galerie, uniquement sur le single destination.
 *
 * GLightbox est vendorisé (assets/vendor/glightbox/) ; le script d'init n'agit que si
 * des liens `.adaptours-glightbox` existent.
 */
function adaptours_enqueue_gallery_lightbox() {
	if ( ! is_singular( 'destination' ) ) {
		return;
	}

	$css      = '/assets/vendor/glightbox/glightbox.min.css';
	$js       = '/assets/vendor/glightbox/glightbox.min.js';
	$init     = '/assets/js/gallery-lightbox.js';
	$css_path = ADAPTOURS_DIR . $css;
	$js_path  = ADAPTOURS_DIR . $js;
	$init_path = ADAPTOURS_DIR . $init;

	if ( ! file_exists( $css_path ) || ! file_exists( $js_path ) || ! file_exists( $init_path ) ) {
		return;
	}

	wp_enqueue_style( 'glightbox', ADAPTOURS_URI . $css, array(), (string) filemtime( $css_path ) );
	wp_enqueue_script( 'glightbox', ADAPTOURS_URI . $js, array(), (string) filemtime( $js_path ), true );
	wp_enqueue_script( 'adaptours-gallery-lightbox', ADAPTOURS_URI . $init, array( 'glightbox' ), (string) filemtime( $init_path ), true );
}
add_action( 'wp_enqueue_scripts', 'adaptours_enqueue_gallery_lightbox' );

/**
 * Charge le script des filtres de l'archive Destinations, uniquement sur cette archive.
 *
 * Amélioration progressive : soumission auto des menus au change, repli sur le bouton
 * « Filtrer » sans JS.
 */
function adaptours_enqueue_archive_destination() {
	if ( ! is_post_type_archive( 'destination' ) ) {
		return;
	}

	$js      = '/assets/js/archive-destination.js';
	$js_path = ADAPTOURS_DIR . $js;

	if ( ! file_exists( $js_path ) ) {
		return;
	}

	wp_enqueue_script(
		'adaptours-archive-destination',
		ADAPTOURS_URI . $js,
		array(),
		(string) filemtime( $js_path ),
		true // in_footer
	);
}
add_action( 'wp_enqueue_scripts', 'adaptours_enqueue_archive_destination' );

/**
 * Charge le script du formulaire Devis, uniquement sur le template Devis.
 *
 * Amélioration progressive : steppers −/+, repli sur les champs nombre natifs sans JS.
 */
function adaptours_enqueue_devis_form() {
	if ( ! is_page_template( 'template-devis.php' ) ) {
		return;
	}

	$js      = '/assets/js/devis-form.js';
	$js_path = ADAPTOURS_DIR . $js;

	if ( ! file_exists( $js_path ) ) {
		return;
	}

	wp_enqueue_script(
		'adaptours-devis-form',
		ADAPTOURS_URI . $js,
		array(),
		(string) filemtime( $js_path ),
		true // in_footer
	);
}
add_action( 'wp_enqueue_scripts', 'adaptours_enqueue_devis_form' );

/**
 * Charge les styles globaux (main.css) côté éditeur de blocs.
 *
 * Les blocs à rendu serveur (ServerSideRender) réutilisent des composants partagés stylés
 * dans main.css ; sans ce chargement, leur aperçu éditeur serait dé-stylé.
 */
function adaptours_enqueue_block_editor_assets() {
	$css      = '/assets/build/main.css';
	$css_path = ADAPTOURS_DIR . $css;

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'adaptours-main-editor',
			ADAPTOURS_URI . $css,
			array(),
			(string) filemtime( $css_path )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'adaptours_enqueue_block_editor_assets' );
