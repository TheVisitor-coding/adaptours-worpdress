<?php
/**
 * Traduction des valeurs par défaut éditoriales des blocs adaptours/*.
 *
 * Gutenberg ne sérialise pas un attribut dont la valeur est égale à son `default` : sur une
 * page traduite dont les blocs viennent du gabarit de adaptours_lock_map(), aucun attribut
 * n'est en base et WP_Block réinjecte les valeurs françaises du block.json. On les fait
 * passer par _x() au moment du rendu.
 *
 * Les msgid littéraux vivent dans inc/block-default-strings.php (généré par
 * tools/gen-block-default-strings.py) : `wp i18n make-pot` n'extrait pas attributes[].default.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Indique si le rendu se fait dans la langue d'écriture des block.json.
 *
 * determine_locale() est le prédicat exact de « quel .mo est chargé » — get_locale() ignore
 * la locale de l'utilisateur en admin et sur les requêtes REST de l'éditeur.
 *
 * @return bool
 */
function adaptours_block_defaults_is_source_locale() {
	$source = 'fr_FR';

	if ( function_exists( 'pll_default_language' ) ) {
		$default = pll_default_language( 'locale' );
		if ( is_string( $default ) && '' !== $default ) {
			$source = $default;
		}
	}

	return determine_locale() === $source;
}

/**
 * Injecte les valeurs par défaut traduites dans les attributs absents d'un bloc adaptours/*.
 *
 * @param array $parsed_block Bloc parsé.
 * @return array
 */
function adaptours_translate_block_defaults( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] )
		|| 0 !== strpos( $parsed_block['blockName'], 'adaptours/' )
		|| adaptours_block_defaults_is_source_locale() ) {
		return $parsed_block;
	}

	$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $parsed_block['blockName'] );
	if ( ! $block_type instanceof WP_Block_Type || ! is_array( $block_type->attributes ) ) {
		return $parsed_block;
	}

	$attrs   = ( isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ) ? $parsed_block['attrs'] : array();
	$changed = false;

	foreach ( $block_type->attributes as $name => $schema ) {
		// array_key_exists et non isset : un champ vidé par la cliente doit rester vide.
		if ( array_key_exists( $name, $attrs ) ) {
			continue;
		}
		if ( ! isset( $schema['type'], $schema['default'] ) || 'string' !== $schema['type'] ) {
			continue;
		}

		$default = $schema['default'];
		if ( ! is_string( $default ) || '' === trim( $default ) ) {
			continue;
		}

		// Appel dynamique assumé : l'extraction passe par inc/block-default-strings.php.
		$translated = translate_with_gettext_context( $default, $parsed_block['blockName'] . ':' . $name, 'adaptours' );

		if ( $translated !== $default ) {
			$attrs[ $name ] = $translated;
			$changed        = true;
		}
	}

	if ( $changed ) {
		$parsed_block['attrs'] = $attrs;
	}

	return $parsed_block;
}
add_filter( 'render_block_data', 'adaptours_translate_block_defaults' );
