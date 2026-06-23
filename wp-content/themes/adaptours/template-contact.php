<?php
/**
 * Template Name: Contact
 *
 * Page Contact — structure figée. Les 3 blocs (hero-contact, contact-form, legal-info)
 * sont pré-insérés et verrouillés (templateLock 'all') : la cliente n'édite que leurs
 * contenus. Pas de prefooter sur cette page (voir adaptours_show_prefooter()).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'parts/header' );
?>
<main id="main" class="site-main container contact-page">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>
<?php
get_template_part( 'parts/footer' );
