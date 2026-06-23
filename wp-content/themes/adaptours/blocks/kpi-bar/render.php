<?php
/**
 * Bloc adaptours/kpi-bar — bande de chiffres clés.
 *
 * Markup sémantique <ul>/<li>, chiffres typés string (« +800 », « 100% », « 4,9/5 »),
 * séparateurs visuels gérés en CSS. Longueur figée par l'attribut `columns`.
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (non utilisé).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$columns = max( 1, (int) ( $attributes['columns'] ?? 4 ) );

// Reconstruit la liste d'items à partir des attributs plats.
$items = array();
for ( $i = 1; $i <= $columns; $i++ ) {
	$value = trim( (string) ( $attributes[ "kpi_{$i}_value" ] ?? '' ) );
	$label = trim( (string) ( $attributes[ "kpi_{$i}_label" ] ?? '' ) );
	if ( '' !== $value || '' !== $label ) {
		$items[] = array(
			'value' => $value,
			'label' => $label,
		);
	}
}

if ( empty( $items ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'      => 'is-cols-' . $columns,
		'aria-label' => esc_attr__( 'Chiffres clés', 'adaptours' ),
		'style'      => '--adaptours-kpi-cols:' . $columns,
	)
);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<ul class="wp-block-adaptours-kpi-bar__list">
		<?php foreach ( $items as $item ) : ?>
			<li class="wp-block-adaptours-kpi-bar__item">
				<?php if ( '' !== $item['value'] ) : ?>
					<span class="wp-block-adaptours-kpi-bar__value"><?php echo esc_html( $item['value'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $item['label'] ) : ?>
					<span class="wp-block-adaptours-kpi-bar__label"><?php echo esc_html( $item['label'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
