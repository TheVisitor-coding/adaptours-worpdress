<?php
/**
 * Intégration Contact Form 7 : formulaire de contact créé par code + anti-spam.
 *
 * Le formulaire FR est créé de façon idempotente (la cliente n'a rien à configurer) et son
 * ID mémorisé dans l'option `adaptours_contact_form_id`. Un honeypot rejette silencieusement
 * les soumissions de robots. Tout est gardé par la présence de CF7 : aucune erreur si le
 * plugin est absent.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slug interne du honeypot (champ caché). S'il est rempli => spam.
 */
const ADAPTOURS_CF7_HONEYPOT = 'adaptours-hp';

/**
 * ID du formulaire de contact FR (0 si non encore créé / CF7 absent).
 *
 * Prêt pour le multilingue : quand les formulaires EN/ES existeront, on renverra
 * l'équivalent traduit via pll_get_post().
 *
 * @return int
 */
function adaptours_get_contact_form_id() {
	$id = (int) get_option( 'adaptours_contact_form_id', 0 );

	if ( $id > 0 && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id );
		if ( $translated ) {
			$id = (int) $translated;
		}
	}

	return $id;
}

/**
 * Crée le formulaire de contact FR s'il n'existe pas encore (idempotent).
 *
 * Déclenché en admin (coût = un get_option + vérification d'existence).
 */
function adaptours_cf7_ensure_contact_form() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return;
	}

	$existing = (int) get_option( 'adaptours_contact_form_id', 0 );
	if ( $existing > 0 && 'wpcf7_contact_form' === get_post_type( $existing ) ) {
		return;
	}

	$email_to     = (string) adaptours_get_option( 'email', get_option( 'admin_email' ) );
	$privacy_url  = (string) adaptours_get_option( 'url_confidentialite', '#' );
	$privacy_link = '<a href="' . esc_url( $privacy_url ) . '">' . esc_html__( 'En savoir plus', 'adaptours' ) . '</a>';

	// Corps du formulaire (balises CF7). Classes BEM pour le style scopé du bloc.
	$form_body = '
<div class="contact-form__grid">
	<p class="contact-form__field">
		<label>' . __( 'Nom et prénom', 'adaptours' ) . '<br />
			[text* contact-nom placeholder "' . __( 'Caroline Martin', 'adaptours' ) . '"]</label>
	</p>
	<p class="contact-form__field">
		<label>' . __( 'E-mail', 'adaptours' ) . '<br />
			[email* contact-email placeholder "' . __( 'caroline@exemple.fr', 'adaptours' ) . '"]</label>
	</p>
</div>
<p class="contact-form__field">
	<label>' . __( 'Sujet', 'adaptours' ) . '<br />
		[select* contact-sujet "' . __( 'Une question générale', 'adaptours' ) . '" "' . __( 'Demande de devis', 'adaptours' ) . '" "' . __( 'Partenariat', 'adaptours' ) . '" "' . __( 'Autre', 'adaptours' ) . '"]</label>
</p>
<p class="contact-form__field">
	<label>' . __( 'Message', 'adaptours' ) . '<br />
		[textarea* contact-message rows 4 placeholder "' . __( 'Dites-nous tout en quelques lignes…', 'adaptours' ) . '"]</label>
</p>
<span class="contact-form__hp" aria-hidden="true">[text ' . ADAPTOURS_CF7_HONEYPOT . ']</span>
<p class="contact-form__consent">[acceptance contact-rgpd] ' . __( 'J’accepte que mes informations soient utilisées pour me répondre.', 'adaptours' ) . ' ' . $privacy_link . ' [/acceptance]</p>
<p class="contact-form__submit">[submit "' . __( 'Envoyer', 'adaptours' ) . '"]</p>
';

	$mail_body = __( 'Sujet : [contact-sujet]', 'adaptours' ) . "\n"
		. __( 'Nom : [contact-nom]', 'adaptours' ) . "\n"
		. __( 'E-mail : [contact-email]', 'adaptours' ) . "\n\n"
		. __( 'Message :', 'adaptours' ) . "\n[contact-message]\n";

	$mail = array(
		'subject'            => __( '[Contact] [contact-sujet] — [contact-nom]', 'adaptours' ),
		'sender'             => '[_site_title] <' . $email_to . '>',
		'recipient'          => $email_to,
		'body'               => $mail_body,
		'additional_headers' => 'Reply-To: [contact-email]',
		'attachments'        => '',
		'use_html'           => 0,
		'exclude_blank'      => 0,
	);

	$mail_2_body = __( 'Bonjour [contact-nom],', 'adaptours' ) . "\n\n"
		. __( 'Merci de nous avoir écrit. Nous avons bien reçu votre message et vous répondrons personnellement, généralement dans la journée.', 'adaptours' ) . "\n\n"
		. __( 'À très vite,', 'adaptours' ) . "\n" . __( 'L’équipe Adaptours', 'adaptours' ) . "\n";

	$mail_2 = array(
		'active'             => true,
		'subject'            => __( 'Nous avons bien reçu votre message — Adaptours', 'adaptours' ),
		'sender'             => '[_site_title] <' . $email_to . '>',
		'recipient'          => '[contact-email]',
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

	$form = WPCF7_ContactForm::get_template(
		array( 'title' => __( 'Contact (FR)', 'adaptours' ) )
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
		update_option( 'adaptours_contact_form_id', (int) $id );
	}
}
add_action( 'admin_init', 'adaptours_cf7_ensure_contact_form' );

/**
 * Honeypot : si le champ caché est rempli, la soumission est un robot → spam.
 *
 * @param bool                 $spam       État spam courant.
 * @param WPCF7_Submission|null $submission Soumission (non utilisée, signature CF7).
 * @return bool
 */
function adaptours_cf7_honeypot_spam( $spam, $submission = null ) {
	if ( $spam ) {
		return $spam;
	}
	$hp = isset( $_POST[ ADAPTOURS_CF7_HONEYPOT ] ) ? trim( (string) wp_unslash( $_POST[ ADAPTOURS_CF7_HONEYPOT ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- lecture honeypot, CF7 gère son nonce.
	return '' !== $hp;
}
add_filter( 'wpcf7_spam', 'adaptours_cf7_honeypot_spam', 10, 2 );
