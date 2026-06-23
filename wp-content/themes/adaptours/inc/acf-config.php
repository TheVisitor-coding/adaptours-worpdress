<?php
/**
 * Configuration ACF (Free uniquement) : Local JSON + champs des destinations.
 *
 * ACF Pro est exclu (budget 0 €) : aucune feature Pro ici (Repeater / Gallery /
 * Flexible Content / Clone / acf_add_options_page). La page d'options « Coordonnées
 * & liens » est gérée en natif (Settings API) dans inc/options.php, PAS par ACF.
 *
 * Local JSON : les groupes de champs sont synchronisés vers `acf-json/` du thème, donc
 * versionnés et reproductibles entre environnements.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Toute la suite ne s'exécute que si ACF est actif.
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

/**
 * Point d'enregistrement du Local JSON (sauvegarde des groupes édités via l'UI).
 *
 * @return string Chemin absolu du dossier acf-json/ du thème.
 */
function adaptours_acf_save_json() {
	return ADAPTOURS_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'adaptours_acf_save_json' );

/**
 * Point de chargement du Local JSON (lecture des groupes versionnés).
 *
 * @param array $paths Chemins de chargement par défaut.
 * @return array
 */
function adaptours_acf_load_json( $paths ) {
	// On remplace le chemin par défaut pour ne charger que le JSON du thème.
	$paths   = array();
	$paths[] = ADAPTOURS_DIR . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'adaptours_acf_load_json' );

/**
 * Groupe de champs du CPT `destination` : carte d'identité, hero, bande méta, carte,
 * accessibilité et suggestions.
 *
 * La synchronisation Polylang de ces métas est gérée dans inc/polylang.php.
 */
function adaptours_register_acf_fields() {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_adaptours_destination',
			'title'                 => __( 'Détails de la destination', 'adaptours' ),
			'fields'                => array(
				array(
					'key'   => 'field_adaptours_ville',
					'label' => __( 'Ville', 'adaptours' ),
					'name'  => 'ville',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_adaptours_duree',
					'label' => __( 'Durée', 'adaptours' ),
					'name'  => 'duree',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_adaptours_prix_a_partir_de',
					'label'         => __( 'Prix à partir de (€)', 'adaptours' ),
					'name'          => 'prix_a_partir_de',
					'type'          => 'number',
					'min'           => 0,
					'step'          => 1,
					'instructions'  => __( 'Montant numérique (sert au filtre budget de l’archive).', 'adaptours' ),
				),
				array(
					'key'     => 'field_adaptours_coup_de_coeur',
					'label'   => __( 'Coup de cœur', 'adaptours' ),
					'name'    => 'coup_de_coeur',
					'type'    => 'true_false',
					'ui'      => 1,
				),
				array(
					'key'           => 'field_adaptours_avis_mis_en_avant',
					'label'         => __( 'Avis mis en avant', 'adaptours' ),
					'name'          => 'avis_mis_en_avant',
					'type'          => 'post_object',
					'post_type'     => array( 'avis' ),
					'return_format' => 'id',
					'multiple'      => 0,
					'allow_null'    => 1,
					'ui'            => 1,
					'instructions'  => __( 'Avis client à mettre en avant sur cette destination. Laissez vide pour ne rien afficher.', 'adaptours' ),
				),

				// Hero de la fiche.
				array(
					'key'          => 'field_adaptours_coordonnees',
					'label'        => __( 'Coordonnées GPS', 'adaptours' ),
					'name'         => 'coordonnees',
					'type'         => 'text',
					'instructions' => __( 'Affichées sous le titre, à côté de la ville (ex. : 8°25′S 115°11′E). Facultatif.', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_accroche_manuscrite',
					'label'        => __( 'Petite phrase manuscrite', 'adaptours' ),
					'name'         => 'accroche_manuscrite',
					'type'         => 'text',
					'instructions' => __( 'Courte phrase écrite à la main affichée près du titre (ex. : l’île qui s’adapte). Facultatif.', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_hero_accroche',
					'label'        => __( 'Phrase d’introduction', 'adaptours' ),
					'name'         => 'hero_accroche',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => __( 'Quelques lignes de présentation affichées sous le titre, en haut de la page.', 'adaptours' ),
				),
				// Bande d'informations sous le titre.
				array(
					'key'          => 'field_adaptours_nuits_sur_place',
					'label'        => __( 'Nombre de nuits sur place', 'adaptours' ),
					'name'         => 'nuits_sur_place',
					'type'         => 'number',
					'min'          => 0,
					'step'         => 1,
					'instructions' => __( 'Affiché sous la durée (ex. : 9 nuits sur place).', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_periode_ideale',
					'label'        => __( 'Période idéale', 'adaptours' ),
					'name'         => 'periode_ideale',
					'type'         => 'text',
					'instructions' => __( 'Meilleure période pour partir (ex. : Mai → Sep).', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_saison_label',
					'label'        => __( 'Précision sur la saison', 'adaptours' ),
					'name'         => 'saison_label',
					'type'         => 'text',
					'instructions' => __( 'Petit complément affiché sous la période (ex. : saison sèche).', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_voyageurs_min',
					'label'        => __( 'Nombre de voyageurs (minimum)', 'adaptours' ),
					'name'         => 'voyageurs_min',
					'type'         => 'number',
					'min'          => 1,
					'step'         => 1,
					'instructions' => __( 'Taille de groupe minimum (ex. : 1).', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_voyageurs_max',
					'label'        => __( 'Nombre de voyageurs (maximum)', 'adaptours' ),
					'name'         => 'voyageurs_max',
					'type'         => 'number',
					'min'          => 1,
					'step'         => 1,
					'instructions' => __( 'Taille de groupe maximum (ex. : 6).', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_temps_vol',
					'label'        => __( 'Temps de vol moyen', 'adaptours' ),
					'name'         => 'temps_vol',
					'type'         => 'text',
					'instructions' => __( 'Durée de vol indicative (ex. : 2h).', 'adaptours' ),
				),
				// Carte du voyage.
				array(
					'key'           => 'field_adaptours_carte_image',
					'label'         => __( 'Carte de la destination', 'adaptours' ),
					'name'          => 'carte_image',
					'type'          => 'image',
					'return_format' => 'id',
					'library'       => 'all',
					'preview_size'  => 'medium',
					'instructions'  => __( 'Image de la carte affichée dans le bloc « Carte du voyage ». Facultatif.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_distance_km',
					'label'         => __( 'Distance depuis Paris (km)', 'adaptours' ),
					'name'          => 'distance_km',
					'type'          => 'number',
					'min'           => 0,
					'step'          => 1,
					'instructions'  => __( 'Distance à vol d’oiseau, en kilomètres (ex. : 11868). Affichée sous le texte de vol.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_decalage_horaire',
					'label'         => __( 'Décalage horaire', 'adaptours' ),
					'name'          => 'decalage_horaire',
					'type'          => 'text',
					'instructions'  => __( 'Affiché dans la bande sous la carte (ex. : +6h en été).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_visa',
					'label'         => __( 'Visa', 'adaptours' ),
					'name'          => 'visa',
					'type'          => 'text',
					'instructions'  => __( 'Formalités d’entrée, en quelques mots (ex. : Sur place 35€).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_monnaie_label',
					'label'         => __( 'Monnaie', 'adaptours' ),
					'name'          => 'monnaie_label',
					'type'          => 'text',
					'instructions'  => __( 'Nom de la monnaie locale (ex. : Rupiah).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_monnaie_code',
					'label'         => __( 'Code de la monnaie', 'adaptours' ),
					'name'          => 'monnaie_code',
					'type'          => 'text',
					'instructions'  => __( 'Code court de la monnaie (ex. : IDR). Facultatif.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_langues_locales',
					'label'         => __( 'Langue(s)', 'adaptours' ),
					'name'          => 'langues_locales',
					'type'          => 'text',
					'instructions'  => __( 'Langue(s) parlée(s) sur place (ex. : Bahasa + anglais).', 'adaptours' ),
				),
				// Accessibilité (4 cartes).
				array(
					'key'           => 'field_adaptours_accessibility_intro',
					'label'         => __( 'Accessibilité — texte d’introduction', 'adaptours' ),
					'name'          => 'accessibility_intro',
					'type'          => 'textarea',
					'rows'          => 3,
					'new_lines'     => '',
					'instructions'  => __( 'Quelques lignes affichées à gauche, à côté des cartes d’accessibilité.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_1_icon',
					'label'         => __( 'Carte 1 — icône', 'adaptours' ),
					'name'          => 'accessibility_card_1_icon',
					'type'          => 'select',
					'choices'       => adaptours_get_icons(),
					'allow_null'    => 1,
					'ui'            => 1,
					'instructions'  => __( 'Choisissez une icône pour cette carte.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_1_title',
					'label'         => __( 'Carte 1 — titre', 'adaptours' ),
					'name'          => 'accessibility_card_1_title',
					'type'          => 'text',
					'instructions'  => __( 'Titre court de la carte (ex. : Fauteuil roulant).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_1_description',
					'label'         => __( 'Carte 1 — description', 'adaptours' ),
					'name'          => 'accessibility_card_1_description',
					'type'          => 'textarea',
					'rows'          => 2,
					'new_lines'     => '',
					'instructions'  => __( 'Une phrase de détail. Laissez les 3 champs de la carte vides pour la masquer.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_2_icon',
					'label'         => __( 'Carte 2 — icône', 'adaptours' ),
					'name'          => 'accessibility_card_2_icon',
					'type'          => 'select',
					'choices'       => adaptours_get_icons(),
					'allow_null'    => 1,
					'ui'            => 1,
					'instructions'  => __( 'Choisissez une icône pour cette carte.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_2_title',
					'label'         => __( 'Carte 2 — titre', 'adaptours' ),
					'name'          => 'accessibility_card_2_title',
					'type'          => 'text',
					'instructions'  => __( 'Titre court de la carte (ex. : Fauteuil roulant).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_2_description',
					'label'         => __( 'Carte 2 — description', 'adaptours' ),
					'name'          => 'accessibility_card_2_description',
					'type'          => 'textarea',
					'rows'          => 2,
					'new_lines'     => '',
					'instructions'  => __( 'Une phrase de détail. Laissez les 3 champs de la carte vides pour la masquer.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_3_icon',
					'label'         => __( 'Carte 3 — icône', 'adaptours' ),
					'name'          => 'accessibility_card_3_icon',
					'type'          => 'select',
					'choices'       => adaptours_get_icons(),
					'allow_null'    => 1,
					'ui'            => 1,
					'instructions'  => __( 'Choisissez une icône pour cette carte.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_3_title',
					'label'         => __( 'Carte 3 — titre', 'adaptours' ),
					'name'          => 'accessibility_card_3_title',
					'type'          => 'text',
					'instructions'  => __( 'Titre court de la carte (ex. : Fauteuil roulant).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_3_description',
					'label'         => __( 'Carte 3 — description', 'adaptours' ),
					'name'          => 'accessibility_card_3_description',
					'type'          => 'textarea',
					'rows'          => 2,
					'new_lines'     => '',
					'instructions'  => __( 'Une phrase de détail. Laissez les 3 champs de la carte vides pour la masquer.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_4_icon',
					'label'         => __( 'Carte 4 — icône', 'adaptours' ),
					'name'          => 'accessibility_card_4_icon',
					'type'          => 'select',
					'choices'       => adaptours_get_icons(),
					'allow_null'    => 1,
					'ui'            => 1,
					'instructions'  => __( 'Choisissez une icône pour cette carte.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_4_title',
					'label'         => __( 'Carte 4 — titre', 'adaptours' ),
					'name'          => 'accessibility_card_4_title',
					'type'          => 'text',
					'instructions'  => __( 'Titre court de la carte (ex. : Fauteuil roulant).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_accessibility_card_4_description',
					'label'         => __( 'Carte 4 — description', 'adaptours' ),
					'name'          => 'accessibility_card_4_description',
					'type'          => 'textarea',
					'rows'          => 2,
					'new_lines'     => '',
					'instructions'  => __( 'Une phrase de détail. Laissez les 3 champs de la carte vides pour la masquer.', 'adaptours' ),
				),
				// Suggestions « Vous aimerez aussi ».
				array(
					'key'           => 'field_adaptours_suggestions',
					'label'         => __( 'Destinations suggérées', 'adaptours' ),
					'name'          => 'suggestions',
					'type'          => 'relationship',
					'post_type'     => array( 'destination' ),
					'filters'       => array( 'search' ),
					'max'           => 4,
					'return_format' => 'id',
					'instructions'  => __( 'Jusqu’à 4 destinations affichées en bas de page (« Vous aimerez aussi »). Laissez vide pour ne rien afficher.', 'adaptours' ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'destination',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'adaptours_register_acf_fields' );

/**
 * Groupe de champs du CPT `avis` (contenu d'un témoignage).
 *
 * Champs ACF Free uniquement. `post_title` reste un identifiant d'admin jamais affiché ;
 * tout le contenu visible passe par ces champs. La synchronisation Polylang est gérée dans
 * inc/polylang.php.
 */
function adaptours_register_acf_avis_fields() {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_adaptours_avis',
			'title'                 => __( 'Contenu de l’avis', 'adaptours' ),
			'fields'                => array(
				array(
					'key'      => 'field_adaptours_avis_nom_voyageur',
					'label'    => __( 'Nom du voyageur', 'adaptours' ),
					'name'     => 'nom_voyageur',
					'type'     => 'text',
					'required' => 1,
					'instructions' => __( 'Nom affiché sous l’avis (ex. : Claire M.).', 'adaptours' ),
				),
				array(
					'key'      => 'field_adaptours_avis_temoignage',
					'label'    => __( 'Témoignage', 'adaptours' ),
					'name'     => 'temoignage',
					'type'     => 'textarea',
					'required' => 1,
					'rows'     => 5,
					'new_lines' => '',
					'instructions' => __( 'Le texte de l’avis.', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_avis_contexte',
					'label'        => __( 'Contexte', 'adaptours' ),
					'name'         => 'contexte',
					'type'         => 'text',
					'instructions' => __( 'Petite précision affichée sous le nom (ex. : Voyage en famille).', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_avis_mois_voyage',
					'label'         => __( 'Mois du voyage', 'adaptours' ),
					'name'          => 'mois_voyage',
					'type'          => 'date_picker',
					'display_format' => 'm/Y',
					'return_format' => 'Ym',
					'first_day'     => 1,
					'instructions'  => __( 'Mois et année du voyage.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_avis_note',
					'label'         => __( 'Note (sur 5)', 'adaptours' ),
					'name'          => 'note',
					'type'          => 'number',
					'min'           => 1,
					'max'           => 5,
					'step'          => 1,
					'default_value' => 5,
					'instructions'  => __( 'Note de 1 à 5 étoiles.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_avis_destination_liee',
					'label'         => __( 'Destination liée', 'adaptours' ),
					'name'          => 'destination_liee',
					'type'          => 'post_object',
					'post_type'     => array( 'destination' ),
					'return_format' => 'id',
					'multiple'      => 0,
					'allow_null'    => 1,
					'ui'            => 1,
					'instructions'  => __( 'Destination concernée par cet avis.', 'adaptours' ),
				),
				array(
					'key'           => 'field_adaptours_avis_photo_voyageur',
					'label'         => __( 'Photo du voyageur', 'adaptours' ),
					'name'          => 'photo_voyageur',
					'type'          => 'image',
					'return_format' => 'id',
					'library'       => 'all',
					'preview_size'  => 'thumbnail',
					'instructions'  => __( 'Photo du voyageur (facultatif).', 'adaptours' ),
				),
				array(
					'key'          => 'field_adaptours_avis_is_featured',
					'label'        => __( 'Avis du mois', 'adaptours' ),
					'name'         => 'is_featured',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => __( 'Affiche cet avis comme « avis du mois » sur la page d’accueil.', 'adaptours' ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'avis',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'adaptours_register_acf_avis_fields' );

/**
 * Champ sur les pièces jointes (images) : badge « jour » de la galerie (ex. « Jour 2 »).
 *
 * Stocké sur l'attachement, donc réutilisable quelle que soit la destination qui l'emploie.
 */
function adaptours_register_acf_attachment_fields() {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_adaptours_attachment',
			'title'                 => __( 'Galerie Adaptours', 'adaptours' ),
			'fields'                => array(
				array(
					'key'          => 'field_adaptours_jour',
					'label'        => __( 'Badge « jour »', 'adaptours' ),
					'name'         => 'adaptours_jour',
					'type'         => 'text',
					'instructions' => __( 'Petit badge affiché sur la photo dans la galerie (ex. : Jour 2). Laissez vide pour ne rien afficher.', 'adaptours' ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'attachment',
						'operator' => '==',
						'value'    => 'image',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'adaptours_register_acf_attachment_fields' );
