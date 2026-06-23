<?php
/**
 * Template Name: Page de contenu riche
 *
 * Page modulaire générique : zone Gutenberg 100 % libre, palette restreinte aux blocs
 * adaptours/* (+ paragraphe/sous-titre/liste natifs pour le corps de « Texte enrichi »).
 * La composition, l'ordre et le contenu sont édités par la cliente (templateLock false,
 * démarrage avec un bloc « En-tête de page » — voir adaptours_lock_map()). Prefooter
 * affiché par défaut.
 *
 * Pas de .container sur <main> : chaque bloc gère sa largeur (sections full-bleed en
 * 100vw, ou conteneur 1280px interne).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'parts/header' );
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>
<?php
get_template_part( 'parts/footer' );
