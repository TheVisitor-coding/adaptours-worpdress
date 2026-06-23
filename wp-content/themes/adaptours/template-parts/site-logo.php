<?php
/**
 * Logo du site, en lien vers la home de la langue courante.
 *
 * Partial partagé entre header et footer. En l'absence de SVG de logo, on affiche le
 * nom du site (wordmark) ; un SVG fourni plus tard le remplacera sans toucher aux appelants.
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
?>
<a class="<?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $home_url ); ?>" rel="home">
	<span class="site-logo__text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
</a>
