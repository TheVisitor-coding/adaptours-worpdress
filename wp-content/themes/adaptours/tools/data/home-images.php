<?php
/**
 * Images de la page d'accueil, récupérées du site atelier (adaptours.matteo.rossi.mds-nantes.fr).
 *
 * Relu par tools/import-home-images.php. URLs en pleine résolution (suffixe -WxH retiré).
 * Mapping : nom de bloc => ( attribut d'image du block.json => URL source ).
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$base = 'https://adaptours.matteo.rossi.mds-nantes.fr/wp-content/uploads/2026/07/';

return array(
	'adaptours/hero-home' => array(
		'polaroid_1' => $base . 'blue-lagoon-b.webp',
		'polaroid_2' => $base . 'IMG-20250610-WA0007.webp',
		'polaroid_3' => $base . '20250205_111931.webp',
		'polaroid_4' => $base . '53.webp',
	),
	'adaptours/section-promise' => array(
		'image_main'  => $base . '1758180676762.webp',
		'image_inset' => $base . 'vue-mer-scaled-1.webp',
	),
	'adaptours/content-storytelling' => array(
		'photo_1' => $base . 'P1000473.webp',
		'photo_2' => $base . 'vehicule-adapte-montreal-1362425045.webp',
		'photo_3' => $base . 'Chypre-accessible-1.webp',
	),
	'adaptours/team-intro' => array(
		'main_image' => $base . 'IMG-20220803-WA0001.webp',
	),
);
