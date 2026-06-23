<?php
/**
 * Taxonomie zone_geographique : sert aux filtres de l'archive destinations.
 *
 * Définie en code (source de vérité unique), non hiérarchique, attachée au CPT destination.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les taxonomies du thème.
 */
function adaptours_register_taxonomies() {

	$zone_labels = array(
		'name'              => _x( 'Zones géographiques', 'taxonomy general name', 'adaptours' ),
		'singular_name'     => _x( 'Zone géographique', 'taxonomy singular name', 'adaptours' ),
		'menu_name'         => __( 'Zones géographiques', 'adaptours' ),
		'all_items'         => __( 'Toutes les zones', 'adaptours' ),
		'edit_item'         => __( 'Modifier la zone', 'adaptours' ),
		'view_item'         => __( 'Voir la zone', 'adaptours' ),
		'update_item'       => __( 'Mettre à jour la zone', 'adaptours' ),
		'add_new_item'      => __( 'Ajouter une zone', 'adaptours' ),
		'new_item_name'     => __( 'Nom de la nouvelle zone', 'adaptours' ),
		'search_items'      => __( 'Rechercher une zone', 'adaptours' ),
		'not_found'         => __( 'Aucune zone trouvée.', 'adaptours' ),
	);

	register_taxonomy(
		'zone_geographique',
		'destination',
		array(
			'labels'            => $zone_labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'zone',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'adaptours_register_taxonomies' );
