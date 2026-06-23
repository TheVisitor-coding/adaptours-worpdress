<?php
/**
 * Bloc adaptours/contact-form — coordonnées + formulaire de contact.
 *
 * Colonne gauche : coordonnées lues depuis les réglages. Colonne droite : surtitre + titre
 * éditables + formulaire Contact Form 7 (via adaptours_get_contact_form_id()). Repli stylé
 * si CF7 est absent ou le formulaire non créé.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_eyebrow = (string) ( $attributes['form_eyebrow'] ?? '' );
$form_p1      = (string) ( $attributes['form_title_part_1'] ?? '' );
$form_p2      = (string) ( $attributes['form_title_part_2'] ?? '' );

$adresse     = (string) adaptours_get_option( 'adresse' );
$tel_display = (string) adaptours_get_option( 'tel_display' );
$tel_link    = (string) adaptours_get_option( 'tel_link' );
$tel_hours   = (string) adaptours_get_option( 'tel_horaires' );
$email       = (string) adaptours_get_option( 'email' );
$email_delai = (string) adaptours_get_option( 'email_delai' );

$form_id = function_exists( 'adaptours_get_contact_form_id' ) ? adaptours_get_contact_form_id() : 0;

$wrapper = get_block_wrapper_attributes( array( 'class' => 'contact-form' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="contact-form__inner">

		<div class="contact-form__coords">
			<?php if ( '' !== trim( $adresse ) ) : ?>
				<div class="contact-form__coord">
					<p class="contact-form__coord-label"><?php esc_html_e( 'Adresse', 'adaptours' ); ?></p>
					<p class="contact-form__coord-value"><?php echo nl2br( esc_html( $adresse ) ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( '' !== trim( $tel_display ) ) : ?>
				<div class="contact-form__coord">
					<p class="contact-form__coord-label"><?php esc_html_e( 'Par téléphone', 'adaptours' ); ?></p>
					<p class="contact-form__coord-value">
						<?php if ( '' !== trim( $tel_link ) ) : ?>
							<a class="contact-form__coord-link" href="<?php echo esc_url( 'tel:' . $tel_link ); ?>"><?php echo esc_html( $tel_display ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $tel_display ); ?>
						<?php endif; ?>
					</p>
					<?php if ( '' !== trim( $tel_hours ) ) : ?>
						<p class="contact-form__coord-note"><?php echo esc_html( $tel_hours ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( '' !== trim( $email ) ) : ?>
				<div class="contact-form__coord">
					<p class="contact-form__coord-label"><?php esc_html_e( 'Par e-mail', 'adaptours' ); ?></p>
					<p class="contact-form__coord-value">
						<a class="contact-form__coord-link" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</p>
					<?php if ( '' !== trim( $email_delai ) ) : ?>
						<p class="contact-form__coord-note"><?php echo esc_html( $email_delai ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="contact-form__main">
			<?php if ( '' !== trim( $form_eyebrow ) ) : ?>
				<p class="contact-form__eyebrow"><?php echo esc_html( $form_eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== trim( $form_p1 . $form_p2 ) ) : ?>
				<h2 class="contact-form__title">
					<?php echo adaptours_bichrome_parts( $form_p1, $form_p2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</h2>
			<?php endif; ?>

			<div class="contact-form__form">
				<?php if ( $form_id > 0 && shortcode_exists( 'contact-form-7' ) ) : ?>
					<?php echo do_shortcode( '[contact-form-7 id="' . (int) $form_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php else : ?>
					<p class="contact-form__fallback"><?php esc_html_e( 'Le formulaire de contact sera disponible très prochainement. En attendant, écrivez-nous directement par e-mail.', 'adaptours' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

	</div>
</section>
