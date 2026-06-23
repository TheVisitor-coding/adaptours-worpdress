<?php
/**
 * Template Name: Devis
 *
 * Page Devis — structure figée. Les 2 blocs (hero-devis, devis-form) sont pré-insérés et
 * verrouillés (templateLock 'all') : la cliente n'édite que le contenu du hero, la structure
 * du formulaire étant définie en code (inc/devis.php). Pas de prefooter sur cette page.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'parts/header' );
?>
<main id="main" class="site-main container devis-page">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>
<?php
get_template_part( 'parts/footer' );
