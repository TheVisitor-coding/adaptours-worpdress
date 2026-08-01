/**
 * Bloc adaptours/card-grid — composant d'édition. Parent InnerBlocks.
 *
 * Hybride : l'en-tête centré (surtitre / titre bichrome), le nombre de colonnes et la
 * couleur de fond s'éditent dans le panneau latéral (le canvas en affiche un aperçu
 * statique) ; les cartes sont des blocs enfants « adaptours/card-grid-card » ajoutés/
 * retirés dans le canvas. save n'émet que les cartes ; render.php les enveloppe dans la
 * grille + ajoute l'en-tête.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

const ALLOWED = [ 'adaptours/card-grid-card' ];
const TEMPLATE = [
	[ 'adaptours/card-grid-card', {} ],
	[ 'adaptours/card-grid-card', {} ],
	[ 'adaptours/card-grid-card', {} ],
];

const BACKGROUND_OPTIONS = [
	{ label: __( 'Blanc cassé', 'adaptours' ), value: 'surface' },
	{ label: __( 'Beige', 'adaptours' ), value: 'surface-alt' },
	{ label: __( 'Pêche', 'adaptours' ), value: 'highlight-soft' },
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const columns = [ 2, 3, 4 ].includes( attributes.columns ) ? attributes.columns : 3;
		const background = BACKGROUND_OPTIONS.some( ( o ) => o.value === attributes.background )
			? attributes.background
			: 'surface';
		const blockProps = useBlockProps( {
			className: `card-grid is-cols-${ columns } card-grid--bg-${ background }`,
			style: { '--adaptours-card-grid-cols': columns },
		} );
		const innerProps = useInnerBlocksProps(
			{ className: 'card-grid__grid' },
			{
				allowedBlocks: ALLOWED,
				template: TEMPLATE,
				templateLock: false,
				orientation: 'horizontal',
			}
		);
		const hasTitle = !! ( attributes.title_part_1 || attributes.title_part_2 );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Titre', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							help={ __( 'Petit texte au-dessus du titre. Laissez vide pour aucun.', 'adaptours' ) }
							value={ attributes.eyebrow }
							onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
						/>
						<TextControl
							label={ __( 'Titre — début', 'adaptours' ) }
							value={ attributes.title_part_1 }
							onChange={ ( title_part_1 ) => setAttributes( { title_part_1 } ) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							help={ __( 'La fin du titre, affichée en orange.', 'adaptours' ) }
							value={ attributes.title_part_2 }
							onChange={ ( title_part_2 ) => setAttributes( { title_part_2 } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Mise en page', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Nombre de colonnes', 'adaptours' ) }
							value={ String( columns ) }
							options={ [
								{ label: '2', value: '2' },
								{ label: '3', value: '3' },
								{ label: '4', value: '4' },
							] }
							onChange={ ( v ) => setAttributes( { columns: parseInt( v, 10 ) } ) }
						/>
						<SelectControl
							label={ __( 'Couleur de fond', 'adaptours' ) }
							help={ __( 'La couleur derrière cette section.', 'adaptours' ) }
							value={ background }
							options={ BACKGROUND_OPTIONS }
							onChange={ ( v ) => setAttributes( { background: v } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<div className="card-grid__inner">
						{ ( !! attributes.eyebrow || hasTitle ) && (
							<header className="card-grid__head">
								{ !! attributes.eyebrow && (
									<p className="card-grid__eyebrow eyebrow">{ attributes.eyebrow }</p>
								) }
								{ hasTitle && (
									<h2 className="card-grid__title">
										{ attributes.title_part_1 }{ ' ' }
										<span className="accent">{ attributes.title_part_2 }</span>
									</h2>
								) }
							</header>
						) }
						<ul { ...innerProps } />
					</div>
				</section>
			</>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
