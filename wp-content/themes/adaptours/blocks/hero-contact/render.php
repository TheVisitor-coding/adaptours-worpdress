<?php
/**
 * Bloc adaptours/hero-contact — en-tête de la page Contact.
 *
 * Titre bichrome à 3 fragments (accent au milieu) via adaptours_bichrome_parts().
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow     = (string) ( $attributes['eyebrow'] ?? '' );
$part_1      = (string) ( $attributes['title_part_1'] ?? '' );
$part_2      = (string) ( $attributes['title_part_2'] ?? '' );
$part_3      = (string) ( $attributes['title_part_3'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'hero-contact' ) );
?>
<header <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php if ( '' !== trim( $eyebrow ) ) : ?>
		<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== trim( $part_1 . $part_2 . $part_3 ) ) : ?>
		<h1 class="hero-contact__title">
			<?php echo adaptours_bichrome_parts( $part_1, $part_2, $part_3 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</h1>
	<?php endif; ?>

	<?php if ( '' !== trim( $description ) ) : ?>
		<p class="hero-contact__desc"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
</header>
