<?php
/**
 * En-tête du site (composant partagé, non éditable).
 *
 * Porte le début du document (doctype, <head> + wp_head(), ouverture du <body>) et le
 * header sticky. Sur le single destination, `.has-transparent-header` (posée sur <body>)
 * rend le header transparent au-dessus du hero, repassé en `.site-header--solid` au scroll
 * par assets/js/header.js. Menu via wp_nav_menu(primary), liens via adaptours_get_option().
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$devis_url = adaptours_get_option( 'url_devis', home_url( '/devis' ) );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Aller au contenu', 'adaptours' ); ?></a>

<header class="site-header">
	<div class="site-header__inner container">
		<?php get_template_part( 'template-parts/site-logo', null, array( 'class' => 'site-header__logo' ) ); ?>

		<button
			class="site-header__burger"
			type="button"
			aria-expanded="false"
			aria-controls="site-primary-nav"
			aria-label="<?php esc_attr_e( 'Menu', 'adaptours' ); ?>"
		>
			<span class="site-header__burger-icon" aria-hidden="true"></span>
		</button>

		<div class="site-header__panel" id="site-primary-nav">
			<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Menu principal', 'adaptours' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'site-header__menu',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>

			<div class="site-header__actions">
				<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
					<div class="site-header__lang">
						<?php
						pll_the_languages(
							array(
								'dropdown'   => 0,
								'show_flags' => 1,
								'show_names' => 1,
							)
						);
						?>
					</div>
				<?php endif; ?>

				<a class="button button--primary site-header__cta" href="<?php echo esc_url( $devis_url ); ?>">
					<?php esc_html_e( 'Demander un devis', 'adaptours' ); ?>
				</a>
			</div>
		</div>
	</div>
</header>
