<?php
/**
 * Corrections de contenu en base suite aux retours cliente (2026-07-28).
 *
 * Quatre corrections, toutes idempotentes :
 *  1. kpi-bar de la home (toutes langues) : la colonne « ans d'expérience » passe à 19,
 *     et les labels au JSON corrompu (littéraux uXXXX hérités d'un ancien seed) sont réparés ;
 *  2. kpi-bar de Qui sommes-nous (toutes langues) : 5 colonnes remplacées par les valeurs
 *     validées (+800 voyageurs · 3 personnes · 30 destinations · 100% sur mesure · 19 ans) ;
 *  3. team-grid de Qui sommes-nous : pré-rempli avec les 3 membres réels s'il est vide
 *     ou s'il contient encore les membres fictifs de recette ;
 *  4. formulaires CF7 Devis FR/EN : « devis sous 48 h » devient « premier échange sous
 *     48 h » (mail de confirmation + message de succès).
 *
 * À exécuter via WP-CLI, chemin ABSOLU obligatoire (wp eval-file résout relativement au CWD) :
 *   wp eval-file "$HOME/preprod/wp-content/themes/adaptours/tools/fix-contenus-cliente.php"
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$adaptours_langs = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : array();

$adaptours_with_translations = function ( $id ) use ( $adaptours_langs ) {
	$ids = array( (int) $id );
	if ( function_exists( 'pll_get_post' ) ) {
		foreach ( $adaptours_langs as $lang ) {
			$t = pll_get_post( (int) $id, $lang );
			if ( $t ) {
				$ids[] = (int) $t;
			}
		}
	}
	return array_values( array_unique( array_filter( $ids ) ) );
};

$adaptours_walk = function ( array $blocks, callable $visit ) use ( &$adaptours_walk ) {
	foreach ( $blocks as &$block ) {
		$visit( $block );
		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = $adaptours_walk( $block['innerBlocks'], $visit );
		}
	}
	unset( $block );
	return $blocks;
};

$adaptours_patch_post = function ( $post_id, callable $visit, $label ) use ( $adaptours_walk ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		echo "  SKIP {$label} : page #{$post_id} introuvable\n";
		return;
	}
	$blocks = $adaptours_walk( parse_blocks( $post->post_content ), $visit );
	$new    = serialize_blocks( $blocks );
	if ( $new === $post->post_content ) {
		echo "  {$label} (#{$post_id}) : inchangé\n";
		return;
	}
	$res = wp_update_post( wp_slash( array( 'ID' => $post_id, 'post_content' => $new ) ), true );
	if ( is_wp_error( $res ) ) {
		echo "  ERREUR {$label} (#{$post_id}) : " . $res->get_error_message() . "\n";
		return;
	}
	echo "  {$label} (#{$post_id}) : contenu corrigé\n";
};

// Décode les séquences uXXXX ayant perdu leur backslash (wp_unslash sur un JSON \uXXXX).
$adaptours_decode_broken_unicode = function ( $s ) {
	return preg_replace_callback(
		'/u([0-9a-fA-F]{4})/',
		function ( $m ) {
			$c = json_decode( '"\u' . $m[1] . '"' );
			return is_string( $c ) ? $c : $m[0];
		},
		$s
	);
};

// 1. Home : « ans d'expérience » → 19 (+ réparation des labels corrompus).
echo "— kpi-bar home —\n";
$adaptours_home_id = (int) get_option( 'page_on_front' );
if ( $adaptours_home_id && 'page' === get_option( 'show_on_front' ) ) {
	$adaptours_home_visit = function ( &$block ) use ( $adaptours_decode_broken_unicode ) {
		if ( 'adaptours/kpi-bar' !== ( $block['blockName'] ?? '' ) ) {
			return;
		}
		foreach ( $block['attrs'] as $key => $val ) {
			if ( preg_match( '/^kpi_\d+_label$/', $key ) && is_string( $val ) && preg_match( '/u[0-9a-fA-F]{4}/', $val ) ) {
				$block['attrs'][ $key ] = $adaptours_decode_broken_unicode( $val );
			}
		}
		for ( $i = 1; $i <= 5; $i++ ) {
			$adaptours_kpi_label = $block['attrs'][ "kpi_{$i}_label" ] ?? '';
			if ( is_string( $adaptours_kpi_label ) && false !== mb_stripos( $adaptours_kpi_label, 'expérience' ) ) {
				$block['attrs'][ "kpi_{$i}_value" ] = '19';
			}
		}
	};
	foreach ( $adaptours_with_translations( $adaptours_home_id ) as $id ) {
		$adaptours_patch_post( $id, $adaptours_home_visit, 'home' );
	}
} else {
	echo "  SKIP : aucune page d'accueil statique\n";
}

// 2 + 3. Qui sommes-nous : kpi-bar aux valeurs validées + team-grid réel.
echo "— Qui sommes-nous —\n";
$adaptours_kpi_qsn = array(
	'kpi_1_value' => '+800',
	'kpi_1_label' => 'voyageurs accompagnés',
	'kpi_2_value' => '3',
	'kpi_2_label' => 'personnes dans l’équipe',
	'kpi_3_value' => '30',
	'kpi_3_label' => 'destinations testées',
	'kpi_4_value' => '100%',
	'kpi_4_label' => 'sur mesure',
	'kpi_5_value' => '19',
	'kpi_5_label' => 'ans à dire oui',
);
$adaptours_team = array(
	array( 'name' => 'Caroline', 'role' => 'Dirigeante' ),
	array( 'name' => 'Célia', 'role' => 'Conseillère voyage adapté' ),
	array( 'name' => 'Élodie', 'role' => 'Conseillère voyage adapté' ),
);
$adaptours_fake_names = array( 'Caroline Lefèvre', 'Marc Hoffmann', 'Inès Berger', 'Tom Renaud' );

$adaptours_qsn_visit = function ( &$block ) use ( $adaptours_kpi_qsn, $adaptours_team, $adaptours_fake_names ) {
	$name = $block['blockName'] ?? '';

	if ( 'adaptours/kpi-bar' === $name ) {
		$block['attrs'] = array_merge( is_array( $block['attrs'] ) ? $block['attrs'] : array(), $adaptours_kpi_qsn );
		return;
	}

	if ( 'adaptours/team-grid' === $name ) {
		$has_fake = false;
		foreach ( (array) $block['innerBlocks'] as $child ) {
			if ( in_array( $child['attrs']['name'] ?? '', $adaptours_fake_names, true ) ) {
				$has_fake = true;
				break;
			}
		}
		if ( ! empty( $block['innerBlocks'] ) && ! $has_fake ) {
			return; // Membres saisis par la cliente : on ne touche pas.
		}
		$inner   = array();
		$content = array( "\n" );
		foreach ( $adaptours_team as $member ) {
			$inner[]   = array(
				'blockName'    => 'adaptours/team-grid-member',
				'attrs'        => $member,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
			$content[] = null;
			$content[] = "\n";
		}
		$block['innerBlocks']  = $inner;
		$block['innerContent'] = $content;
	}
};

$adaptours_qsn = get_page_by_path( 'qui-sommes-nous' );
if ( $adaptours_qsn ) {
	foreach ( $adaptours_with_translations( $adaptours_qsn->ID ) as $id ) {
		$adaptours_patch_post( $id, $adaptours_qsn_visit, 'qui-sommes-nous' );
	}
} else {
	echo "  SKIP : page qui-sommes-nous introuvable\n";
}

// 4. Formulaires CF7 Devis : premier échange sous 48 h, le devis prend plus de temps.
echo "— Formulaires devis —\n";
$adaptours_form_replacements = array(
	'Nous avons bien reçu toutes vos informations et revenons vers vous sous 48 h ouvrées avec une proposition sur mesure.'
		=> 'Nous avons bien reçu toutes vos informations et nous vous recontactons sous 48 h ouvrées pour un premier échange. Le devis demande un peu plus de temps : chaque voyage est construit sur mesure.',
	'Merci ! Votre demande est arrivée. On revient vers vous sous 48 h ouvrées avec un devis sur mesure.'
		=> 'Merci ! Votre demande est arrivée. On vous recontacte sous 48 h ouvrées pour un premier échange.',
	'We have received all your information and will get back to you within 48 business hours with a tailor-made proposal.'
		=> 'We have received all your information and will contact you within 48 business hours for a first conversation. The quote takes a little more time: each trip is entirely tailor-made.',
	'Thank you! Your request has arrived. We’ll get back to you within 48 business hours with a tailor-made quote.'
		=> 'Thank you! Your request has arrived. We’ll contact you within 48 business hours for a first conversation.',
);

if ( class_exists( 'WPCF7_ContactForm' ) ) {
	foreach ( array( 'adaptours_devis_form_id', 'adaptours_devis_form_id_en' ) as $opt ) {
		$fid = (int) get_option( $opt, 0 );
		if ( ! $fid ) {
			echo "  SKIP {$opt} : option absente\n";
			continue;
		}
		$form = WPCF7_ContactForm::get_instance( $fid );
		if ( ! $form ) {
			echo "  SKIP form #{$fid} : introuvable\n";
			continue;
		}
		$adaptours_hits    = 0;
		$adaptours_replace = function ( $value ) use ( &$adaptours_replace, $adaptours_form_replacements, &$adaptours_hits ) {
			if ( is_string( $value ) ) {
				$new = strtr( $value, $adaptours_form_replacements );
				if ( $new !== $value ) {
					$adaptours_hits++;
				}
				return $new;
			}
			if ( is_array( $value ) ) {
				return array_map( $adaptours_replace, $value );
			}
			return $value;
		};
		$props = $adaptours_replace( $form->get_properties() );
		if ( $adaptours_hits ) {
			$form->set_properties( $props );
			$form->save();
			echo "  Form #{$fid} : {$adaptours_hits} texte(s) corrigé(s)\n";
		} else {
			echo "  Form #{$fid} : inchangé\n";
		}
	}
} else {
	echo "  SKIP : Contact Form 7 inactif\n";
}

echo "Terminé.\n";
