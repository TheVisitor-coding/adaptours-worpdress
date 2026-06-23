<?php
/**
 * Bloc adaptours/legal-info — bandeau des mentions légales.
 *
 * Aucun attribut : valeurs lues depuis la page de réglages (groupe « Informations légales »),
 * les mêmes options que le footer (saisie unique).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields = array(
	__( 'Forme juridique', 'adaptours' )        => 'legal_forme_juridique',
	__( 'SIRET', 'adaptours' )                  => 'legal_siret',
	__( 'NAF', 'adaptours' )                     => 'legal_naf',
	__( 'RCS', 'adaptours' )                     => 'legal_rcs',
	__( 'TVA intracommunautaire', 'adaptours' )  => 'legal_tva',
	__( 'Atout France', 'adaptours' )            => 'legal_atout_france',
);

$cells = array();
foreach ( $fields as $label => $key ) {
	$value = (string) adaptours_get_option( $key );
	if ( '' !== trim( $value ) ) {
		$cells[] = array( $label, $value );
	}
}

$apst = (string) adaptours_get_option( 'legal_apst' );

if ( empty( $cells ) && '' === trim( $apst ) ) {
	return; // rien à afficher tant que les réglages ne sont pas saisis
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'legal-info' ) );
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-label="<?php esc_attr_e( 'Informations légales', 'adaptours' ); ?>">
	<p class="legal-info__eyebrow"><?php esc_html_e( 'Informations légales', 'adaptours' ); ?></p>

	<?php if ( ! empty( $cells ) ) : ?>
		<dl class="legal-info__grid">
			<?php foreach ( $cells as $cell ) : ?>
				<div class="legal-info__cell">
					<dt class="legal-info__label"><?php echo esc_html( $cell[0] ); ?></dt>
					<dd class="legal-info__value"><?php echo esc_html( $cell[1] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	<?php endif; ?>

	<?php if ( '' !== trim( $apst ) ) : ?>
		<p class="legal-info__apst"><?php echo esc_html( $apst ); ?></p>
	<?php endif; ?>
</section>
