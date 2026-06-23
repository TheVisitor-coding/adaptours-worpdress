<?php
/**
 * Pied de page du site (composant partagé, non éditable).
 *
 * Porte la fin du document (wp_footer() + fermeture <body>/<html>). Affiche d'abord le
 * prefooter (sauf pages exclues, via adaptours_show_prefooter()), puis le footer : 4 colonnes
 * (marque + 2 menus + réseaux sociaux) et une barre légale. Données via adaptours_get_option()
 * et les menus footer_1 / footer_2.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( adaptours_show_prefooter() ) {
	get_template_part( 'parts/prefooter' );
}

$footer_email       = adaptours_get_option( 'email' );
$footer_tel_display = adaptours_get_option( 'tel_display' );
$footer_tel_link    = adaptours_get_option( 'tel_link' );

// Réseaux sociaux : clé d'option => [ libellé, SVG inline monochrome (currentColor) ].
$footer_socials = array(
	'instagram' => array(
		'label' => __( 'Instagram', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.4" cy="6.6" r="1.3" fill="currentColor"/></svg>',
	),
	'facebook'  => array(
		'label' => __( 'Facebook', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M13.5 21v-7h2.3l.4-2.8h-2.7V9.4c0-.81.22-1.36 1.39-1.36h1.42V5.5a19.7 19.7 0 0 0-2.27-.12c-2.25 0-3.79 1.37-3.79 3.9V11.2H8v2.8h2.25V21z"/></svg>',
	),
	'linkedin'  => array(
		'label' => __( 'LinkedIn', 'adaptours' ),
		'svg'   => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.94 5a1.94 1.94 0 1 1-3.88 0 1.94 1.94 0 0 1 3.88 0zM3.32 8.4h3.27V21H3.32zM9.2 8.4h3.13v1.72h.05c.44-.83 1.5-1.7 3.1-1.7 3.31 0 3.92 2.18 3.92 5.01V21h-3.27v-5.96c0-1.42-.03-3.25-1.98-3.25-1.98 0-2.28 1.55-2.28 3.15V21H9.2z"/></svg>',
	),
);

$legal_links = array(
	'url_cgv'              => __( 'CGV', 'adaptours' ),
	'url_mentions_legales' => __( 'Mentions légales', 'adaptours' ),
	'url_confidentialite'  => __( 'Confidentialité', 'adaptours' ),
);
?>
<footer class="site-footer">
	<div class="site-footer__inner container">
		<div class="site-footer__brand">
			<?php get_template_part( 'template-parts/site-logo', null, array( 'class' => 'site-footer__logo' ) ); ?>
			<p class="site-footer__tagline">
				<?php esc_html_e( 'Séjours accessibles, sur mesure, depuis 2011. Voyagez sans limites, où que vous soyez.', 'adaptours' ); ?>
			</p>
			<ul class="site-footer__contact">
				<?php if ( $footer_email ) : ?>
					<li>
						<a class="site-footer__email" href="<?php echo esc_url( 'mailto:' . antispambot( $footer_email ) ); ?>">
							<?php echo esc_html( $footer_email ); ?>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $footer_tel_display ) : ?>
					<li>
						<?php if ( $footer_tel_link ) : ?>
							<a class="site-footer__tel" href="<?php echo esc_url( 'tel:' . $footer_tel_link ); ?>">
								<?php echo esc_html( $footer_tel_display ); ?>
							</a>
						<?php else : ?>
							<span class="site-footer__tel"><?php echo esc_html( $footer_tel_display ); ?></span>
						<?php endif; ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<?php
		get_template_part(
			'template-parts/footer-menu-column',
			null,
			array(
				'location' => 'footer_1',
				'title'    => __( 'Destinations', 'adaptours' ),
			)
		);
		get_template_part(
			'template-parts/footer-menu-column',
			null,
			array(
				'location' => 'footer_2',
				'title'    => __( 'À propos', 'adaptours' ),
			)
		);
		?>

		<div class="site-footer__col site-footer__social">
			<h2 class="site-footer__col-title"><?php esc_html_e( 'Nous suivre', 'adaptours' ); ?></h2>
			<ul class="site-footer__social-list">
				<?php
				foreach ( $footer_socials as $key => $social ) :
					$url = adaptours_get_option( 'url_' . $key );
					if ( ! $url ) {
						continue;
					}
					?>
					<li>
						<a
							class="site-footer__social-link"
							href="<?php echo esc_url( $url ); ?>"
							aria-label="<?php echo esc_attr( $social['label'] ); ?>"
							rel="noopener noreferrer"
							target="_blank"
						>
							<?php echo wp_kses( $social['svg'], adaptours_svg_allowed_tags() ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="site-footer__legal">
		<div class="site-footer__legal-inner container">
			<p class="site-footer__copyright">
				<?php
				printf(
					/* translators: %s: année courante. */
					esc_html__( '© %s Adaptours · Tous droits réservés', 'adaptours' ),
					esc_html( wp_date( 'Y' ) )
				);
				?>
			</p>
			<?php
			$legal_items = array();
			foreach ( $legal_links as $opt_key => $label ) {
				$url = adaptours_get_option( $opt_key );
				if ( $url ) {
					$legal_items[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
				}
			}
			if ( $legal_items ) :
				?>
				<nav class="site-footer__legal-links" aria-label="<?php esc_attr_e( 'Liens légaux', 'adaptours' ); ?>">
					<?php echo wp_kses_post( implode( '<span class="site-footer__legal-sep" aria-hidden="true"> · </span>', $legal_items ) ); ?>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
