<?php
/**
 * Liste fermée d'icônes partagée par les blocs section-accessibility et section-practical.
 *
 * Source unique (pas d'upload libre) ; chaque slug correspond à assets/icons/{slug}.svg.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retourne la liste fermée des icônes : slug => libellé.
 *
 * @return array<string,string>
 */
function adaptours_get_icons() {
	return array(
		'visa'           => __( 'Visa / formalités', 'adaptours' ),
		'sante'          => __( 'Santé', 'adaptours' ),
		'decalage'       => __( 'Décalage horaire', 'adaptours' ),
		'vol'            => __( 'Vol', 'adaptours' ),
		'budget'         => __( 'Budget', 'adaptours' ),
		'langue'         => __( 'Langue', 'adaptours' ),
		'monnaie'        => __( 'Monnaie', 'adaptours' ),
		'transport'      => __( 'Transport', 'adaptours' ),
		'accessibilite'  => __( 'Accessibilité', 'adaptours' ),
		'rythme'         => __( 'Rythme adapté', 'adaptours' ),
		'accompagnement' => __( 'Accompagnement', 'adaptours' ),
		'confort'        => __( 'Confort', 'adaptours' ),
	);
}

/**
 * Indique si un slug d'icône appartient à la liste fermée.
 *
 * @param string $slug Slug à valider.
 * @return bool
 */
function adaptours_is_valid_icon( $slug ) {
	return array_key_exists( $slug, adaptours_get_icons() );
}

/**
 * Chemin absolu du fichier SVG d'une icône, ou chaîne vide si le slug est inconnu.
 *
 * @param string $slug Slug d'icône.
 * @return string
 */
function adaptours_icon_path( $slug ) {
	if ( ! adaptours_is_valid_icon( $slug ) ) {
		return '';
	}
	return ADAPTOURS_DIR . '/assets/icons/' . $slug . '.svg';
}
