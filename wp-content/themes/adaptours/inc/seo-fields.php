<?php
/**
 * Champs SEO éditables en backoffice (title + meta description).
 *
 * Complète inc/seo.php, qui dérive les métas du contenu : ici la cliente peut *surcharger*
 * le title et la meta description, contexte par contexte. Une valeur vide = comportement
 * automatique inchangé (aucune régression possible).
 *
 * Couverture de tous les contextes indexables :
 *   - pages (tous templates : modulaire, contact, devis, QSN, légales) et home ..... post meta
 *   - fiches destination ........................................................... post meta
 *   - articles de blog ............................................................. post meta
 *   - archive /destinations/ ................................. options (chaînes Polylang)
 *   - archives /zone/{terme}/ ...................................................... term meta
 * Les contextes sans surface d'édition (recherche, 404, archives auteur/catégorie) n'ont
 * volontairement pas de champ : ils relèvent d'un noindex, pas d'une rédaction.
 *
 * Multilingue : les post metas et term metas sont portées par le contenu, donc **une par
 * langue** nativement (une traduction Polylang est un post distinct, un terme traduit est un
 * terme distinct). Les deux clés ne doivent jamais rejoindre adaptours_pll_copy_post_metas()
 * (inc/polylang.php) sous peine de recopier le texte FR sur les traductions.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ADAPTOURS_SEO_TITLE_META = '_adaptours_seo_title';
const ADAPTOURS_SEO_DESC_META  = '_adaptours_seo_desc';
const ADAPTOURS_SEO_NONCE      = 'adaptours_seo_nonce';

/**
 * Longueurs recommandées, partagées entre l'aide des champs et le compteur.
 */
const ADAPTOURS_SEO_TITLE_MAX = 60;
const ADAPTOURS_SEO_DESC_MAX  = 160;

/**
 * Types de contenu portant les champs SEO.
 *
 * @return string[]
 */
function adaptours_seo_field_post_types() {
	return array( 'page', 'post', 'destination' );
}

/**
 * Lit une option de la page d'options en appliquant la traduction Polylang.
 *
 * Alias historique de adaptours_get_option_i18n() (inc/options.php), conservé pour les
 * appels du module SEO.
 *
 * @param string $key Clé d'option.
 * @return string
 */
function adaptours_seo_option_i18n( $key ) {
	return adaptours_get_option_i18n( $key );
}

/**
 * Surcharge SEO saisie en backoffice pour le contexte affiché.
 *
 * @param string $field 'title' ou 'desc'.
 * @return string Chaîne saisie, ou '' si aucune surcharge pour ce contexte.
 */
function adaptours_seo_override( $field ) {
	$is_title = ( 'title' === $field );
	$meta_key = $is_title ? ADAPTOURS_SEO_TITLE_META : ADAPTOURS_SEO_DESC_META;

	// Pages (dont la home, page_on_front et ses traductions), destinations, articles.
	if ( is_singular() ) {
		$id = get_queried_object_id();
		if ( $id && in_array( (string) get_post_type( $id ), adaptours_seo_field_post_types(), true ) ) {
			return trim( (string) get_post_meta( $id, $meta_key, true ) );
		}
		return '';
	}

	// Archive des destinations : pas de post support, on passe par la page d'options.
	if ( is_post_type_archive( 'destination' ) ) {
		return trim( adaptours_seo_option_i18n( $is_title ? 'dest_seo_title' : 'dest_seo_desc' ) );
	}

	// Archives de zone géographique : term meta (une par terme, donc une par langue).
	if ( is_tax( 'zone_geographique' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return trim( (string) get_term_meta( $term->term_id, $meta_key, true ) );
		}
	}

	return '';
}

/**
 * Déclare les métas (sanitisation + autorisation centralisées).
 *
 * show_in_rest reste à false : la saisie passe par une métabox classique, exposer ces clés
 * dans l'API n'apporterait rien et élargirait la surface d'écriture.
 */
function adaptours_seo_register_meta() {
	foreach ( adaptours_seo_field_post_types() as $post_type ) {
		foreach ( array( ADAPTOURS_SEO_TITLE_META, ADAPTOURS_SEO_DESC_META ) as $key ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => '',
					'show_in_rest'      => false,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}
}
add_action( 'init', 'adaptours_seo_register_meta' );

/*
|--------------------------------------------------------------------------
| Métabox (pages, articles, destinations)
|--------------------------------------------------------------------------
*/

/**
 * Déclare la métabox SEO sur les écrans d'édition concernés.
 */
function adaptours_seo_add_metabox() {
	add_meta_box(
		'adaptours_seo',
		__( 'Référencement (Google)', 'adaptours' ),
		'adaptours_seo_render_metabox',
		adaptours_seo_field_post_types(),
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'adaptours_seo_add_metabox' );

/**
 * Affiche les deux champs, en explicitant la valeur automatique appliquée si on laisse vide.
 *
 * @param WP_Post $post Post courant.
 */
function adaptours_seo_render_metabox( $post ) {
	wp_nonce_field( 'adaptours_seo_save', ADAPTOURS_SEO_NONCE );

	$title = (string) get_post_meta( $post->ID, ADAPTOURS_SEO_TITLE_META, true );
	$desc  = (string) get_post_meta( $post->ID, ADAPTOURS_SEO_DESC_META, true );

	// Titre automatique tel que le produit add_theme_support('title-tag').
	$sep           = apply_filters( 'document_title_separator', '-' );
	$auto_title    = trim( get_the_title( $post ) . ' ' . $sep . ' ' . get_bloginfo( 'name' ) );
	$is_front_page = (int) get_option( 'page_on_front' ) === (int) $post->ID
		|| ( function_exists( 'adaptours_is_front_page_in_any_language' )
			&& adaptours_is_front_page_in_any_language( $post->ID ) );
	if ( $is_front_page ) {
		$auto_title = trim( get_bloginfo( 'name' ) . ' ' . $sep . ' ' . get_bloginfo( 'description' ) );
	}
	?>
	<div class="adaptours-seo" data-adaptours-seo>
		<p class="description" style="margin-bottom:1em">
			<?php esc_html_e( 'Ce que Google affiche dans ses résultats. Laissez vide pour utiliser la valeur automatique indiquée sous chaque champ.', 'adaptours' ); ?>
		</p>

		<p>
			<label for="adaptours_seo_title" style="display:block;font-weight:600">
				<?php esc_html_e( 'Titre dans Google', 'adaptours' ); ?>
			</label>
			<input
				type="text"
				id="adaptours_seo_title"
				name="adaptours_seo_title"
				class="large-text"
				data-seo-input
				data-seo-max="<?php echo esc_attr( ADAPTOURS_SEO_TITLE_MAX ); ?>"
				value="<?php echo esc_attr( $title ); ?>"
			/>
			<span class="description" data-seo-counter></span>
			<span class="description" style="display:block">
				<?php
				printf(
					/* translators: %1$d : longueur recommandée, %2$s : titre automatique. */
					esc_html__( '%1$d caractères maximum. Si vide : « %2$s ».', 'adaptours' ),
					(int) ADAPTOURS_SEO_TITLE_MAX,
					esc_html( $auto_title )
				);
				?>
			</span>
		</p>

		<p>
			<label for="adaptours_seo_desc" style="display:block;font-weight:600">
				<?php esc_html_e( 'Description dans Google', 'adaptours' ); ?>
			</label>
			<textarea
				id="adaptours_seo_desc"
				name="adaptours_seo_desc"
				class="large-text"
				rows="3"
				data-seo-input
				data-seo-max="<?php echo esc_attr( ADAPTOURS_SEO_DESC_MAX ); ?>"
			><?php echo esc_textarea( $desc ); ?></textarea>
			<span class="description" data-seo-counter></span>
			<span class="description" style="display:block">
				<?php
				printf(
					/* translators: %d : longueur recommandée. */
					esc_html__( '%d caractères maximum. Deux phrases décrivant la page, avec les mots que taperait un visiteur.', 'adaptours' ),
					(int) ADAPTOURS_SEO_DESC_MAX
				);
				?>
			</span>
		</p>
	</div>
	<?php
}

/**
 * Compteur de caractères des champs SEO (aide à la rédaction, aucune contrainte imposée).
 *
 * @param string $hook Hook de la page admin courante.
 */
function adaptours_seo_enqueue( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( (string) $screen->post_type, adaptours_seo_field_post_types(), true ) ) {
		return;
	}

	$js = <<<'JS'
( function() {
	var root = document.querySelector( '[data-adaptours-seo]' );
	if ( ! root ) {
		return;
	}

	Array.prototype.forEach.call( root.querySelectorAll( '[data-seo-input]' ), function( input ) {
		var max     = parseInt( input.getAttribute( 'data-seo-max' ), 10 ) || 0;
		var counter = input.parentNode.querySelector( '[data-seo-counter]' );
		if ( ! counter ) {
			return;
		}

		function update() {
			var len = input.value.length;
			counter.textContent = len + ' / ' + max;
			counter.style.color = ( max && len > max ) ? '#d63638' : '';
		}

		input.addEventListener( 'input', update );
		update();
	} );
}() );
JS;

	wp_add_inline_script( 'wp-util', $js );
}
add_action( 'admin_enqueue_scripts', 'adaptours_seo_enqueue' );

/**
 * Enregistre les champs SEO à la sauvegarde du contenu.
 *
 * @param int $post_id ID du post sauvegardé.
 */
function adaptours_seo_save_post( $post_id ) {
	if ( ! isset( $_POST[ ADAPTOURS_SEO_NONCE ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST[ ADAPTOURS_SEO_NONCE ] ), 'adaptours_seo_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$map = array(
		'adaptours_seo_title' => ADAPTOURS_SEO_TITLE_META,
		'adaptours_seo_desc'  => ADAPTOURS_SEO_DESC_META,
	);

	foreach ( $map as $input => $meta_key ) {
		$raw   = isset( $_POST[ $input ] ) ? wp_unslash( $_POST[ $input ] ) : '';
		$value = sanitize_text_field( (string) $raw );

		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}
		update_post_meta( $post_id, $meta_key, $value );
	}
}
foreach ( adaptours_seo_field_post_types() as $adaptours_seo_pt ) {
	add_action( 'save_post_' . $adaptours_seo_pt, 'adaptours_seo_save_post' );
}
unset( $adaptours_seo_pt );

/*
|--------------------------------------------------------------------------
| Champs de terme (archives /zone/{terme}/)
|--------------------------------------------------------------------------
*/

/**
 * Champs SEO sur le formulaire d'édition d'une zone géographique.
 *
 * @param WP_Term $term Terme édité.
 */
function adaptours_seo_term_edit_fields( $term ) {
	$title = (string) get_term_meta( $term->term_id, ADAPTOURS_SEO_TITLE_META, true );
	$desc  = (string) get_term_meta( $term->term_id, ADAPTOURS_SEO_DESC_META, true );
	wp_nonce_field( 'adaptours_seo_term_save', ADAPTOURS_SEO_NONCE );
	?>
	<tr class="form-field">
		<th scope="row"><label for="adaptours_seo_title"><?php esc_html_e( 'Titre dans Google', 'adaptours' ); ?></label></th>
		<td>
			<input type="text" id="adaptours_seo_title" name="adaptours_seo_title" value="<?php echo esc_attr( $title ); ?>" class="regular-text" />
			<p class="description">
				<?php
				printf(
					/* translators: %d : longueur recommandée. */
					esc_html__( '%d caractères maximum. Laissez vide pour utiliser le nom de la zone.', 'adaptours' ),
					(int) ADAPTOURS_SEO_TITLE_MAX
				);
				?>
			</p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="adaptours_seo_desc"><?php esc_html_e( 'Description dans Google', 'adaptours' ); ?></label></th>
		<td>
			<textarea id="adaptours_seo_desc" name="adaptours_seo_desc" rows="3" class="large-text"><?php echo esc_textarea( $desc ); ?></textarea>
			<p class="description">
				<?php
				printf(
					/* translators: %d : longueur recommandée. */
					esc_html__( '%d caractères maximum.', 'adaptours' ),
					(int) ADAPTOURS_SEO_DESC_MAX
				);
				?>
			</p>
		</td>
	</tr>
	<?php
}
add_action( 'zone_geographique_edit_form_fields', 'adaptours_seo_term_edit_fields' );

/**
 * Enregistre les champs SEO d'une zone géographique.
 *
 * @param int $term_id ID du terme.
 */
function adaptours_seo_save_term( $term_id ) {
	if ( ! isset( $_POST[ ADAPTOURS_SEO_NONCE ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST[ ADAPTOURS_SEO_NONCE ] ), 'adaptours_seo_term_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}

	$map = array(
		'adaptours_seo_title' => ADAPTOURS_SEO_TITLE_META,
		'adaptours_seo_desc'  => ADAPTOURS_SEO_DESC_META,
	);

	foreach ( $map as $input => $meta_key ) {
		$raw   = isset( $_POST[ $input ] ) ? wp_unslash( $_POST[ $input ] ) : '';
		$value = sanitize_text_field( (string) $raw );

		if ( '' === $value ) {
			delete_term_meta( $term_id, $meta_key );
			continue;
		}
		update_term_meta( $term_id, $meta_key, $value );
	}
}
add_action( 'edited_zone_geographique', 'adaptours_seo_save_term' );
