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
 * Langues à provisionner pour les formulaires CF7 : slug => locale, langue par défaut d'abord.
 *
 * Tableau vide si Polylang est absent : un seul formulaire est alors créé dans la langue du site,
 * sans liaison de traduction. Partagé avec inc/devis.php.
 *
 * @return array<string,string>
 */
function adaptours_cf7_languages() {
	if ( ! function_exists( 'PLL' ) || ! PLL() || ! isset( PLL()->model ) ) {
		return array();
	}

	$default = function_exists( 'pll_default_language' ) ? pll_default_language() : '';
	$langs   = array();
	foreach ( PLL()->model->get_languages_list() as $lang ) {
		$langs[ $lang->slug ] = $lang->locale;
	}

	if ( $default && isset( $langs[ $default ] ) ) {
		$langs = array( $default => $langs[ $default ] ) + $langs;
	}

	return $langs;
}

/**
 * Option stockant l'ID du formulaire pour une langue.
 *
 * La langue par défaut conserve l'option historique (sans suffixe) pour compatibilité.
 *
 * @param string $base_option Option de la langue par défaut (ex. `adaptours_contact_form_id`).
 * @param string $slug        Slug de langue.
 * @param string $default     Slug de la langue par défaut.
 * @return string
 */
function adaptours_cf7_lang_option( $base_option, $slug, $default ) {
	return ( $slug === $default ) ? $base_option : $base_option . '_' . $slug;
}

/**
 * Crée/lie les formulaires CF7 d'une famille (un par langue Polylang), idempotent.
 *
 * Partagé par Contact et Devis : seuls l'option de base et la fabrique de formulaire diffèrent.
 * Sans Polylang, un seul formulaire est créé dans la langue du site (sans liaison).
 *
 * @param string   $base_option Option de la langue par défaut (ex. adaptours_contact_form_id).
 * @param callable $upsert      fn( string $option, string $locale ): int — crée le form, renvoie son ID.
 */
function adaptours_cf7_ensure_translated_forms( $base_option, $upsert ) {
	$langs   = adaptours_cf7_languages();
	$default = function_exists( 'pll_default_language' ) ? pll_default_language() : '';

	if ( empty( $langs ) ) {
		$upsert( $base_option, '' );
		return;
	}

	$ids     = array();
	$created = false;
	foreach ( $langs as $slug => $locale ) {
		$option   = adaptours_cf7_lang_option( $base_option, $slug, $default );
		$existing = (int) get_option( $option, 0 );
		$id       = $upsert( $option, $locale );
		if ( $id ) {
			$ids[ $slug ] = $id;
			pll_set_post_language( $id, $slug );
			if ( $id !== $existing ) {
				$created = true;
			}
		}
	}

	if ( $created && count( $ids ) > 1 && function_exists( 'pll_save_post_translations' ) ) {
		pll_save_post_translations( $ids );
	}
}

/**
 * Crée les formulaires de contact (un par langue) et les lie comme traductions (idempotent).
 *
 * Chaque formulaire est construit dans sa locale (switch_to_locale) pour que les balises et
 * mails reprennent les chaînes traduites du thème.
 */
function adaptours_cf7_ensure_contact_form() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return;
	}

	$email_to    = (string) adaptours_get_option( 'email', get_option( 'admin_email' ) );
	$privacy_url = (string) adaptours_get_option( 'url_confidentialite', '#' );

	adaptours_cf7_ensure_translated_forms(
		'adaptours_contact_form_id',
		static fn( $option, $locale ) => adaptours_cf7_upsert_contact( $option, $locale, $email_to, $privacy_url )
	);
}
add_action( 'admin_init', 'adaptours_cf7_ensure_contact_form' );

/**
 * Crée le formulaire de contact d'une langue s'il n'existe pas, et renvoie son ID.
 *
 * Idempotent : si l'option pointe déjà vers un formulaire valide, la construction est sautée.
 *
 * @param string $option      Option mémorisant l'ID.
 * @param string $locale      Locale de construction (vide = locale courante).
 * @param string $email_to    Destinataire des e-mails.
 * @param string $privacy_url URL de la politique de confidentialité.
 * @return int
 */
function adaptours_cf7_upsert_contact( $option, $locale, $email_to, $privacy_url ) {
	$existing = (int) get_option( $option, 0 );
	if ( $existing > 0 && 'wpcf7_contact_form' === get_post_type( $existing ) ) {
		return $existing;
	}

	if ( $locale ) {
		switch_to_locale( $locale );
	}

	$props = adaptours_cf7_contact_properties( $email_to, $privacy_url );
	$form  = WPCF7_ContactForm::get_template( array( 'title' => $props['title'] ) );
	$form->set_properties(
		array(
			'form'     => $props['form'],
			'mail'     => $props['mail'],
			'mail_2'   => $props['mail_2'],
			'messages' => $props['messages'],
		)
	);
	$id = (int) $form->save();

	if ( $locale ) {
		restore_previous_locale();
	}

	if ( $id ) {
		update_option( $option, $id );
	}

	return $id;
}

/**
 * Propriétés du formulaire de contact dans la locale courante.
 *
 * @param string $email_to    Destinataire des e-mails.
 * @param string $privacy_url URL de la politique de confidentialité.
 * @return array{title:string,form:string,mail:array,mail_2:array,messages:array}
 */
function adaptours_cf7_contact_properties( $email_to, $privacy_url ) {
	$privacy_link = '<a href="' . esc_url( $privacy_url ) . '">' . esc_html__( 'En savoir plus', 'adaptours' ) . '</a>';

	// Classes BEM contact-form__* pour le style scopé du bloc.
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

	return array(
		'title'    => __( 'Contact (FR)', 'adaptours' ),
		'form'     => $form_body,
		'mail'     => $mail,
		'mail_2'   => $mail_2,
		'messages' => $messages,
	);
}

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
