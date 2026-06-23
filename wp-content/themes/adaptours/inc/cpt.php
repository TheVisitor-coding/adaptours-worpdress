<?php
/**
 * Custom Post Types : destination et avis.
 *
 * Déclarés en code (source de vérité unique), sans extension CPT UI.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les CPT du thème.
 */
function adaptours_register_post_types() {

	// CPT destination.
	$destination_labels = array(
		'name'                  => _x( 'Destinations', 'post type general name', 'adaptours' ),
		'singular_name'         => _x( 'Destination', 'post type singular name', 'adaptours' ),
		'menu_name'             => _x( 'Destinations', 'admin menu', 'adaptours' ),
		'name_admin_bar'        => _x( 'Destination', 'add new on admin bar', 'adaptours' ),
		'add_new'               => __( 'Ajouter', 'adaptours' ),
		'add_new_item'          => __( 'Ajouter une destination', 'adaptours' ),
		'new_item'              => __( 'Nouvelle destination', 'adaptours' ),
		'edit_item'             => __( 'Modifier la destination', 'adaptours' ),
		'view_item'             => __( 'Voir la destination', 'adaptours' ),
		'all_items'             => __( 'Toutes les destinations', 'adaptours' ),
		'search_items'          => __( 'Rechercher une destination', 'adaptours' ),
		'not_found'             => __( 'Aucune destination trouvée.', 'adaptours' ),
		'not_found_in_trash'    => __( 'Aucune destination dans la corbeille.', 'adaptours' ),
		'featured_image'        => __( 'Image principale', 'adaptours' ),
		'set_featured_image'    => __( 'Définir l\'image principale', 'adaptours' ),
		'remove_featured_image' => __( 'Retirer l\'image principale', 'adaptours' ),
		'use_featured_image'    => __( 'Utiliser comme image principale', 'adaptours' ),
		'items_list'            => __( 'Liste des destinations', 'adaptours' ),
		'archives'              => __( 'Archives des destinations', 'adaptours' ),
	);

	register_post_type(
		'destination',
		array(
			'labels'       => $destination_labels,
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => 'destinations',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'show_in_rest' => true,
			'menu_position' => 5,
			'menu_icon'    => 'dashicons-palmtree',
		)
	);

	// CPT avis.
	$avis_labels = array(
		'name'               => _x( 'Avis', 'post type general name', 'adaptours' ),
		'singular_name'      => _x( 'Avis', 'post type singular name', 'adaptours' ),
		'menu_name'          => _x( 'Avis', 'admin menu', 'adaptours' ),
		'name_admin_bar'     => _x( 'Avis', 'add new on admin bar', 'adaptours' ),
		'add_new'            => __( 'Ajouter', 'adaptours' ),
		'add_new_item'       => __( 'Ajouter un avis', 'adaptours' ),
		'new_item'           => __( 'Nouvel avis', 'adaptours' ),
		'edit_item'          => __( 'Modifier l\'avis', 'adaptours' ),
		'view_item'          => __( 'Voir l\'avis', 'adaptours' ),
		'all_items'          => __( 'Tous les avis', 'adaptours' ),
		'search_items'       => __( 'Rechercher un avis', 'adaptours' ),
		'not_found'          => __( 'Aucun avis trouvé.', 'adaptours' ),
		'not_found_in_trash' => __( 'Aucun avis dans la corbeille.', 'adaptours' ),
		'items_list'         => __( 'Liste des avis', 'adaptours' ),
	);

	register_post_type(
		'avis',
		array(
			'labels'             => $avis_labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'has_archive'        => false,
			'rewrite'            => false,
			'supports'           => array( 'title', 'revisions' ),
			'show_in_rest'       => true,
			'menu_position'      => 6,
			'menu_icon'          => 'dashicons-format-quote',
		)
	);
}
add_action( 'init', 'adaptours_register_post_types' );
