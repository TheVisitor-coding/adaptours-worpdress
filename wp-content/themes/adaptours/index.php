<?php
/**
 * Template de secours générique (hiérarchie WordPress).
 *
 * Le début et la fin du document vivent dans parts/header.php et parts/footer.php,
 * chargés via get_template_part() (et non get_header()/get_footer(), ces fichiers étant
 * rangés sous parts/).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'parts/header' );
?>
<main id="main" class="site-main container">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_title( '<h1>', '</h1>' );
			the_content();
		}
	} else {
		echo '<p>' . esc_html__( 'Aucun contenu pour le moment.', 'adaptours' ) . '</p>';
	}
	?>
</main>
<?php
get_template_part( 'parts/footer' );

