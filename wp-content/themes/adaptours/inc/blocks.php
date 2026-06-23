<?php
/**
 * Enregistrement des blocs adaptours/* et verrouillage de l'édition par contexte.
 *
 * `templateLock` et `allowedBlocks` cadrent l'expérience d'édition (palette, structure
 * figée). Ce ne sont pas des contrôles de sécurité : celle-ci repose sur les capabilities
 * du rôle cliente (inc/roles.php).
 *
 * Les blocs vivent dans `blocks/<nom>/` (source) mais sont compilés par @wordpress/scripts
 * vers `assets/build/blocks/<nom>/`. register_block_type() cible donc la sortie de build :
 * un bloc n'apparaît qu'après `npm run build`.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catégorie de blocs « Adaptours », référencée par chaque block.json.
 *
 * @param array $categories Catégories de blocs existantes.
 * @return array
 */
function adaptours_block_categories( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'adaptours',
			'title' => __( 'Adaptours', 'adaptours' ),
			'icon'  => null,
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'adaptours_block_categories' );

/**
 * Enregistre automatiquement chaque dossier de bloc compilé contenant un block.json.
 */
function adaptours_register_blocks() {
	$build_dir = ADAPTOURS_DIR . '/assets/build/blocks';

	if ( ! is_dir( $build_dir ) ) {
		return; // Build pas encore lancé.
	}

	foreach ( (array) glob( $build_dir . '/*', GLOB_ONLYDIR ) as $dir ) {
		if ( file_exists( $dir . '/block.json' ) ) {
			register_block_type( $dir );
		}
	}
}
add_action( 'init', 'adaptours_register_blocks' );

/**
 * Noms des blocs adaptours/* enregistrés.
 *
 * `allowed_block_types_all` n'accepte pas de joker : restreindre la palette à adaptours/*
 * suppose donc d'énumérer les noms exacts.
 *
 * @return string[]
 */
function adaptours_registered_block_names() {
	$names = array();
	foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
		if ( 0 === strpos( $name, 'adaptours/' ) ) {
			$names[] = $name;
		}
	}
	return $names;
}

/**
 * Carte de verrouillage centrale, par contexte d'édition.
 *
 * Pour chaque contexte :
 *  - `lock`     : valeur de `templateLock` ('all' = figé, false = libre).
 *  - `template` : structure de blocs pré-insérée (liste ordonnée).
 *  - `allowed`  : 'adaptours' → palette restreinte aux blocs adaptours/* enregistrés.
 *
 * Les tableaux `template` ne doivent référencer que des blocs déjà enregistrés :
 * un bloc inexistant casse l'éditeur.
 *
 * @return array<string, array>
 */
function adaptours_lock_map() {
	return array(
		// Homepage — structure entièrement figée.
		'front-page'               => array(
			'lock'     => 'all',
			'template' => array(
				array( 'adaptours/hero-home' ),
				array( 'adaptours/kpi-bar' ),
				array( 'adaptours/section-promise' ),
				array( 'adaptours/destinations-grid' ),
				array( 'adaptours/process' ),
				array( 'adaptours/avis-grid' ),
				array( 'adaptours/content-storytelling' ),
				array( 'adaptours/team-intro' ),
			),
		),
		// Pages à structure figée.
		'template-devis'           => array(
			'lock'     => 'all',
			'template' => array(
				array( 'adaptours/hero-devis' ),
				array( 'adaptours/devis-form' ),
			),
		),
		// Qui sommes-nous — page éditoriale figée (7 sections, ordre fixe).
		// Le bloc kpi-bar est posé à 5 colonnes (vs 4 sur la home) via l'attribut `columns`
		// et pré-rempli (sinon il ne s'affiche pas tant que les chiffres sont vides). Les
		// repeaters libres (équipe, conditions) sont des InnerBlocks : la cliente ajoute/retire
		// des éléments à l'intérieur, mais ne peut pas toucher l'ordre des 7 sections (lock all).
		'template-qui-sommes-nous' => array(
			'lock'     => 'all',
			'template' => array(
				array( 'adaptours/hero-qsn' ),
				array( 'adaptours/founder-story' ),
				array(
					'adaptours/kpi-bar',
					array(
						'columns'     => 5,
						'kpi_1_value' => '+800',
						'kpi_1_label' => __( 'voyageurs accompagnés', 'adaptours' ),
						'kpi_2_value' => '12',
						'kpi_2_label' => __( 'personnes dans l’équipe', 'adaptours' ),
						'kpi_3_value' => '32',
						'kpi_3_label' => __( 'partenaires sur place', 'adaptours' ),
						'kpi_4_value' => '9',
						'kpi_4_label' => __( 'langues parlées au bureau', 'adaptours' ),
						'kpi_5_value' => '15',
						'kpi_5_label' => __( 'ans à dire oui', 'adaptours' ),
					),
				),
				array( 'adaptours/team-grid' ),
				array( 'adaptours/avis-grid' ),
				array( 'adaptours/recruitment' ),
				array( 'adaptours/dual-cta' ),
			),
		),
		'template-contact'         => array(
			'lock'     => 'all',
			'template' => array(
				array( 'adaptours/hero-contact' ),
				array( 'adaptours/contact-form' ),
				array( 'adaptours/legal-info' ),
			),
		),
		// Page modulaire — libre, palette restreinte à adaptours/*. Démarrage avec un
		// bloc « En-tête de page » pré-posé ; la cliente ajoute/retire/réordonne ensuite.
		'template-page-modulaire'  => array(
			'lock'     => false,
			'allowed'  => 'adaptours',
			'template' => array(
				array( 'adaptours/page-header' ),
			),
		),
		// Single destination — hero et bande méta rendus en PHP ; la zone Gutenberg est
		// pré-remplie avec les 7 sections, réordonnables et supprimables (lock false).
		'single-destination'       => array(
			'lock'     => false,
			'allowed'  => 'adaptours',
			'template' => array(
				array( 'adaptours/section-map' ),
				array( 'adaptours/section-accessibility' ),
				array( 'adaptours/destination-gallery' ),
				array( 'adaptours/itinerary' ),
				array( 'adaptours/avis-spotlight' ),
				array( 'adaptours/section-practical' ),
				array( 'adaptours/destinations-suggestions' ),
			),
		),
		// L'archive destinations est rendue en PHP (pas d'éditeur de blocs) : absente d'ici.
	);
}

/**
 * Détermine le contexte d'édition du post courant.
 *
 * @param WP_Post|null $post Post édité (depuis WP_Block_Editor_Context).
 * @return string Clé de adaptours_lock_map(), ou '' si non géré.
 */
function adaptours_block_context( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	if ( 'page' === $post->post_type && (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
		return 'front-page';
	}

	if ( 'destination' === $post->post_type ) {
		return 'single-destination';
	}

	$template_contexts = array(
		'template-devis.php'             => 'template-devis',
		'template-qui-sommes-nous.php'   => 'template-qui-sommes-nous',
		'template-contact.php'           => 'template-contact',
		'template-page-modulaire.php'    => 'template-page-modulaire',
	);
	$slug = get_page_template_slug( $post );
	if ( $slug && isset( $template_contexts[ $slug ] ) ) {
		return $template_contexts[ $slug ];
	}

	return '';
}

/**
 * Restreint la palette de l'inserter selon le contexte (carte de lock).
 *
 * @param bool|string[]            $allowed Liste autorisée (true = tous).
 * @param WP_Block_Editor_Context  $context Contexte de l'éditeur.
 * @return bool|string[]
 */
function adaptours_allowed_block_types( $allowed, $context ) {
	$post = isset( $context->post ) ? $context->post : null;
	$key  = adaptours_block_context( $post );
	if ( '' === $key ) {
		return $allowed; // contexte non géré → palette par défaut inchangée.
	}

	$config = adaptours_lock_map();
	if ( ! isset( $config[ $key ] ) ) {
		return $allowed;
	}

	// Pages figées (templateLock 'all') comme modulaires : palette = blocs adaptours/*.
	// On ajoute les blocs de texte natifs (paragraphe / sous-titre / liste) : ils servent
	// de corps libre au bloc adaptours/rich-text (InnerBlocks). Sans eux, la restriction
	// globale les bloquerait aussi à l'intérieur du bloc. Conséquence assumée : ils sont
	// également insérables à la racine d'une page modulaire (rendu correct).
	return array_merge(
		adaptours_registered_block_names(),
		array( 'core/paragraph', 'core/heading', 'core/list', 'core/list-item' )
	);
}
add_filter( 'allowed_block_types_all', 'adaptours_allowed_block_types', 10, 2 );

/**
 * Injecte `template` + `templateLock` pour le post édité selon la carte de lock.
 *
 * @param array                   $settings Réglages de l'éditeur de blocs.
 * @param WP_Block_Editor_Context $context  Contexte de l'éditeur.
 * @return array
 */
function adaptours_block_editor_settings( $settings, $context ) {
	$post = isset( $context->post ) ? $context->post : null;
	$key  = adaptours_block_context( $post );
	if ( '' === $key ) {
		return $settings;
	}

	$config = adaptours_lock_map();
	if ( ! isset( $config[ $key ] ) ) {
		return $settings;
	}

	if ( array_key_exists( 'template', $config[ $key ] ) ) {
		$settings['template'] = $config[ $key ]['template'];
	}
	if ( array_key_exists( 'lock', $config[ $key ] ) ) {
		$settings['templateLock'] = $config[ $key ]['lock'];
	}

	return $settings;
}
add_filter( 'block_editor_settings_all', 'adaptours_block_editor_settings', 10, 2 );
