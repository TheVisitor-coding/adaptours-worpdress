<?php
/**
 * Page Devis — formulaire CF7, sur l'infra posée pour Contact (inc/cf7.php).
 *
 * - Formulaire Devis FR créé de façon idempotente, ID en option `adaptours_devis_form_id`.
 * - Logique conditionnelle « Vous êtes » via CF7 Conditional Fields : balises [group] dans
 *   le corps + règles stockées dans le post meta `wpcf7cf_options`.
 * - Dropdown « Destination choisie » peuplé dynamiquement (filtre wpcf7_form_elements),
 *   avec pré-remplissage par `?dest={slug}`.
 * - Mail interne récapitulatif en HTML + auto-réponse au demandeur.
 *
 * Le honeypot anti-spam est partagé avec Contact (constante + filtre dans inc/cf7.php) :
 * il suffit d'inclure le champ caché dans ce formulaire.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nom interne du champ select des destinations (réutilisé par le filtre d'injection).
 */
const ADAPTOURS_DEVIS_DEST_FIELD = 'devis-destination';

/**
 * Paramètre d'URL de pré-remplissage de la destination.
 *
 * `dest` et non `destination` : ce dernier est la query var publique du CPT du même nom,
 * et `?destination=…` détournerait la requête principale (le formulaire ne se rendrait
 * plus). Le CTA « Demander un devis » du single (inc/helpers.php) ajoute ce paramètre.
 */
const ADAPTOURS_DEVIS_PREFILL_PARAM = 'dest';

/**
 * ID du formulaire Devis FR (0 si non encore créé / CF7 absent).
 *
 * Prêt pour le multilingue : renvoie l'équivalent traduit via pll_get_post() le moment venu.
 *
 * @return int
 */
function adaptours_get_devis_form_id() {
	$id = (int) get_option( 'adaptours_devis_form_id', 0 );

	if ( $id > 0 && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id );
		if ( $translated ) {
			$id = (int) $translated;
		}
	}

	return $id;
}

/**
 * Libellés (= valeurs) des 3 profils « Vous êtes ».
 *
 * Centralisés : utilisés à la fois dans la balise [radio] et dans les règles conditionnelles
 * (le if_value doit correspondre au caractère près à la valeur soumise par le bouton radio).
 *
 * @return array{particulier:string,agence:string,partenaire:string}
 */
function adaptours_devis_statut_labels() {
	return array(
		'particulier' => __( 'Particulier', 'adaptours' ),
		'agence'      => __( 'Agence / revendeur', 'adaptours' ),
		'partenaire'  => __( 'Partenaire (asso, MDPH…)', 'adaptours' ),
	);
}

/**
 * Crée le formulaire Devis FR s'il n'existe pas encore (idempotent).
 *
 * Déclenché en admin.
 */
function adaptours_cf7_ensure_devis_form() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return;
	}

	$existing = (int) get_option( 'adaptours_devis_form_id', 0 );
	if ( $existing > 0 && 'wpcf7_contact_form' === get_post_type( $existing ) ) {
		return;
	}

	$email_to     = (string) adaptours_get_option( 'email', get_option( 'admin_email' ) );
	$privacy_url  = (string) adaptours_get_option( 'url_confidentialite', '#' );
	$privacy_link = '<a href="' . esc_url( $privacy_url ) . '">' . esc_html__( 'politique de confidentialité', 'adaptours' ) . '</a>';

	$statut = adaptours_devis_statut_labels();

	$form_body = adaptours_devis_form_body( $statut, $privacy_link );

	$mail = array(
		'subject'            => __( '[Devis] [devis-destination] — [devis-nom-prenom][devis-nom-client]', 'adaptours' ),
		'sender'             => '[_site_title] <' . $email_to . '>',
		'recipient'          => $email_to,
		'body'               => adaptours_devis_mail_body_html(),
		'additional_headers' => 'Reply-To: [devis-email]',
		'attachments'        => '',
		'use_html'           => 1,
		'exclude_blank'      => 0,
	);

	$mail_2_body = __( 'Bonjour [devis-nom-prenom][devis-nom-client],', 'adaptours' ) . "\n\n"
		. __( 'Merci pour votre demande de devis concernant : [devis-destination].', 'adaptours' ) . "\n\n"
		. __( 'Nous avons bien reçu toutes vos informations et revenons vers vous sous 48 h ouvrées avec une proposition sur mesure.', 'adaptours' ) . "\n\n"
		. __( 'À très vite,', 'adaptours' ) . "\n" . __( 'L’équipe Adaptours', 'adaptours' ) . "\n";

	$mail_2 = array(
		'active'             => true,
		'subject'            => __( 'Votre demande de devis Adaptours — bien reçue', 'adaptours' ),
		'sender'             => '[_site_title] <' . $email_to . '>',
		'recipient'          => '[devis-email]',
		'body'               => $mail_2_body,
		'additional_headers' => 'Reply-To: ' . $email_to,
		'attachments'        => '',
		'use_html'           => 0,
		'exclude_blank'      => 0,
	);

	// Messages : repartir des libellés CF7 par défaut (localisés).
	$messages = array();
	if ( function_exists( 'wpcf7_messages' ) ) {
		foreach ( wpcf7_messages() as $key => $arr ) {
			$messages[ $key ] = isset( $arr['default'] ) ? $arr['default'] : '';
		}
	}
	$messages['mail_sent_ok'] = __( 'Merci ! Votre demande est arrivée. On revient vers vous sous 48 h ouvrées avec un devis sur mesure.', 'adaptours' );

	$form = WPCF7_ContactForm::get_template(
		array( 'title' => __( 'Devis (FR)', 'adaptours' ) )
	);
	$form->set_properties(
		array(
			'form'     => $form_body,
			'mail'     => $mail,
			'mail_2'   => $mail_2,
			'messages' => $messages,
		)
	);
	$id = $form->save();

	if ( $id ) {
		update_option( 'adaptours_devis_form_id', (int) $id );
		adaptours_devis_set_conditions( (int) $id, $statut );
	}
}
add_action( 'admin_init', 'adaptours_cf7_ensure_devis_form' );

/**
 * Corps du formulaire (balises CF7 + HTML de structure).
 *
 * Sections dans l'ordre du formulaire ; classes BEM `devis-form__*` pour le style scopé
 * du bloc. Les titres de section et le filet horizontal sont portés par le CSS (legend).
 *
 * @param array  $statut       Libellés des 3 profils.
 * @param string $privacy_link Lien HTML vers la politique de confidentialité.
 * @return string
 */
function adaptours_devis_form_body( $statut, $privacy_link ) {
	$hp = ADAPTOURS_CF7_HONEYPOT;

	ob_start();
	?>
<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Vous êtes', 'adaptours' ); ?></legend>
	<div class="devis-form__pills devis-form__pills--statut">
		[radio devis-statut default:1 use_label_element "<?php echo esc_attr( $statut['particulier'] ); ?>" "<?php echo esc_attr( $statut['agence'] ); ?>" "<?php echo esc_attr( $statut['partenaire'] ); ?>"]
	</div>
	[group grp-particulier clear_on_hide]
	<p class="devis-form__field">
		<label class="devis-form__label"><?php esc_html_e( 'Votre nom et prénom', 'adaptours' ); ?></label>
		[text* devis-nom-prenom placeholder "<?php esc_attr_e( 'Caroline Martin', 'adaptours' ); ?>"]
	</p>
	[/group]
	[group grp-nomclient clear_on_hide]
	<p class="devis-form__field">
		<label class="devis-form__label"><?php esc_html_e( 'Nom du client', 'adaptours' ); ?></label>
		[text* devis-nom-client placeholder "<?php esc_attr_e( 'Nom du voyageur', 'adaptours' ); ?>"]
	</p>
	[/group]
	[group grp-agence clear_on_hide]
	<p class="devis-form__field">
		<label class="devis-form__label"><?php esc_html_e( 'Agence / revendeur', 'adaptours' ); ?></label>
		[text* devis-agence placeholder "<?php esc_attr_e( 'Nom de l’agence', 'adaptours' ); ?>"]
	</p>
	[/group]
	[group grp-partenaire clear_on_hide]
	<p class="devis-form__field">
		<label class="devis-form__label"><?php esc_html_e( 'Structure partenaire', 'adaptours' ); ?></label>
		[text* devis-partenaire placeholder "<?php esc_attr_e( 'Association, MDPH…', 'adaptours' ); ?>"]
	</p>
	[/group]
</fieldset>

<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Vos coordonnées', 'adaptours' ); ?></legend>
	<p class="devis-form__field">
		<label class="devis-form__label"><?php esc_html_e( 'Adresse e-mail', 'adaptours' ); ?></label>
		[email* devis-email placeholder "<?php esc_attr_e( 'caroline@exemple.fr', 'adaptours' ); ?>"]
	</p>
	<div class="devis-form__row">
		<p class="devis-form__field">
			<label class="devis-form__label"><?php esc_html_e( 'Portable', 'adaptours' ); ?></label>
			[tel* devis-portable placeholder "<?php esc_attr_e( '06 12 34 56 78', 'adaptours' ); ?>"]
		</p>
		<p class="devis-form__field">
			<label class="devis-form__label"><?php esc_html_e( 'Téléphone fixe', 'adaptours' ); ?> <span class="devis-form__optional"><?php esc_html_e( 'facultatif', 'adaptours' ); ?></span></label>
			[tel devis-fixe placeholder "<?php esc_attr_e( '04 12 34 56 78', 'adaptours' ); ?>"]
		</p>
	</div>
</fieldset>

<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Votre voyage', 'adaptours' ); ?></legend>
	<p class="devis-form__field">
		<label class="devis-form__label"><?php esc_html_e( 'Destination choisie', 'adaptours' ); ?></label>
		[select* <?php echo esc_html( ADAPTOURS_DEVIS_DEST_FIELD ); ?> "<?php esc_attr_e( '— Choisir une destination —', 'adaptours' ); ?>"]
	</p>
	<div class="devis-form__field">
		<span class="devis-form__label"><?php esc_html_e( 'Vol et transport', 'adaptours' ); ?></span>
		<div class="devis-form__pills">
			[radio devis-transport default:1 use_label_element "<?php esc_attr_e( 'Avion', 'adaptours' ); ?>" "<?php esc_attr_e( 'Train', 'adaptours' ); ?>" "<?php esc_attr_e( 'Voiture', 'adaptours' ); ?>" "<?php esc_attr_e( 'À voir ensemble', 'adaptours' ); ?>"]
		</div>
	</div>
	<div class="devis-form__row">
		<p class="devis-form__field">
			<label class="devis-form__label"><?php esc_html_e( 'Ville de départ', 'adaptours' ); ?></label>
			[text* devis-ville-depart placeholder "<?php esc_attr_e( 'ex. Lyon', 'adaptours' ); ?>"]
		</p>
		<p class="devis-form__field">
			<label class="devis-form__label"><?php esc_html_e( 'Dates ou période souhaitée', 'adaptours' ); ?></label>
			[text* devis-dates placeholder "<?php esc_attr_e( 'ex. 12 → 22 mars', 'adaptours' ); ?>"]
		</p>
	</div>
</fieldset>

<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Les voyageurs', 'adaptours' ); ?></legend>
	<div class="devis-form__stepper-row">
		<span class="devis-form__label"><?php esc_html_e( 'Nombre total de personnes', 'adaptours' ); ?></span>
		<span class="devis-form__stepper">[number* devis-total min:1 max:20 "2"]</span>
	</div>
	<div class="devis-form__stepper-row">
		<span class="devis-form__label"><?php esc_html_e( 'Dont enfants', 'adaptours' ); ?><span class="devis-form__sublabel"><?php esc_html_e( 'Moins de 18 ans', 'adaptours' ); ?></span></span>
		<span class="devis-form__stepper">[number* devis-enfants min:0 max:20 "0"]</span>
	</div>
	<p class="devis-form__field devis-form__field--budget">
		<label class="devis-form__label"><?php esc_html_e( 'Budget par personne', 'adaptours' ); ?> <span class="devis-form__optional"><?php esc_html_e( 'indicatif', 'adaptours' ); ?></span></label>
		<span class="devis-form__currency">[text* devis-budget placeholder "<?php esc_attr_e( 'ex. 2 500 €', 'adaptours' ); ?>"]</span>
	</p>
</fieldset>

<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Mobilité', 'adaptours' ); ?></legend>
	<div class="devis-form__stepper-row">
		<span class="devis-form__label"><?php esc_html_e( 'Fauteuils roulants manuels', 'adaptours' ); ?></span>
		<span class="devis-form__stepper">[number* devis-fauteuils-manuels min:0 max:20 "0"]</span>
	</div>
	<div class="devis-form__stepper-row">
		<span class="devis-form__label"><?php esc_html_e( 'Fauteuils roulants électriques', 'adaptours' ); ?></span>
		<span class="devis-form__stepper">[number* devis-fauteuils-electriques min:0 max:20 "0"]</span>
	</div>
	<div class="devis-form__field">
		<span class="devis-form__label"><?php esc_html_e( 'Véhicule adapté (avec rampe) sur place', 'adaptours' ); ?></span>
		<div class="devis-form__pills">
			[radio devis-vehicule default:3 use_label_element "<?php esc_attr_e( 'Indispensable', 'adaptours' ); ?>" "<?php esc_attr_e( 'Non', 'adaptours' ); ?>" "<?php esc_attr_e( 'À voir', 'adaptours' ); ?>"]
		</div>
	</div>
</fieldset>

<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Matériel sur place', 'adaptours' ); ?></legend>
	<div class="devis-form__stepper-row">
		<span class="devis-form__label"><?php esc_html_e( 'Sièges de douche', 'adaptours' ); ?></span>
		<span class="devis-form__stepper">[number* devis-sieges-douche min:0 max:10 "0"]</span>
	</div>
	<div class="devis-form__stepper-row">
		<span class="devis-form__label"><?php esc_html_e( 'Lits médicalisés', 'adaptours' ); ?></span>
		<span class="devis-form__stepper">[number* devis-lits min:0 max:10 "0"]</span>
	</div>
	<div class="devis-form__stepper-row">
		<span class="devis-form__label"><?php esc_html_e( 'Lève-personnes', 'adaptours' ); ?></span>
		<span class="devis-form__stepper">[number* devis-leve-personnes min:0 max:10 "0"]</span>
	</div>
</fieldset>

<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Accompagnement & soins', 'adaptours' ); ?></legend>
	<div class="devis-form__field">
		<span class="devis-form__label"><?php esc_html_e( 'Accompagnateur fourni par Adaptours', 'adaptours' ); ?><span class="devis-form__sublabel"><?php esc_html_e( 'Un membre de notre équipe vous accompagne sur tout le séjour.', 'adaptours' ); ?></span></span>
		<div class="devis-form__pills devis-form__pills--binary">
			[radio devis-accompagnateur default:2 use_label_element "<?php esc_attr_e( 'Oui', 'adaptours' ); ?>" "<?php esc_attr_e( 'Non', 'adaptours' ); ?>"]
		</div>
	</div>
	<div class="devis-form__field">
		<span class="devis-form__label"><?php esc_html_e( 'Soins d’auxiliaire de vie', 'adaptours' ); ?></span>
		<div class="devis-form__pills devis-form__pills--binary">
			[radio devis-auxiliaire default:2 use_label_element "<?php esc_attr_e( 'Oui', 'adaptours' ); ?>" "<?php esc_attr_e( 'Non', 'adaptours' ); ?>"]
		</div>
	</div>
	<div class="devis-form__field">
		<span class="devis-form__label"><?php esc_html_e( 'Soins infirmiers', 'adaptours' ); ?></span>
		<div class="devis-form__pills devis-form__pills--binary">
			[radio devis-infirmiers default:2 use_label_element "<?php esc_attr_e( 'Oui', 'adaptours' ); ?>" "<?php esc_attr_e( 'Non', 'adaptours' ); ?>"]
		</div>
	</div>
</fieldset>

<fieldset class="devis-form__section">
	<legend class="devis-form__legend"><?php esc_html_e( 'Autre chose ?', 'adaptours' ); ?></legend>
	<p class="devis-form__field">
		<label class="devis-form__label"><?php esc_html_e( 'Besoins spécifiques ou demandes particulières', 'adaptours' ); ?> <span class="devis-form__optional"><?php esc_html_e( 'facultatif', 'adaptours' ); ?></span></label>
		[textarea devis-autre rows 4 placeholder "<?php esc_attr_e( 'Allergie, anniversaire à fêter sur place, peur, rêve précis…', 'adaptours' ); ?>"]
	</p>
</fieldset>

<span class="devis-form__hp" aria-hidden="true">[text <?php echo esc_html( $hp ); ?>]</span>

<p class="devis-form__consent">[acceptance devis-rgpd] <?php
	/* translators: %s: lien vers la politique de confidentialité. */
	printf( esc_html__( 'J’accepte que mes données soient utilisées pour traiter ma demande de devis. Voir notre %s.', 'adaptours' ), $privacy_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lien déjà échappé (esc_url).
?> [/acceptance]</p>

<div class="devis-form__submit">
	[submit "<?php esc_attr_e( 'Envoyer ma demande →', 'adaptours' ); ?>"]
	<span class="devis-form__submit-note"><?php esc_html_e( 'Réponse sous 48 h ouvrées · sans engagement', 'adaptours' ); ?></span>
</div>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Corps HTML du mail interne récapitulatif.
 *
 * HTML léger en styles inline (robustes côté clients mail), structuré par sections du
 * formulaire. Le nom est unifié par concaténation [devis-nom-prenom][devis-nom-client] :
 * grâce à `clear_on_hide`, un seul des deux est rempli.
 *
 * @return string
 */
function adaptours_devis_mail_body_html() {
	$rows = array(
		array(
			'title' => __( 'Profil', 'adaptours' ),
			'lines' => array(
				array( __( 'Vous êtes', 'adaptours' ), '[devis-statut]' ),
				array( __( 'Nom', 'adaptours' ), '[devis-nom-prenom][devis-nom-client]' ),
				array( __( 'Agence / revendeur', 'adaptours' ), '[devis-agence]' ),
				array( __( 'Structure partenaire', 'adaptours' ), '[devis-partenaire]' ),
			),
		),
		array(
			'title' => __( 'Coordonnées', 'adaptours' ),
			'lines' => array(
				array( __( 'E-mail', 'adaptours' ), '[devis-email]' ),
				array( __( 'Portable', 'adaptours' ), '[devis-portable]' ),
				array( __( 'Téléphone fixe', 'adaptours' ), '[devis-fixe]' ),
			),
		),
		array(
			'title' => __( 'Voyage', 'adaptours' ),
			'lines' => array(
				array( __( 'Destination', 'adaptours' ), '[devis-destination]' ),
				array( __( 'Vol et transport', 'adaptours' ), '[devis-transport]' ),
				array( __( 'Ville de départ', 'adaptours' ), '[devis-ville-depart]' ),
				array( __( 'Dates / période', 'adaptours' ), '[devis-dates]' ),
			),
		),
		array(
			'title' => __( 'Voyageurs', 'adaptours' ),
			'lines' => array(
				array( __( 'Nombre total', 'adaptours' ), '[devis-total]' ),
				array( __( 'Dont enfants', 'adaptours' ), '[devis-enfants]' ),
				array( __( 'Budget / personne', 'adaptours' ), '[devis-budget]' ),
			),
		),
		array(
			'title' => __( 'Mobilité', 'adaptours' ),
			'lines' => array(
				array( __( 'Fauteuils manuels', 'adaptours' ), '[devis-fauteuils-manuels]' ),
				array( __( 'Fauteuils électriques', 'adaptours' ), '[devis-fauteuils-electriques]' ),
				array( __( 'Véhicule adapté sur place', 'adaptours' ), '[devis-vehicule]' ),
			),
		),
		array(
			'title' => __( 'Matériel sur place', 'adaptours' ),
			'lines' => array(
				array( __( 'Sièges de douche', 'adaptours' ), '[devis-sieges-douche]' ),
				array( __( 'Lits médicalisés', 'adaptours' ), '[devis-lits]' ),
				array( __( 'Lève-personnes', 'adaptours' ), '[devis-leve-personnes]' ),
			),
		),
		array(
			'title' => __( 'Accompagnement & soins', 'adaptours' ),
			'lines' => array(
				array( __( 'Accompagnateur Adaptours', 'adaptours' ), '[devis-accompagnateur]' ),
				array( __( 'Soins d’auxiliaire de vie', 'adaptours' ), '[devis-auxiliaire]' ),
				array( __( 'Soins infirmiers', 'adaptours' ), '[devis-infirmiers]' ),
			),
		),
		array(
			'title' => __( 'Autre', 'adaptours' ),
			'lines' => array(
				array( __( 'Besoins / demandes', 'adaptours' ), '[devis-autre]' ),
			),
		),
	);

	$html  = '<div style="font-family:Arial,Helvetica,sans-serif;color:#1B1D2A;max-width:640px;">';
	$html .= '<h2 style="color:#C2502E;">' . esc_html__( 'Nouvelle demande de devis', 'adaptours' ) . '</h2>';

	foreach ( $rows as $section ) {
		$html .= '<h3 style="margin:24px 0 8px;border-bottom:1px solid #E0DACB;padding-bottom:4px;">' . esc_html( $section['title'] ) . '</h3>';
		$html .= '<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">';
		foreach ( $section['lines'] as $line ) {
			$html .= '<tr>'
				. '<td style="padding:4px 12px 4px 0;color:#4A4D5C;width:220px;vertical-align:top;">' . esc_html( $line[0] ) . '</td>'
				. '<td style="padding:4px 0;font-weight:bold;">' . $line[1] . '</td>'
				. '</tr>';
		}
		$html .= '</table>';
	}

	$html .= '</div>';

	return $html;
}

/**
 * Définit les règles conditionnelles (plugin CF7 Conditional Fields) pour la section
 * « Vous êtes ». Stockées dans le post meta `wpcf7cf_options` (format natif du plugin).
 *
 * - Particulier        → affiche grp-particulier (nom & prénom).
 * - Agence / Partenaire → affiche grp-nomclient (nom du client) [2 entrées = OU].
 * - Agence             → affiche grp-agence.
 * - Partenaire         → affiche grp-partenaire.
 *
 * @param int   $form_id ID du formulaire.
 * @param array $statut  Libellés des profils.
 */
function adaptours_devis_set_conditions( $form_id, $statut ) {
	$rule = static function ( $then_field, $value ) {
		return array(
			'then_field' => $then_field,
			'and_rules'  => array(
				array(
					'if_field' => 'devis-statut',
					'operator' => 'equals',
					'if_value' => $value,
				),
			),
		);
	};

	$conditions = array(
		$rule( 'grp-particulier', $statut['particulier'] ),
		$rule( 'grp-nomclient', $statut['agence'] ),
		$rule( 'grp-nomclient', $statut['partenaire'] ),
		$rule( 'grp-agence', $statut['agence'] ),
		$rule( 'grp-partenaire', $statut['partenaire'] ),
	);

	update_post_meta( $form_id, 'wpcf7cf_options', $conditions );
}

/**
 * Peuple le select « Destination choisie » et gère le pré-remplissage `?dest={slug}`.
 *
 * Le corps CF7 est une chaîne statique : on post-traite le HTML rendu pour injecter les
 * options des destinations publiées. Option vide en tête, « Sur-mesure » en queue. Si
 * `?dest={slug}` désigne une destination publiée, son option reçoit l'attribut `selected` ;
 * sinon, le formulaire reste vierge sans erreur.
 *
 * @param string $html HTML rendu du formulaire CF7.
 * @return string
 */
function adaptours_devis_inject_destinations( $html ) {
	$field = ADAPTOURS_DEVIS_DEST_FIELD;

	// Ne traiter que le formulaire Devis (présence du select ciblé).
	if ( false === strpos( $html, 'name="' . $field . '"' ) ) {
		return $html;
	}

	// Destination présélectionnée via le paramètre `dest` (voir ADAPTOURS_DEVIS_PREFILL_PARAM).
	$selected_title = '';
	if ( isset( $_GET[ ADAPTOURS_DEVIS_PREFILL_PARAM ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture GET en pré-remplissage, sans effet de bord.
		$slug = sanitize_key( wp_unslash( $_GET[ ADAPTOURS_DEVIS_PREFILL_PARAM ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $slug ) {
			$dest = get_page_by_path( $slug, OBJECT, 'destination' );
			if ( $dest && 'publish' === get_post_status( $dest ) ) {
				$selected_title = get_the_title( $dest );
			}
		}
	}

	$args = array(
		'post_type'      => 'destination',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$args['lang'] = pll_current_language();
	}
	$destinations = get_posts( $args );

	$options  = '<option value="">' . esc_html__( '— Choisir une destination —', 'adaptours' ) . '</option>';
	foreach ( $destinations as $dest ) {
		$title    = get_the_title( $dest );
		$selected = ( '' !== $selected_title && $title === $selected_title ) ? ' selected="selected"' : '';
		$options .= '<option value="' . esc_attr( $title ) . '"' . $selected . '>' . esc_html( $title ) . '</option>';
	}
	$options .= '<option value="' . esc_attr__( 'Sur-mesure / je ne sais pas encore', 'adaptours' ) . '">' . esc_html__( 'Sur-mesure / je ne sais pas encore', 'adaptours' ) . '</option>';

	// Remplacer le contenu du <select> ciblé (placeholder rendu par CF7) par nos options.
	$pattern = '/(<select[^>]*name="' . preg_quote( $field, '/' ) . '"[^>]*>)(.*?)(<\/select>)/s';
	$html    = preg_replace_callback(
		$pattern,
		static function ( $matches ) use ( $options ) {
			return $matches[1] . $options . $matches[3];
		},
		$html,
		1
	);

	return $html;
}
add_filter( 'wpcf7_form_elements', 'adaptours_devis_inject_destinations', 20 );
