<?php
/**
 * Template de la page d'accueil statique.
 *
 * Rend le contenu Gutenberg (8 blocs figés, templateLock 'all' via adaptours_lock_map())
 * sans the_title() : le H1 vit dans le hero. Header/footer via parts/.
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
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	}
	?>
</main>
<?php
get_template_part( 'parts/footer' );
