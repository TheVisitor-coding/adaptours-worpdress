<?php
/**
 * Prefooter — bande de conversion (composant partagé, figé en PHP).
 *
 * Affiché par footer.php sauf sur les pages devis / QSN / contact (adaptours_show_prefooter()).
 * Fond sombre, CTA en boutons. URL devis et téléphone via adaptours_get_option(), titre
 * bichrome via adaptours_bichrome().
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$devis_url   = adaptours_get_option( 'url_devis', home_url( '/devis' ) );
$tel_display = adaptours_get_option( 'tel_display' );
$tel_link    = adaptours_get_option( 'tel_link' );

// Titre bichrome : texte complet + sous-chaîne mise en accent (orange).
$title_full   = __( 'Votre prochain voyage commence par un mot.', 'adaptours' );
$title_accent = __( 'un mot.', 'adaptours' );

$trust_points = array(
	__( 'Réponse sous 48h', 'adaptours' ),
	__( 'Devis gratuit', 'adaptours' ),
	__( 'Sans engagement', 'adaptours' ),
	__( '9h–18h, lun–ven', 'adaptours' ),
);
?>
<section class="prefooter" aria-labelledby="prefooter-title">
	<div class="prefooter__inner container">
		<p class="prefooter__eyebrow"><?php esc_html_e( 'ET SI C’ÉTAIT VOTRE TOUR ?', 'adaptours' ); ?></p>

		<h2 class="prefooter__title" id="prefooter-title">
			<?php
			// Sortie déjà échappée fragment par fragment par adaptours_bichrome().
			echo adaptours_bichrome( $title_full, $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</h2>

		<p class="prefooter__desc">
			<?php esc_html_e( 'Dites-nous où vous rêvez d’aller, on s’occupe du reste. Premier échange gratuit, sans engagement. Réponse sous 48h.', 'adaptours' ); ?>
		</p>

		<div class="prefooter__actions">
			<a class="button button--primary" href="<?php echo esc_url( $devis_url ); ?>">
				<?php esc_html_e( 'Demander mon devis', 'adaptours' ); ?>
			</a>

			<?php if ( $tel_display ) : ?>
				<a class="button button--secondary" href="<?php echo $tel_link ? esc_url( 'tel:' . $tel_link ) : '#'; ?>">
					<?php
					printf(
						/* translators: %s: numéro de téléphone affiché. */
						esc_html__( 'Nous appeler · %s', 'adaptours' ),
						esc_html( $tel_display )
					);
					?>
				</a>
			<?php endif; ?>
		</div>

		<ul class="prefooter__trust">
			<?php foreach ( $trust_points as $point ) : ?>
				<li class="prefooter__trust-item">
					<span class="prefooter__trust-check" aria-hidden="true">✓</span>
					<?php echo esc_html( $point ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
