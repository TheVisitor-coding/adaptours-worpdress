<?php
/**
 * Bandeau d'annonce, premier enfant du header.
 *
 * Contenu piloté depuis « Coordonnées & liens ». Ne rend rien si le bandeau est
 * inactif : c'est ce qui garde la géométrie du header inchangée hors annonce.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$announcement = adaptours_get_announcement();

if ( empty( $announcement ) ) {
	return;
}
?>
<div class="site-announcement site-header__announcement">
	<p class="site-announcement__inner">
		<span class="site-announcement__message"><?php echo esc_html( $announcement['message'] ); ?></span>

		<?php if ( '' !== $announcement['link_url'] ) : ?>
			<a class="site-announcement__link" href="<?php echo esc_url( $announcement['link_url'] ); ?>">
				<?php echo esc_html( $announcement['link_label'] ); ?>
			</a>
		<?php endif; ?>
	</p>
</div>
