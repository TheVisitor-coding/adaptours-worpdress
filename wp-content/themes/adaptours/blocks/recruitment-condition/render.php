<?php
/**
 * Bloc adaptours/recruitment-condition — une condition de recrutement.
 *
 * Numéro (compteur CSS, décoratif), titre, explication. Rendu en <li> dans la liste du
 * bloc parent adaptours/recruitment (pas de style propre).
 *
 * @var array    $attributes Attributs du bloc (title, description).
 * @var string   $content    Contenu interne (non utilisé).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = (string) ( $attributes['title'] ?? '' );
$description = (string) ( $attributes['description'] ?? '' );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'recruitment__condition' ) );
?>
<li <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<span class="recruitment__num" aria-hidden="true"></span>
	<div class="recruitment__cond-body">
		<?php if ( '' !== trim( $title ) ) : ?>
			<p class="recruitment__cond-title"><?php echo esc_html( $title ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== trim( $description ) ) : ?>
			<p class="recruitment__cond-desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
</li>
