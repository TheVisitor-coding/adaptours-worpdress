<?php
/**
 * Logo du site, en lien vers la home de la langue courante.
 *
 * Partial partagé entre header et footer. Affiche l'image de logo si elle est présente,
 * sinon un repli sur le nom du site (wordmark texte).
 *
 * @param array $args {
 *     @type string $class Classe(s) additionnelle(s) sur le lien.
 * }
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array( 'class' => '' )
);

// Home de la langue courante (Polylang) si dispo, sinon home WP.
$home_url = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );

$classes = trim( 'site-logo ' . $args['class'] );

$logo_rel = 'assets/icons/logo.png';
$has_logo = file_exists( get_theme_file_path( $logo_rel ) );
?>
<a class="<?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $home_url ); ?>" rel="home">
	<?php if ( $has_logo ) : ?>
		<img class="site-logo__img" src="<?php echo esc_url( get_theme_file_uri( $logo_rel ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="86" height="86" decoding="async">
	<?php else : ?>
		<span class="site-logo__text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
	<?php endif; ?>
</a>
