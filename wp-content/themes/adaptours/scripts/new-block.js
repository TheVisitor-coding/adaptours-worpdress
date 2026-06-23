#!/usr/bin/env node
/**
 * Générateur de scaffold de bloc Adaptours.
 *
 * Usage : npm run new-block -- <slug> [archetype]
 *   <slug>      nom du bloc sans namespace, ^[a-z][a-z0-9-]*$ (ex. avis-spotlight)
 *   [archetype] plat-texte (défaut) | media-texte | innerblocks
 *
 * Produit blocks/<slug>/{block.json,index.js,render.php,style.scss} pré-câblés,
 * reproduisant la convention recettée du bloc kpi-bar (un seul index.js qui importe
 * le SCSS, save dynamique, block.json pointant les assets compilés). Ensuite :
 *
 *   npm run build   compile vers assets/build/blocks/<slug>/ ; inc/blocks.php boucle
 *                   sur ce dossier de build et enregistre le bloc automatiquement.
 *
 * Ce script NE touche PAS inc/blocks.php ni adaptours_lock_map() : penser à ajouter
 * l'entrée de lock/template du nouveau bloc selon son contexte d'édition.
 */

'use strict';

const fs = require( 'fs' );
const path = require( 'path' );

const ARCHETYPES = [ 'plat-texte', 'media-texte', 'innerblocks' ];

const [ , , slug, archetypeArg ] = process.argv;
const archetype = archetypeArg || 'plat-texte';

function fail( msg ) {
	process.stderr.write( '\n✖ ' + msg + '\n\n' );
	process.exit( 1 );
}

if ( ! slug ) {
	fail( 'Slug manquant. Usage : npm run new-block -- <slug> [plat-texte|media-texte|innerblocks]' );
}
if ( ! /^[a-z][a-z0-9-]*$/.test( slug ) ) {
	fail(
		'Slug invalide : « ' + slug + ' ». Attendu ^[a-z][a-z0-9-]*$ ' +
		'(minuscules / chiffres / tirets, commence par une lettre). ' +
		'Rappel : un nom de bloc ne peut pas commencer par « _ ».'
	);
}
if ( ! ARCHETYPES.includes( archetype ) ) {
	fail( 'Archétype inconnu : « ' + archetype + ' ». Valeurs : ' + ARCHETYPES.join( ', ' ) + '.' );
}

const themeRoot = path.resolve( __dirname, '..' );
const blockDir = path.join( themeRoot, 'blocks', slug );
if ( fs.existsSync( blockDir ) ) {
	fail( 'Le dossier existe déjà : blocks/' + slug + '/' );
}

const title = slug
	.split( '-' )
	.map( ( w ) => w.charAt( 0 ).toUpperCase() + w.slice( 1 ) )
	.join( ' ' );
const cls = `wp-block-adaptours-${ slug }`;

// --- Templates ---------------------------------------------------------------

function blockJson() {
	const obj = {
		$schema: 'https://schemas.wp.org/trunk/block.json',
		apiVersion: 3,
		name: `adaptours/${ slug }`,
		title,
		category: 'adaptours',
		icon: 'block-default',
		description: '',
		textdomain: 'adaptours',
		supports: { html: false, anchor: true },
		attributes: {},
		render: 'file:./render.php',
		editorScript: 'file:./index.js',
		style: 'file:./style-index.css',
	};
	return JSON.stringify( obj, null, '\t' ) + '\n';
}

function indexJs() {
	const intro =
`/**
 * Bloc adaptours/${ slug } — composant d'édition (côté éditeur). Archétype : ${ archetype }.
 * Bloc dynamique : le rendu FRONT est dans render.php.
 */

import { registerBlockType } from '@wordpress/blocks';
`;

	// L'import du SCSS est OBLIGATOIRE : sans lui wp-scripts n'émet pas style-index.css.
	const styleImport =
`
// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

`;

	if ( archetype === 'innerblocks' ) {
		return (
			intro +
			"import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';\n" +
			"import metadata from './block.json';\n" +
			styleImport +
`registerBlockType( metadata.name, {
	edit: () => {
		const blockProps = useBlockProps();
		// Verrou cliente : une fois le bloc enfant créé, restreindre la palette, ex. :
		//   allowedBlocks={ [ 'adaptours/${ slug }-item' ] }
		//   template={ [ [ 'adaptours/${ slug }-item' ] ] }
		//   templateLock={ false }
		return (
			<section { ...blockProps }>
				<InnerBlocks />
			</section>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
`
		);
	}

	if ( archetype === 'media-texte' ) {
		return (
			intro +
			"import { useBlockProps } from '@wordpress/block-editor';\n" +
			"import { __ } from '@wordpress/i18n';\n" +
			"import metadata from './block.json';\n" +
			styleImport +
`registerBlockType( metadata.name, {
	edit: () => {
		const blockProps = useBlockProps();
		return (
			<section { ...blockProps }>
				<div className="${ cls }__inner">
					<figure className="${ cls }__media">
						{ /* TODO : MediaUpload / image — alt obligatoire, aspect-ratio anti-CLS. */ }
					</figure>
					<div className="${ cls }__body">
						{ /* TODO : titre + texte (RichText) + CTA.
						   Pour l'édition inline : importer RichText de '@wordpress/block-editor',
						   déclarer les attributs dans block.json, puis les éditer ici. */ }
						<p>{ __( '${ title } — média + texte à intégrer.', 'adaptours' ) }</p>
					</div>
				</div>
			</section>
		);
	},
	save: () => null,
} );
`
		);
	}

	// plat-texte (défaut)
	return (
		intro +
		"import { useBlockProps } from '@wordpress/block-editor';\n" +
		"import { __ } from '@wordpress/i18n';\n" +
		"import metadata from './block.json';\n" +
		styleImport +
`registerBlockType( metadata.name, {
	edit: () => {
		const blockProps = useBlockProps();
		return (
			<section { ...blockProps }>
				<div className="${ cls }__inner">
					{ /* TODO : déclarer les attributs dans block.json, puis éditer ici.
					   Édition inline (ergonomie cliente) : importer RichText de
					   '@wordpress/block-editor' et, par ex. :
					     <RichText
					       tagName="p"
					       className="${ cls }__text"
					       value={ attributes.text }
					       allowedFormats={ [] }
					       onChange={ ( text ) => setAttributes( { text } ) }
					       placeholder={ __( 'Texte…', 'adaptours' ) }
					     /> */ }
					<p>{ __( '${ title } — bloc à intégrer.', 'adaptours' ) }</p>
				</div>
			</section>
		);
	},
	save: () => null,
} );
`
	);
}

function renderPhp() {
	const head =
`<?php
/**
 * Bloc adaptours/${ slug } — rendu serveur.
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne (InnerBlocks).
 * @var WP_Block $block      Instance du bloc.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

`;

	if ( archetype === 'innerblocks' ) {
		return (
			head +
`if ( '' === trim( (string) $content ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes();
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput — échappé par get_block_wrapper_attributes() ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput — InnerBlocks rendus et assainis par WordPress ?>
</section>
`
		);
	}

	if ( archetype === 'media-texte' ) {
		return (
			head +
`$wrapper_attributes = get_block_wrapper_attributes();
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput — échappé par get_block_wrapper_attributes() ?>>
	<div class="${ cls }__inner">
		<figure class="${ cls }__media">
			<?php /* TODO : <img> responsive — esc_url(), esc_attr() sur l'alt, loading="lazy". */ ?>
		</figure>
		<div class="${ cls }__body">
			<?php /* TODO : markup BEM sémantique. Échapper TOUTE sortie. */ ?>
		</div>
	</div>
</section>
`
		);
	}

	// plat-texte (défaut)
	return (
		head +
`$wrapper_attributes = get_block_wrapper_attributes();
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput — échappé par get_block_wrapper_attributes() ?>>
	<div class="${ cls }__inner">
		<?php /* TODO : markup BEM sémantique. Échapper TOUTE sortie : esc_html() / esc_attr() / esc_url() / wp_kses_post(). */ ?>
	</div>
</section>
`
	);
}

function styleScss() {
	const header =
`// adaptours/${ slug } — styles front (clé "style" du block.json → style-index.css).
// Standards : conteneur 1280px, tokens theme.json, sizing rem, BEM scopé, desktop-first.

@use "abstracts" as *;

`;

	if ( archetype === 'media-texte' ) {
		return (
			header +
`.${ cls } {
	padding-block: var(--wp--preset--spacing--80);

	// Contenu borné à 1280px ; 2 colonnes média + texte (empilées en mobile).
	&__inner {
		@include container;
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: var(--wp--preset--spacing--60);
		align-items: center;

		@include bp(mobile) { grid-template-columns: 1fr; } // ≤ 768px : empilement
	}

	&__media {
		margin: 0;

		img {
			width: 100%;
			height: auto;
			aspect-ratio: 4 / 3;
			object-fit: cover;
			border-radius: var(--wp--custom--radius--media);
		}
	}

	&__body {
		// titre + texte…
	}

	@include bp(mobile) {
		padding-block: var(--wp--preset--spacing--60);
	}
}
`
		);
	}

	// plat-texte / innerblocks : conteneur simple.
	return (
		header +
`.${ cls } {
	padding-block: var(--wp--preset--spacing--80);

	// Contenu borné à 1280px, centré, gouttières fluides (section full-bleed possible).
	&__inner {
		@include container;
	}

	// --- Dégradation desktop-first ---
	// @include bp(tablet) { } // ≤ 1024px
	// @include bp(mobile) {   // ≤ 768px
	// 	padding-block: var(--wp--preset--spacing--60);
	// }
}
`
	);
}

// --- Écriture ----------------------------------------------------------------

fs.mkdirSync( blockDir, { recursive: true } );
fs.writeFileSync( path.join( blockDir, 'block.json' ), blockJson() );
fs.writeFileSync( path.join( blockDir, 'index.js' ), indexJs() );
fs.writeFileSync( path.join( blockDir, 'render.php' ), renderPhp() );
fs.writeFileSync( path.join( blockDir, 'style.scss' ), styleScss() );

process.stdout.write(
	'\n✓ Bloc adaptours/' + slug + ' (' + archetype + ') créé dans blocks/' + slug + '/\n' +
	'  1. npm run build        (compile + enregistre depuis assets/build/blocks/)\n' +
	'  2. adaptours_lock_map() (inc/blocks.php) : ajouter le lock/template si contexte verrouillé\n' +
	'  3. recette wp-env       (éditeur + front, en tenant compte du verrou cliente)\n\n'
);
