<?php
/**
 * Tags d'étape du bloc adaptours/itinerary.
 *
 * Liste fermée en code plutôt qu'une taxonomie, pour éviter les tags orphelins en admin.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retourne les tags d'étape d'itinéraire : slug => libellé.
 *
 * @return array<string,string>
 */
function adaptours_get_itinerary_tags() {
	return array(
		'vol'        => __( 'Vol', 'adaptours' ),
		'transport'  => __( 'Transport', 'adaptours' ),
		'nature'     => __( 'Nature', 'adaptours' ),
		'temps_fort' => __( 'Temps fort', 'adaptours' ),
		'culture'    => __( 'Culture', 'adaptours' ),
		'bien_etre'  => __( 'Bien-être', 'adaptours' ),
		'spirituel'  => __( 'Spirituel', 'adaptours' ),
		'eau'        => __( 'Eau', 'adaptours' ),
		'faune'      => __( 'Faune', 'adaptours' ),
		'marche'     => __( 'Marche', 'adaptours' ),
	);
}

/**
 * Indique si un slug de tag d'itinéraire est valide.
 *
 * @param string $slug Slug à valider.
 * @return bool
 */
function adaptours_is_valid_itinerary_tag( $slug ) {
	return array_key_exists( $slug, adaptours_get_itinerary_tags() );
}
