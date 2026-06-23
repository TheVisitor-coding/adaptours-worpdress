<?php
/**
 * Bloc adaptours/avis-grid — grille de témoignages.
 *
 * Compose : header (eyebrow + titre bichrome + description + note Google), grille (avis du
 * mois en grande card sombre + 2 derniers avis en mini-cards), bande CTA. Les cards passent
 * par le partial source unique template-parts/card-avis.php.
 *
 * Avis du mois : on privilégie le picker éditeur `featured_avis_id`, sinon le dernier avis
 * publié marqué `is_featured`. La note Google est saisie manuellement dans les options.
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

$eyebrow      = (string) ( $attributes['eyebrow'] ?? '' );
$title        = (string) ( $attributes['title'] ?? '' );
$title_accent = (string) ( $attributes['title_accent'] ?? '' );
$description  = (string) ( $attributes['description'] ?? '' );
$featured_id  = (int) ( $attributes['featured_avis_id'] ?? 0 );
$band_text    = (string) ( $attributes['band_text'] ?? '' );
$cta_label    = (string) ( $attributes['cta_label'] ?? '' );
$cta_url      = (string) ( $attributes['cta_url'] ?? '' );
if ( '' === trim( $cta_url ) ) {
	$cta_url = (string) adaptours_get_option( 'url_devis' );
}

// --- Avis du mois : picker explicite, sinon dernier is_featured publié. ------
$featured_post = 0;
if ( $featured_id > 0 && 'avis' === get_post_type( $featured_id ) && 'publish' === get_post_status( $featured_id ) ) {
	$featured_post = $featured_id;
} else {
	$fq = new WP_Query(
		array(
			'post_type'      => 'avis',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => 'is_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $fq->posts ) ) {
		$featured_post = (int) $fq->posts[0];
	}
}

// --- Mini-cards : 2 derniers avis publiés hors featured. ---------------------
$mq = new WP_Query(
	array(
		'post_type'           => 'avis',
		'post_status'         => 'publish',
		'posts_per_page'      => 2,
		'post__not_in'        => $featured_post ? array( $featured_post ) : array(),
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
		'fields'              => 'ids',
		'ignore_sticky_posts' => true,
	)
);
$mini_ids = array_map( 'intval', (array) $mq->posts );

// Rien à afficher si aucun avis.
if ( ! $featured_post && empty( $mini_ids ) ) {
	return;
}

// --- Badge note Google (fallback manuel via options). ------------------------
$g_rating = (string) adaptours_get_option( 'google_rating' );
$g_count  = (string) adaptours_get_option( 'google_review_count' );

$wrapper = get_block_wrapper_attributes(
	array(
		'aria-label' => esc_attr__( 'Avis clients', 'adaptours' ),
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region">
	<span class="avis-grid__decor" aria-hidden="true"></span>

	<div class="avis-grid__inner">
		<header class="avis-grid__head">
			<div class="avis-grid__intro">
				<?php if ( '' !== trim( $eyebrow ) ) : ?>
					<p class="avis-grid__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $title ) ) : ?>
					<h2 class="avis-grid__title"><?php echo adaptours_bichrome( $title, $title_accent ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
			</div>

			<div class="avis-grid__aside">
				<?php if ( '' !== trim( $description ) ) : ?>
					<p class="avis-grid__desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== trim( $g_rating ) ) : ?>
					<div class="avis-grid__rating">
						<span class="avis-grid__rating-stars" aria-hidden="true">★★★★★</span>
						<span class="avis-grid__rating-divider" aria-hidden="true"></span>
						<span class="avis-grid__rating-score">
							<span class="avis-grid__rating-value"><?php echo esc_html( str_replace( '.', ',', $g_rating ) ); ?></span><span class="avis-grid__rating-max">/5</span>
						</span>
						<?php if ( '' !== trim( $g_count ) ) : ?>
							<span class="avis-grid__rating-divider" aria-hidden="true"></span>
							<span class="avis-grid__rating-count">
								<?php
								printf(
									/* translators: %s: nombre d'avis Google. */
									esc_html__( '%s avis', 'adaptours' ),
									esc_html( number_format_i18n( (float) $g_count ) )
								);
								?>
								<span class="avis-grid__rating-source">Google</span>
							</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</header>

		<div class="avis-grid__grid">
			<?php if ( $featured_post ) : ?>
				<div class="avis-grid__featured">
					<?php
					$fargs                  = adaptours_get_avis_card_args( $featured_post );
					$fargs['variant']       = 'featured';
					$fargs['heading_level'] = 3;
					get_template_part( 'template-parts/card-avis', null, $fargs );
					?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $mini_ids ) ) : ?>
				<div class="avis-grid__minis">
					<?php
					foreach ( $mini_ids as $mid ) {
						$margs                  = adaptours_get_avis_card_args( $mid );
						$margs['variant']       = 'mini';
						$margs['heading_level'] = 3;
						get_template_part( 'template-parts/card-avis', null, $margs );
					}
					?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( '' !== trim( $band_text ) || ( '' !== trim( $cta_label ) && '' !== trim( $cta_url ) ) ) : ?>
			<div class="avis-grid__band">
				<div class="avis-grid__band-intro">
					<span class="avis-grid__avatars" aria-hidden="true">
						<span class="avis-grid__avatar"></span>
						<span class="avis-grid__avatar"></span>
						<span class="avis-grid__avatar"></span>
						<span class="avis-grid__avatar"></span>
					</span>
					<?php if ( '' !== trim( $band_text ) ) : ?>
						<p class="avis-grid__band-text"><?php echo wp_kses_post( $band_text ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( '' !== trim( $cta_label ) && '' !== trim( $cta_url ) ) : ?>
					<a class="button button--primary avis-grid__cta" href="<?php echo esc_url( $cta_url ); ?>">
						<?php echo esc_html( $cta_label ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
