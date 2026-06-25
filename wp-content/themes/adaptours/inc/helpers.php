<?php
/**
 * Utilitaires partagés, appelés depuis les templates et les render.php des blocs.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bichromie : enveloppe un segment d'accent dans <span class="accent">.
 *
 * Sortie échappée fragment par fragment (esc_html) ; le <span> est ajouté par le code,
 * jamais par l'entrée. Seule la première occurrence de $accent est mise en accent ;
 * si $accent est vide ou absent, le texte est simplement échappé.
 *
 * @param string $text   Texte complet.
 * @param string $accent Sous-chaîne à mettre en accent (doit exister dans $text).
 * @return string HTML sûr (déjà échappé), à imprimer directement.
 */
function adaptours_bichrome( $text, $accent = '' ) {
	$text   = (string) $text;
	$accent = (string) $accent;

	if ( '' === $accent ) {
		return esc_html( $text );
	}

	$pos = mb_strpos( $text, $accent );
	if ( false === $pos ) {
		return esc_html( $text );
	}

	$before = mb_substr( $text, 0, $pos );
	$match  = mb_substr( $text, $pos, mb_strlen( $accent ) );
	$after  = mb_substr( $text, $pos + mb_strlen( $accent ) );

	return esc_html( $before )
		. '<span class="accent">' . esc_html( $match ) . '</span>'
		. esc_html( $after );
}

/**
 * Titre bichrome en 3 fragments explicites.
 *
 * Variante d'adaptours_bichrome() quand l'accent est porté par un champ dédié et peut se
 * trouver au milieu du titre. $part_3 vide ⇒ accent en fin de titre. Fragments joints par
 * une espace.
 *
 * @param string $part_1 Texte avant l'accent.
 * @param string $part_2 Texte mis en accent (orange).
 * @param string $part_3 Texte après l'accent (optionnel).
 * @return string HTML sûr (déjà échappé), à imprimer directement.
 */
function adaptours_bichrome_parts( $part_1, $part_2, $part_3 = '' ) {
	$pieces = array();

	if ( '' !== trim( (string) $part_1 ) ) {
		$pieces[] = esc_html( $part_1 );
	}
	if ( '' !== trim( (string) $part_2 ) ) {
		$pieces[] = '<span class="accent">' . esc_html( $part_2 ) . '</span>';
	}
	if ( '' !== trim( (string) $part_3 ) ) {
		$pieces[] = esc_html( $part_3 );
	}

	return implode( ' ', $pieces );
}

/**
 * Liste blanche de balises/attributs SVG pour wp_kses().
 *
 * Permet d'échapper sûrement les SVG inline (icônes monochromes en currentColor)
 * tout en autorisant les éléments de tracé nécessaires. Réutilisable par les
 * partials et les render.php des blocs.
 *
 * @return array<string,array>
 */
function adaptours_svg_allowed_tags() {
	$shared = array(
		'fill'            => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
	);

	return array(
		'svg'      => array_merge(
			$shared,
			array(
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'class'       => true,
				'aria-hidden' => true,
				'focusable'   => true,
				'role'        => true,
				'xmlns'       => true,
			)
		),
		'path'     => array_merge( $shared, array( 'd' => true, 'fill-rule' => true, 'clip-rule' => true ) ),
		'rect'     => array_merge( $shared, array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true ) ),
		'circle'   => array_merge( $shared, array( 'cx' => true, 'cy' => true, 'r' => true ) ),
		'ellipse'  => array_merge( $shared, array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ) ),
		'line'     => array_merge( $shared, array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ) ),
		'polyline' => array_merge( $shared, array( 'points' => true ) ),
		'polygon'  => array_merge( $shared, array( 'points' => true ) ),
		'g'        => $shared,
	);
}

/**
 * Rend l'icône SVG inline d'un slug de la liste fermée (inc/icons.php).
 *
 * Charge le fichier `assets/icons/{slug}.svg` (jeu monochrome `currentColor`) et le
 * renvoie échappé via wp_kses() + la liste blanche adaptours_svg_allowed_tags(). Le
 * fichier est statique et fourni par le thème ; l'échappement reste appliqué par
 * principe. Retourne '' si le slug est inconnu ou le fichier absent (dégradation).
 *
 * @param string $slug Slug d'icône (voir adaptours_get_icons()).
 * @return string SVG inline échappé, ou '' si introuvable.
 */
function adaptours_icon_svg( $slug ) {
	$path = adaptours_icon_path( (string) $slug );
	if ( '' === $path || ! is_readable( $path ) ) {
		return '';
	}
	$svg = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	return wp_kses( $svg, adaptours_svg_allowed_tags() );
}

/**
 * Rend le drapeau SVG inline d'une langue (sélecteur de langue du header).
 *
 * @param string $flag_code Code drapeau Polylang (sera assaini en [a-z]{2,3}).
 * @return string SVG inline échappé, ou '' si introuvable.
 */
function adaptours_flag_svg( $flag_code ) {
	$code = strtolower( preg_replace( '/[^a-z]/i', '', (string) $flag_code ) );
	if ( '' === $code ) {
		return '';
	}
	$path = ADAPTOURS_DIR . '/assets/flags/' . $code . '.svg';
	if ( ! is_readable( $path ) ) {
		return '';
	}
	$svg = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	return wp_kses( $svg, adaptours_svg_allowed_tags() );
}

/**
 * Lit une méta de destination (ACF si actif, sinon post meta brute).
 *
 * Les champs ACF sont stockés en post meta sous leur `name` : la lecture via
 * get_post_meta() reste valable même si ACF Free n'est pas chargé (ex. recette).
 *
 * @param int    $post_id ID de la destination.
 * @param string $key     Nom du champ (ville, duree, prix_a_partir_de, coup_de_coeur).
 * @return mixed Valeur brute du champ (string|int|bool selon le champ).
 */
function adaptours_get_destination_meta( $post_id, $key ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $key, $post_id );
	}
	return get_post_meta( (int) $post_id, $key, true );
}

/**
 * Formate un prix « à partir de » en chaîne d'affichage (ex. « 2 490€ »).
 *
 * `prix_a_partir_de` est stocké en nombre (il sert au filtre budget) ; le formatage
 * d'affichage est fait ici, séparateur de milliers selon la locale.
 *
 * @param mixed $prix Montant numérique brut (0/'' => chaîne vide).
 * @return string Prix formaté avec « € », ou '' si absent.
 */
function adaptours_format_price( $prix ) {
	if ( '' === $prix || null === $prix || (float) $prix <= 0 ) {
		return '';
	}
	return number_format_i18n( (float) $prix, 0 ) . '€';
}

/**
 * Description courte d'une card destination, tirée de l'extrait du post.
 *
 * @param int $post_id ID de la destination.
 * @return string Extrait tronqué (texte brut), ou '' si vide.
 */
function adaptours_get_destination_excerpt( $post_id ) {
	$excerpt = get_the_excerpt( (int) $post_id );
	if ( '' === trim( (string) $excerpt ) ) {
		return '';
	}
	return wp_trim_words( $excerpt, 24, '…' );
}

/**
 * Tableau d'arguments normalisé d'une card destination (source unique des consommateurs).
 *
 * Lit le post, ses métas, l'image à la une et les zones, et renvoie le contrat attendu par
 * template-parts/card-destination.php, qui reste agnostique. Le niveau de titre est
 * fusionné par le consommateur à l'appel (le partial le défaute à 3).
 *
 * @param int $post_id ID de la destination.
 * @return array Tableau normalisé (voir card-destination.php pour le contrat complet).
 */
function adaptours_get_destination_card_args( $post_id ) {
	$post_id = (int) $post_id;

	$zones = wp_get_post_terms( $post_id, 'zone_geographique', array( 'fields' => 'names' ) );
	if ( is_wp_error( $zones ) ) {
		$zones = array();
	}

	return array(
		'titre'         => get_the_title( $post_id ),
		'permalink'     => (string) get_permalink( $post_id ),
		'image_id'      => (int) get_post_thumbnail_id( $post_id ),
		'ville'         => (string) adaptours_get_destination_meta( $post_id, 'ville' ),
		'duree'         => (string) adaptours_get_destination_meta( $post_id, 'duree' ),
		'prix'          => adaptours_format_price( adaptours_get_destination_meta( $post_id, 'prix_a_partir_de' ) ),
		'description'   => adaptours_get_destination_excerpt( $post_id ),
		'zones'         => $zones,
		'coup_de_coeur' => (bool) adaptours_get_destination_meta( $post_id, 'coup_de_coeur' ),
	);
}

/**
 * Données du hero figé du single destination.
 *
 * Tableau normalisé consommé par single-destination.php, qui reste agnostique (ne lit ni
 * le post global ni get_field() directement).
 *
 * @param int $post_id ID de la destination.
 * @return array{titre:string,permalink:string,image_id:int,ville:string,coordonnees:string,accroche_manuscrite:string,hero_accroche:string}
 */
function adaptours_get_destination_hero_args( $post_id ) {
	$post_id = (int) $post_id;

	return array(
		'titre'               => get_the_title( $post_id ),
		'permalink'           => (string) get_permalink( $post_id ),
		'image_id'            => (int) get_post_thumbnail_id( $post_id ),
		'ville'               => (string) adaptours_get_destination_meta( $post_id, 'ville' ),
		'coordonnees'         => (string) adaptours_get_destination_meta( $post_id, 'coordonnees' ),
		'accroche_manuscrite' => (string) adaptours_get_destination_meta( $post_id, 'accroche_manuscrite' ),
		'hero_accroche'       => (string) adaptours_get_destination_meta( $post_id, 'hero_accroche' ),
	);
}

/**
 * Cellules de la bande méta figée du single destination.
 *
 * Renvoie une liste ordonnée de cellules { eyebrow, value, sub } déjà formatées et filtrée
 * des cellules vides, plus le CTA devis.
 *
 * @param int $post_id ID de la destination.
 * @return array{cells:array<int,array{eyebrow:string,value:string,sub:string}>,cta_url:string,cta_label:string,prix:string,prix_sub:string}
 */
function adaptours_get_destination_meta_band( $post_id ) {
	$post_id = (int) $post_id;

	$duree           = (string) adaptours_get_destination_meta( $post_id, 'duree' );
	$nuits           = adaptours_get_destination_meta( $post_id, 'nuits_sur_place' );
	$periode         = (string) adaptours_get_destination_meta( $post_id, 'periode_ideale' );
	$saison          = (string) adaptours_get_destination_meta( $post_id, 'saison_label' );
	$voyageurs_min   = adaptours_get_destination_meta( $post_id, 'voyageurs_min' );
	$voyageurs_max   = adaptours_get_destination_meta( $post_id, 'voyageurs_max' );
	$temps_vol       = (string) adaptours_get_destination_meta( $post_id, 'temps_vol' );

	// Sous-libellé « N nuits sur place » (masqué si non renseigné).
	$nuits_sub = '';
	if ( '' !== (string) $nuits && (int) $nuits > 0 ) {
		/* translators: %d: nombre de nuits sur place. */
		$nuits_sub = sprintf( _n( '%d nuit sur place', '%d nuits sur place', (int) $nuits, 'adaptours' ), (int) $nuits );
	}

	// Plage de voyageurs « min → max » (ou une seule borne si l'autre manque).
	$voyageurs_value = '';
	if ( '' !== (string) $voyageurs_min && '' !== (string) $voyageurs_max ) {
		$voyageurs_value = (int) $voyageurs_min . ' → ' . (int) $voyageurs_max;
	} elseif ( '' !== (string) $voyageurs_min ) {
		$voyageurs_value = (string) (int) $voyageurs_min;
	} elseif ( '' !== (string) $voyageurs_max ) {
		$voyageurs_value = (string) (int) $voyageurs_max;
	}

	$cells = array(
		array(
			'eyebrow' => __( 'Durée', 'adaptours' ),
			'value'   => $duree,
			'sub'     => $nuits_sub,
		),
		array(
			'eyebrow' => __( 'Période idéale', 'adaptours' ),
			'value'   => $periode,
			'sub'     => $saison,
		),
		array(
			'eyebrow' => __( 'Voyageurs', 'adaptours' ),
			'value'   => $voyageurs_value,
			'sub'     => '' !== $voyageurs_value ? __( 'en petit comité', 'adaptours' ) : '',
		),
		array(
			'eyebrow' => __( 'Temps de vol moyen', 'adaptours' ),
			'value'   => $temps_vol,
			'sub'     => '',
		),
	);

	// Ne garder que les cellules ayant une valeur (évite une colonne vide).
	$cells = array_values(
		array_filter(
			$cells,
			static function ( $cell ) {
				return '' !== trim( (string) $cell['value'] );
			}
		)
	);

	// CTA devis pré-rempli via le paramètre `dest` (et non `destination`, qui est la query
	// var du CPT et provoquerait une collision de requête).
	$devis_url = adaptours_get_option( 'url_devis', home_url( '/devis' ) );
	$slug      = get_post_field( 'post_name', $post_id );
	if ( '' !== (string) $slug ) {
		$devis_url = add_query_arg( 'dest', $slug, $devis_url );
	}

	return array(
		'cells'     => $cells,
		'prix'      => adaptours_format_price( adaptours_get_destination_meta( $post_id, 'prix_a_partir_de' ) ),
		'prix_sub'  => __( '/ personne', 'adaptours' ),
		'cta_url'   => $devis_url,
		'cta_label' => __( 'Demander un devis', 'adaptours' ),
	);
}

/**
 * Données du bloc « Carte du voyage ».
 *
 * Tableau normalisé consommé par blocks/section-map/render.php : image de la carte,
 * distance auto-formatée depuis `distance_km`, et bande d'infos (décalage horaire, visa,
 * monnaie, langue) déjà filtrée des cellules vides.
 *
 * @param int $post_id ID de la destination.
 * @return array{image_id:int,distance_auto:string,cells:array<int,array{label:string,value:string}>}
 */
function adaptours_get_destination_map_args( $post_id ) {
	$post_id = (int) $post_id;

	$decalage      = (string) adaptours_get_destination_meta( $post_id, 'decalage_horaire' );
	$visa          = (string) adaptours_get_destination_meta( $post_id, 'visa' );
	$monnaie_label = (string) adaptours_get_destination_meta( $post_id, 'monnaie_label' );
	$monnaie_code  = (string) adaptours_get_destination_meta( $post_id, 'monnaie_code' );
	$langues       = (string) adaptours_get_destination_meta( $post_id, 'langues_locales' );
	$distance_km   = adaptours_get_destination_meta( $post_id, 'distance_km' );

	// Monnaie : « libellé code » (ex. « Rupiah IDR ») en encre uniforme.
	$monnaie = trim( $monnaie_label . ' ' . $monnaie_code );

	// Distance « à vol d'oiseau » formatée depuis les km (masquée si non renseignée).
	$distance_auto = '';
	if ( '' !== (string) $distance_km && (float) $distance_km > 0 ) {
		$distance_auto = '→ ' . number_format_i18n( (float) $distance_km, 0 ) . ' ' . __( 'km à vol d’oiseau', 'adaptours' );
	}

	$cells = array(
		array(
			'label' => __( 'Décalage horaire', 'adaptours' ),
			'value' => $decalage,
		),
		array(
			'label' => __( 'Visa', 'adaptours' ),
			'value' => $visa,
		),
		array(
			'label' => __( 'Monnaie', 'adaptours' ),
			'value' => $monnaie,
		),
		array(
			'label' => __( 'Langue', 'adaptours' ),
			'value' => $langues,
		),
	);

	// Ne garder que les cellules renseignées (évite une colonne vide).
	$cells = array_values(
		array_filter(
			$cells,
			static function ( $cell ) {
				return '' !== trim( (string) $cell['value'] );
			}
		)
	);

	return array(
		'image_id'      => (int) adaptours_get_destination_meta( $post_id, 'carte_image' ),
		'distance_auto' => $distance_auto,
		'cells'         => $cells,
	);
}

/**
 * Données du bloc « Accessibilité ».
 *
 * Tableau normalisé consommé par blocks/section-accessibility/render.php : introduction
 * + jusqu'à 4 cartes (icône + titre + description) lues dans des champs plats du CPT
 * (« repeater » sans ACF Pro, longueur figée à 4). Les cartes sans titre ni description
 * sont filtrées.
 *
 * @param int $post_id ID de la destination.
 * @return array{intro:string,cards:array<int,array{icon:string,title:string,description:string}>}
 */
function adaptours_get_destination_accessibility_args( $post_id ) {
	$post_id = (int) $post_id;

	$cards = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$icon  = (string) adaptours_get_destination_meta( $post_id, "accessibility_card_{$i}_icon" );
		$title = (string) adaptours_get_destination_meta( $post_id, "accessibility_card_{$i}_title" );
		$desc  = (string) adaptours_get_destination_meta( $post_id, "accessibility_card_{$i}_description" );

		if ( '' !== trim( $title ) || '' !== trim( $desc ) ) {
			$cards[] = array(
				'icon'        => $icon,
				'title'       => $title,
				'description' => $desc,
			);
		}
	}

	return array(
		'intro' => (string) adaptours_get_destination_meta( $post_id, 'accessibility_intro' ),
		'cards' => $cards,
	);
}

/**
 * Items de la galerie d'une destination.
 *
 * Lit le post meta `_adaptours_gallery_ids` (métabox native, ordre conservé) et renvoie
 * une liste normalisée pour blocs/destination-gallery : URL pleine taille (lightbox), URL
 * d'aperçu (tuile), légende, texte alternatif et badge « jour » optionnel.
 *
 * @param int $post_id ID de la destination.
 * @return array<int,array{id:int,full:string,thumb:string,caption:string,alt:string,jour:string}>
 */
function adaptours_get_destination_gallery_items( $post_id ) {
	$post_id = (int) $post_id;

	$ids = get_post_meta( $post_id, '_adaptours_gallery_ids', true );
	$ids = is_array( $ids ) ? array_values( array_filter( array_map( 'absint', $ids ) ) ) : array();
	if ( empty( $ids ) ) {
		return array();
	}

	$items = array();
	foreach ( $ids as $id ) {
		$full = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $full ) {
			continue; // Pièce jointe supprimée.
		}

		// Badge « jour » : ACF si actif, sinon post meta brute (champ sur la pièce jointe).
		$jour = function_exists( 'get_field' ) ? get_field( 'adaptours_jour', $id ) : get_post_meta( $id, 'adaptours_jour', true );

		$items[] = array(
			'id'      => (int) $id,
			'full'    => (string) $full,
			'thumb'   => (string) ( wp_get_attachment_image_url( $id, 'large' ) ?: $full ),
			'caption' => (string) wp_get_attachment_caption( $id ),
			'alt'     => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			'jour'    => (string) $jour,
		);
	}

	return $items;
}

/**
 * Destinations suggérées d'une destination.
 *
 * Lit la relation ACF `suggestions` (max 4), traduit les IDs vers la langue courante,
 * exclut la destination courante et ne garde que les destinations publiées. Pas
 * d'auto-complétion : seules les destinations choisies sont rendues.
 *
 * @param int $post_id ID de la destination courante.
 * @return int[] IDs de destinations publiées (≤ 4).
 */
function adaptours_get_destination_suggestions( $post_id ) {
	$post_id = (int) $post_id;

	$ids = adaptours_get_destination_meta( $post_id, 'suggestions' );
	if ( ! is_array( $ids ) ) {
		return array();
	}

	$out = array();
	foreach ( $ids as $id ) {
		$id = (int) $id;
		if ( function_exists( 'pll_get_post' ) ) {
			$translated = pll_get_post( $id );
			if ( $translated ) {
				$id = (int) $translated;
			}
		}
		if ( $id <= 0 || $id === $post_id ) {
			continue;
		}
		if ( 'destination' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
			$out[] = $id;
		}
		if ( count( $out ) >= 4 ) {
			break;
		}
	}

	return $out;
}

/**
 * Formate un mois de voyage (champ `mois_voyage`, stocké « Ym ») en « mm/aaaa ».
 *
 * @param mixed $ym Valeur brute (« 202601 » => « 01/2026 » ; vide/invalide => '').
 * @return string Mois formaté, ou '' si absent/invalide.
 */
function adaptours_format_mois( $ym ) {
	$ym = trim( (string) $ym );
	if ( ! preg_match( '/^(\d{4})(\d{2})$/', $ym, $m ) ) {
		return '';
	}
	return $m[2] . '/' . $m[1];
}

/**
 * Tableau d'arguments normalisé d'une card avis (source unique).
 *
 * Lit les champs ACF du CPT `avis` et l'éventuelle destination liée, et renvoie le contrat
 * attendu par template-parts/card-avis.php, qui reste agnostique. La destination liée est
 * traduite vers la langue courante si Polylang est actif.
 *
 * @param int $post_id ID de l'avis.
 * @return array Tableau normalisé (voir card-avis.php pour le contrat complet).
 */
function adaptours_get_avis_card_args( $post_id ) {
	$post_id = (int) $post_id;

	// Destination liée → nom de pays du badge (traduite si Polylang est actif).
	$dest_id = (int) adaptours_get_destination_meta( $post_id, 'destination_liee' );
	if ( $dest_id > 0 && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $dest_id );
		if ( $translated ) {
			$dest_id = (int) $translated;
		}
	}
	$destination_label = $dest_id > 0 ? (string) get_the_title( $dest_id ) : '';

	return array(
		'titre_admin'       => get_the_title( $post_id ),
		'note'              => (int) adaptours_get_destination_meta( $post_id, 'note' ),
		'temoignage'        => (string) adaptours_get_destination_meta( $post_id, 'temoignage' ),
		'nom'               => (string) adaptours_get_destination_meta( $post_id, 'nom_voyageur' ),
		'contexte'          => (string) adaptours_get_destination_meta( $post_id, 'contexte' ),
		'photo_id'          => (int) adaptours_get_destination_meta( $post_id, 'photo_voyageur' ),
		'destination_label' => $destination_label,
		'mois_label'        => adaptours_format_mois( adaptours_get_destination_meta( $post_id, 'mois_voyage' ) ),
	);
}

/**
 * Markup accessible des étoiles d'une note (1..5), partagé card-avis / avis-spotlight.
 *
 * Rend 5 glyphes (pleines + vides) dans un conteneur `role="img"` + `aria-label`
 * explicite ; les glyphes décoratifs sont masqués aux lecteurs d'écran.
 *
 * @param int    $note  Note brute (bornée 1..5).
 * @param string $class Classe CSS du conteneur (BEM scopé par le consommateur).
 * @return string HTML échappé.
 */
function adaptours_avis_stars_markup( $note, $class = '' ) {
	$note  = max( 1, min( 5, (int) $note ) );
	$stars = str_repeat( '★', $note ) . str_repeat( '☆', 5 - $note );
	/* translators: %d: note de 1 à 5. */
	$label = sprintf( __( 'Note : %d sur 5', 'adaptours' ), $note );

	return sprintf(
		'<p class="%1$s" role="img" aria-label="%2$s"><span aria-hidden="true">%3$s</span></p>',
		esc_attr( $class ),
		esc_attr( $label ),
		esc_html( $stars )
	);
}

/**
 * Résout l'avis à afficher dans avis-spotlight pour une destination.
 *
 * Sélection manuelle via le champ `avis_mis_en_avant` du CPT destination (1 max), sans
 * repli automatique. L'ID est traduit vers la langue courante si Polylang est actif.
 *
 * @param int $destination_id ID de la destination courante.
 * @return int ID d'un avis publié, ou 0 (champ vide / avis dépublié => bloc masqué).
 */
function adaptours_get_spotlight_avis_id( $destination_id ) {
	$destination_id = (int) $destination_id;
	if ( $destination_id <= 0 ) {
		return 0;
	}

	$avis_id = (int) adaptours_get_destination_meta( $destination_id, 'avis_mis_en_avant' );
	if ( $avis_id <= 0 ) {
		return 0;
	}

	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $avis_id );
		if ( $translated ) {
			$avis_id = (int) $translated;
		}
	}

	if ( 'avis' !== get_post_type( $avis_id ) || 'publish' !== get_post_status( $avis_id ) ) {
		return 0;
	}

	return $avis_id;
}

/**
 * Indique si le prefooter doit s'afficher sur la requête courante.
 *
 * Retourne false sur les pages Devis, Qui sommes-nous et Contact, où la bande de
 * conversion n'a pas de sens.
 *
 * @return bool
 */
function adaptours_show_prefooter() {
	$blacklist = array(
		'template-devis.php',
		'template-qui-sommes-nous.php',
		'template-contact.php',
	);

	return ! is_page_template( $blacklist );
}
