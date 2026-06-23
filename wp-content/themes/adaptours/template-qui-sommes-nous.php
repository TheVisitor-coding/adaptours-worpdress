<?php
/**
 * Template Name: Qui sommes-nous
 *
 * Page éditoriale figée. Rend les 7 sections Gutenberg (templateLock 'all' via
 * adaptours_lock_map()) sans the_title() : le H1 vit dans le hero. Pas de prefooter (le
 * bloc dual-cta en tient lieu — template déjà exclu par adaptours_show_prefooter()).
 * Chaque bloc gère sa propre largeur ; pas de .container sur <main>.
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
