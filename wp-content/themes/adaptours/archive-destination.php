<?php
/**
 * Archive du CPT destination — page de listing « Destinations ».
 *
 * Template figé (aucun bloc Gutenberg) : chapô + barre de filtres + grille de cards. Rendu
 * côté serveur, filtres au rechargement de page (formulaire GET). La logique de requête est
 * dans inc/archive-destination.php, le rendu des cards dans template-parts/card-destination.php.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$chapo       = adaptours_get_destinations_chapo();
$query       = adaptours_get_destinations_query();
$archive_url = (string) get_post_type_archive_link( 'destination' );
$buckets     = adaptours_destination_budget_buckets();
$chips       = adaptours_destination_filter_chips();

// Valeurs courantes des filtres (état sélectionné / actif des contrôles).
$cur_search = isset( $_GET['recherche'] ) ? sanitize_text_field( wp_unslash( $_GET['recherche'] ) ) : '';
$cur_zone   = isset( $_GET['zone'] ) ? sanitize_title( wp_unslash( $_GET['zone'] ) ) : '';
$cur_budget = isset( $_GET['budget'] ) ? sanitize_key( wp_unslash( $_GET['budget'] ) ) : '';

$zone_terms = get_terms(
	array(
		'taxonomy'   => 'zone_geographique',
		'hide_empty' => false,
	)
);
if ( is_wp_error( $zone_terms ) ) {
	$zone_terms = array();
}

get_template_part( 'parts/header' );
?>
<main id="main" class="site-main archive-destinations">

	<header class="archive-destinations__hero">
		<span class="archive-destinations__blob archive-destinations__blob--orange" aria-hidden="true"></span>
		<span class="archive-destinations__blob archive-destinations__blob--blue" aria-hidden="true"></span>

		<div class="archive-destinations__hero-inner">
			<div class="archive-destinations__intro">
				<?php if ( '' !== $chapo['eyebrow'] ) : ?>
					<p class="eyebrow"><?php echo esc_html( $chapo['eyebrow'] ); ?></p>
				<?php endif; ?>

				<h1 class="archive-destinations__title">
					<?php
					$title_full = trim( $chapo['title_part_1'] . ' ' . $chapo['title_part_2'] );
					// Fragments déjà échappés par adaptours_bichrome().
					echo adaptours_bichrome( $title_full, $chapo['title_part_2'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</h1>
			</div>

			<div class="archive-destinations__lead">
				<?php if ( '' !== $chapo['intro'] ) : ?>
					<p class="archive-destinations__lead-text"><?php echo esc_html( $chapo['intro'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $chapo['badge'] ) : ?>
					<span class="archive-destinations__handwritten"><?php echo esc_html( $chapo['badge'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<div class="archive-destinations__body container">

		<form class="archive-destinations__filters" role="search" method="get" action="<?php echo esc_url( $archive_url ); ?>">
			<label class="archive-destinations__search">
				<span class="screen-reader-text"><?php esc_html_e( 'Rechercher une destination', 'adaptours' ); ?></span>
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
					<circle cx="11" cy="11" r="7" />
					<line x1="21" y1="21" x2="16.65" y2="16.65" />
				</svg>
				<input
					type="search"
					name="recherche"
					class="archive-destinations__search-input"
					value="<?php echo esc_attr( $cur_search ); ?>"
					placeholder="<?php esc_attr_e( 'Rechercher une destination', 'adaptours' ); ?>"
				/>
			</label>

			<label class="screen-reader-text" for="filter-zone"><?php esc_html_e( 'Continent', 'adaptours' ); ?></label>
			<select id="filter-zone" name="zone" class="archive-destinations__select<?php echo '' !== $cur_zone ? ' is-active' : ''; ?>">
				<option value=""><?php esc_html_e( 'Continent', 'adaptours' ); ?></option>
				<?php foreach ( $zone_terms as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cur_zone, $term->slug ); ?>>
						<?php echo esc_html( $term->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<label class="screen-reader-text" for="filter-budget"><?php esc_html_e( 'Budget', 'adaptours' ); ?></label>
			<select id="filter-budget" name="budget" class="archive-destinations__select<?php echo '' !== $cur_budget ? ' is-active' : ''; ?>">
				<option value=""><?php esc_html_e( 'Budget', 'adaptours' ); ?></option>
				<?php foreach ( $buckets as $slug => $bucket ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $cur_budget, $slug ); ?>>
						<?php echo esc_html( $bucket['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<button type="submit" class="archive-destinations__submit"><?php esc_html_e( 'Filtrer', 'adaptours' ); ?></button>
		</form>

		<div class="archive-destinations__status">
			<p class="archive-destinations__count">
				<?php
				printf(
					/* translators: %s = nombre de destinations (mis en valeur). */
					esc_html( _n( '%s destination correspond', '%s destinations correspondent', (int) $query->found_posts, 'adaptours' ) ),
					'<strong>' . esc_html( number_format_i18n( (int) $query->found_posts ) ) . '</strong>'
				);
				?>
			</p>

			<?php foreach ( $chips as $chip ) : ?>
				<a class="archive-destinations__chip" href="<?php echo esc_url( $chip['remove_url'] ); ?>">
					<?php echo esc_html( $chip['label'] ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '— retirer ce filtre', 'adaptours' ); ?></span>
				</a>
			<?php endforeach; ?>

			<?php if ( ! empty( $chips ) ) : ?>
				<a class="archive-destinations__clear" href="<?php echo esc_url( $archive_url ); ?>">
					<?php esc_html_e( 'Tout effacer', 'adaptours' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $query->have_posts() ) : ?>
			<ul class="archive-destinations__grid" role="list">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$card_args                  = adaptours_get_destination_card_args( get_the_ID() );
					$card_args['heading_level'] = 2; // le H1 est le titre du chapô
					?>
					<li class="archive-destinations__item">
						<?php get_template_part( 'template-parts/card-destination', null, $card_args ); ?>
					</li>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		<?php else : ?>
			<div class="archive-destinations__empty">
				<p class="archive-destinations__empty-text"><?php esc_html_e( 'Aucune destination ne correspond à votre recherche.', 'adaptours' ); ?></p>
				<a class="button button--secondary" href="<?php echo esc_url( $archive_url ); ?>">
					<?php esc_html_e( 'Tout effacer', 'adaptours' ); ?>
				</a>
			</div>
		<?php endif; ?>

	</div>
</main>
<?php
get_template_part( 'parts/footer' );
