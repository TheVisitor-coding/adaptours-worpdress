<?php
/**
 * Bloc adaptours/devis-form — formulaire de demande de devis.
 *
 * Sans attribut : le contenu (structure, champs, mails) est piloté par la configuration
 * CF7 côté admin (form FR créé par code dans inc/devis.php). Ce bloc est un simple wrapper
 * qui rend le shortcode CF7 de la langue courante.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_id = function_exists( 'adaptours_get_devis_form_id' ) ? adaptours_get_devis_form_id() : 0;

$wrapper = get_block_wrapper_attributes( array( 'class' => 'devis-form' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="devis-form__inner">
		<?php if ( $form_id > 0 && shortcode_exists( 'contact-form-7' ) ) : ?>
			<?php echo do_shortcode( '[contact-form-7 id="' . (int) $form_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php else : ?>
			<p class="devis-form__fallback"><?php esc_html_e( 'Le formulaire de devis sera disponible très prochainement. En attendant, écrivez-nous directement par e-mail.', 'adaptours' ); ?></p>
		<?php endif; ?>
	</div>
</section>
