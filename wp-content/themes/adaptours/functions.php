<?php
/**
 * Bootstrap du thème Adaptours : supports, i18n et chargement des modules.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADAPTOURS_VERSION', '0.1.0' );
define( 'ADAPTOURS_DIR', get_template_directory() );
define( 'ADAPTOURS_URI', get_template_directory_uri() );

/**
 * Supports du thème + internationalisation.
 */
function adaptours_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	load_theme_textdomain( 'adaptours', ADAPTOURS_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'adaptours_setup' );

// Couche données : CPT, taxonomies, rôles, helpers.
require ADAPTOURS_DIR . '/inc/helpers.php';
require ADAPTOURS_DIR . '/inc/roles.php';
require ADAPTOURS_DIR . '/inc/cpt.php';
require ADAPTOURS_DIR . '/inc/taxonomies.php';
require ADAPTOURS_DIR . '/inc/archive-destination.php';
require ADAPTOURS_DIR . '/inc/gallery-metabox.php';
require ADAPTOURS_DIR . '/inc/polylang.php';
require ADAPTOURS_DIR . '/inc/icons.php';
require ADAPTOURS_DIR . '/inc/itinerary-tags.php';

// Champs ACF, page de réglages et menus.
require ADAPTOURS_DIR . '/inc/acf-config.php';
require ADAPTOURS_DIR . '/inc/options.php';
require ADAPTOURS_DIR . '/inc/menus.php';

// Blocs et présentation.
require ADAPTOURS_DIR . '/inc/blocks.php';
require ADAPTOURS_DIR . '/inc/template-hooks.php';

// Formulaires (Contact Form 7).
require ADAPTOURS_DIR . '/inc/cf7.php';
require ADAPTOURS_DIR . '/inc/devis.php';

require ADAPTOURS_DIR . '/inc/enqueue.php';

/**
 * À l'activation : enregistre CPT et taxonomies puis purge les règles de réécriture.
 *
 * flush_rewrite_rules() est coûteux et ne tourne donc qu'ici. Les fonctions
 * d'enregistrement sont appelées à la main car le hook `init` n'a pas encore eu lieu.
 */
function adaptours_activate_theme() {
	adaptours_register_post_types();
	adaptours_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'adaptours_activate_theme' );
