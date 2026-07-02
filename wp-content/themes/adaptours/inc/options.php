<?php
/**
 * Page de réglages globale « Coordonnées & liens » (Settings API native).
 *
 * IMPORTANT : page NATIVE (Settings API), pas ACF. acf_add_options_page() est une
 * feature ACF Pro, exclue du projet. Toutes les valeurs sont stockées dans UNE option
 * tableau `adaptours_options`, lue via adaptours_get_option().
 *
 * Accès réservé à la capability custom `manage_adaptours_options` (inc/roles.php) :
 * administrateur + rôle Cliente uniquement.
 *
 * Données pures (tel, email, SIRET…) globales/non traduites. Les chaînes d'affichage
 * (horaires, délai de réponse) sont enregistrées dans Polylang quand il est actif.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ADAPTOURS_OPTION_NAME  = 'adaptours_options';
const ADAPTOURS_OPTION_GROUP = 'adaptours_options_group';
const ADAPTOURS_OPTION_PAGE  = 'adaptours-options';

/**
 * Schéma des réglages : sections → champs (clé => type|label|description).
 *
 * Source de vérité unique, consommée à la fois par le rendu et la sanitisation.
 * Types supportés : text, email, url, textarea, number_float, number_int.
 *
 * @return array<string,array>
 */
function adaptours_options_schema() {
	return array(
		'coordonnees'    => array(
			'title'  => __( 'Coordonnées', 'adaptours' ),
			'fields' => array(
				'tel_display'  => array(
					'label' => __( 'Téléphone (affiché sur le site)', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Numéro tel qu’il apparaît sur le site, ex. « 04 XX XX XX XX ».', 'adaptours' ),
				),
				'tel_link'     => array(
					'label' => __( 'Téléphone (numéro cliquable)', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Au format international, sans espaces, ex. « +33XXXXXXXXX ». Permet d’appeler d’un clic depuis un mobile.', 'adaptours' ),
				),
				'tel_horaires' => array(
					'label' => __( 'Horaires d’appel', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Ex. « Lundi – Vendredi · 9h → 18h ». Traduisible (Polylang).', 'adaptours' ),
				),
				'email'        => array(
					'label' => __( 'Email de contact', 'adaptours' ),
					'type'  => 'email',
				),
				'email_delai'  => array(
					'label' => __( 'Délai de réponse', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Ex. « Réponse sous 24h ouvrées ». Traduisible (Polylang).', 'adaptours' ),
				),
				'adresse'      => array(
					'label' => __( 'Adresse postale', 'adaptours' ),
					'type'  => 'textarea',
				),
			),
		),
		'liens'          => array(
			'title'  => __( 'Liens internes', 'adaptours' ),
			'fields' => array(
				'url_devis'             => array(
					'label' => __( 'Lien vers la page Devis', 'adaptours' ),
					'type'  => 'url',
				),
				'url_contact'           => array(
					'label' => __( 'Lien vers la page Contact', 'adaptours' ),
					'type'  => 'url',
				),
				'url_cgv'               => array(
					'label' => __( 'Lien vers les CGV', 'adaptours' ),
					'type'  => 'url',
				),
				'url_mentions_legales'  => array(
					'label' => __( 'Lien vers les Mentions légales', 'adaptours' ),
					'type'  => 'url',
				),
				'url_confidentialite'   => array(
					'label' => __( 'Lien vers la Politique de confidentialité', 'adaptours' ),
					'type'  => 'url',
				),
			),
		),
		'reseaux'        => array(
			'title'  => __( 'Réseaux sociaux', 'adaptours' ),
			'fields' => array(
				'url_facebook'  => array(
					'label' => __( 'Lien Facebook', 'adaptours' ),
					'type'  => 'url',
				),
				'url_instagram' => array(
					'label' => __( 'Lien Instagram', 'adaptours' ),
					'type'  => 'url',
				),
				'url_linkedin'  => array(
					'label' => __( 'Lien LinkedIn', 'adaptours' ),
					'type'  => 'url',
				),
			),
		),
		'google'         => array(
			'title'  => __( 'Avis Google', 'adaptours' ),
			'fields' => array(
				'google_rating'       => array(
					'label' => __( 'Note Google (★)', 'adaptours' ),
					'type'  => 'number_float',
					'desc'  => __( 'Saisie manuelle, ex. « 4.9 ». Fallback si pas d’API.', 'adaptours' ),
				),
				'google_review_count' => array(
					'label' => __( 'Nombre d’avis Google', 'adaptours' ),
					'type'  => 'number_int',
				),
			),
		),
		'destinations'   => array(
			'title'  => __( 'Page Destinations', 'adaptours' ),
			'fields' => array(
				'dest_eyebrow'      => array(
					'label' => __( 'Surtitre', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Petit mot affiché au-dessus du titre. Laissez vide pour afficher « CATALOGUE ».', 'adaptours' ),
				),
				'dest_title_part_1' => array(
					'label' => __( 'Titre', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Début du grand titre de la page. Laissez vide pour « Destinations ».', 'adaptours' ),
				),
				'dest_title_part_2' => array(
					'label' => __( 'Mot(s) en orange', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Mot(s) affiché(s) en orange, à la suite du titre. Laissez vide pour « accessibles. ».', 'adaptours' ),
				),
				'dest_intro'        => array(
					'label' => __( 'Texte d’introduction', 'adaptours' ),
					'type'  => 'textarea',
					'desc'  => __( 'Court texte de présentation, à droite du titre.', 'adaptours' ),
				),
				'dest_badge_label'  => array(
					'label' => __( 'Phrase manuscrite', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Petite phrase manuscrite. Écrivez {n} à l’endroit où doit apparaître le nombre de voyages, ex. « {n} voyages prêts à partir » ; le {n} est remplacé automatiquement par le nombre réel.', 'adaptours' ),
				),
			),
		),
		'legal'          => array(
			'title'  => __( 'Informations légales', 'adaptours' ),
			'fields' => array(
				'legal_forme_juridique' => array(
					'label' => __( 'Forme juridique', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Ex. « SARL au capital de 10 000 € ».', 'adaptours' ),
				),
				'legal_siret'           => array(
					'label' => __( 'SIRET', 'adaptours' ),
					'type'  => 'text',
				),
				'legal_naf'             => array(
					'label' => __( 'Code NAF', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Code d’activité de l’entreprise, ex. « 7911Z ». Laissez vide pour ne pas l’afficher.', 'adaptours' ),
				),
				'legal_rcs'             => array(
					'label' => __( 'RCS', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Immatriculation au registre du commerce, ex. « Lyon B 123 456 789 ». Laissez vide pour ne pas l’afficher.', 'adaptours' ),
				),
				'legal_tva'             => array(
					'label' => __( 'Numéro de TVA', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Numéro de TVA intracommunautaire, ex. « FR12 345678901 ». Laissez vide pour ne pas l’afficher.', 'adaptours' ),
				),
				'legal_atout_france'    => array(
					'label' => __( 'N° Atout France', 'adaptours' ),
					'type'  => 'text',
				),
				'legal_apst'            => array(
					'label' => __( 'Garantie APST', 'adaptours' ),
					'type'  => 'text',
					'desc'  => __( 'Si vous êtes adhérent, ex. « Adhérent APST — … ». Laissez vide sinon.', 'adaptours' ),
				),
			),
		),
	);
}

/**
 * Aplatit le schéma en carte clé => type (pour la sanitisation).
 *
 * @return array<string,string>
 */
function adaptours_options_field_types() {
	$types = array();
	foreach ( adaptours_options_schema() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$types[ $key ] = $field['type'];
		}
	}
	return $types;
}

/**
 * Enregistre la page d'admin (top-level), réservée à manage_adaptours_options.
 */
function adaptours_options_menu() {
	add_menu_page(
		__( 'Coordonnées & liens', 'adaptours' ),
		__( 'Coordonnées & liens', 'adaptours' ),
		ADAPTOURS_CAP_OPTIONS,
		ADAPTOURS_OPTION_PAGE,
		'adaptours_options_render_page',
		'dashicons-phone',
		59
	);
}
add_action( 'admin_menu', 'adaptours_options_menu' );

/**
 * Déclare le réglage, ses sections et ses champs (Settings API).
 */
function adaptours_options_init() {
	register_setting(
		ADAPTOURS_OPTION_GROUP,
		ADAPTOURS_OPTION_NAME,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'adaptours_sanitize_options',
			'default'           => array(),
		)
	);

	foreach ( adaptours_options_schema() as $section_id => $section ) {
		add_settings_section(
			'adaptours_section_' . $section_id,
			$section['title'],
			'__return_false',
			ADAPTOURS_OPTION_PAGE
		);

		foreach ( $section['fields'] as $key => $field ) {
			add_settings_field(
				'adaptours_field_' . $key,
				$field['label'],
				'adaptours_options_render_field',
				ADAPTOURS_OPTION_PAGE,
				'adaptours_section_' . $section_id,
				array(
					'key'       => $key,
					'type'      => $field['type'],
					'desc'      => isset( $field['desc'] ) ? $field['desc'] : '',
					'label_for' => 'adaptours_field_' . $key,
				)
			);
		}
	}
}
add_action( 'admin_init', 'adaptours_options_init' );

/**
 * Rend un champ du formulaire selon son type. Sortie échappée.
 *
 * @param array $args Contexte du champ (key, type, desc, label_for).
 */
function adaptours_options_render_field( $args ) {
	$key   = $args['key'];
	$type  = $args['type'];
	$id    = $args['label_for'];
	$value = adaptours_get_option( $key );
	$name  = ADAPTOURS_OPTION_NAME . '[' . $key . ']';

	if ( 'textarea' === $type ) {
		printf(
			'<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( $value )
		);
	} else {
		$input_type = 'text';
		$extra      = 'class="regular-text"';

		switch ( $type ) {
			case 'email':
				$input_type = 'email';
				break;
			case 'url':
				$input_type = 'url';
				$extra      = 'class="regular-text code"';
				break;
			case 'number_float':
				$input_type = 'number';
				$extra      = 'class="small-text" step="0.1" min="0"';
				break;
			case 'number_int':
				$input_type = 'number';
				$extra      = 'class="small-text" step="1" min="0"';
				break;
		}

		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s />',
			esc_attr( $input_type ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- littéraux internes contrôlés ci-dessus.
		);
	}

	if ( '' !== $args['desc'] ) {
		printf( '<p class="description">%s</p>', esc_html( $args['desc'] ) );
	}
}

/**
 * Sanitise l'option `adaptours_options` champ par champ selon son type.
 *
 * @param mixed $input Valeurs brutes postées.
 * @return array Valeurs sûres.
 */
function adaptours_sanitize_options( $input ) {
	$input  = is_array( $input ) ? $input : array();
	$types  = adaptours_options_field_types();
	$output = array();

	foreach ( $types as $key => $type ) {
		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';

		switch ( $type ) {
			case 'email':
				$output[ $key ] = sanitize_email( $raw );
				break;
			case 'url':
				$output[ $key ] = esc_url_raw( $raw );
				break;
			case 'textarea':
				$output[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'number_float':
				$output[ $key ] = ( '' === $raw ) ? '' : (string) floatval( $raw );
				break;
			case 'number_int':
				$output[ $key ] = ( '' === $raw ) ? '' : (string) absint( $raw );
				break;
			default:
				$output[ $key ] = sanitize_text_field( $raw );
				break;
		}
	}

	return $output;
}

/**
 * Rend la page de réglages (formulaire Settings API).
 */
function adaptours_options_render_page() {
	if ( ! current_user_can( ADAPTOURS_CAP_OPTIONS ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( ADAPTOURS_OPTION_GROUP );
			do_settings_sections( ADAPTOURS_OPTION_PAGE );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Lit une valeur de réglage. Retourne la valeur BRUTE (échappement délégué aux
 * templates — jamais ici).
 *
 * @param string $key     Clé du champ (ex. « email », « tel_display »).
 * @param string $default Valeur par défaut si absente/vide.
 * @return mixed
 */
function adaptours_get_option( $key, $default = '' ) {
	$options = get_option( ADAPTOURS_OPTION_NAME, array() );

	if ( is_array( $options ) && isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}

	return $default;
}

/**
 * Enregistre dans Polylang les chaînes d'affichage traduisibles (si actif).
 *
 * Seules les phrases affichées sont traduites ; les données pures (tel, email, SIRET,
 * RCS, TVA, Atout France…) restent globales.
 */
function adaptours_register_option_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$translatable = array(
		'tel_horaires',
		'email_delai',
		'dest_eyebrow',
		'dest_title_part_1',
		'dest_title_part_2',
		'dest_intro',
		'dest_badge_label',
	);

	foreach ( $translatable as $key ) {
		$value = adaptours_get_option( $key );
		if ( '' !== $value ) {
			pll_register_string( 'adaptours_' . $key, $value, 'Adaptours' );
		}
	}
}
add_action( 'init', 'adaptours_register_option_strings' );
