/**
 * Bloc adaptours/cards-numbered — composant d'édition. Parent InnerBlocks.
 *
 * Hybride : l'en-tête (surtitre / titre bichrome / description) et la couleur de fond
 * s'éditent dans le panneau latéral (le canvas en affiche un aperçu statique) ; les cartes
 * sont des blocs enfants « adaptours/cards-numbered-card » ajoutés/retirés dans le canvas.
 * save n'émet que les cartes ; render.php les enveloppe dans une <ol> + ajoute l'en-tête.
 * Numérotation 01..NN en CSS.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

const ALLOWED = [ 'adaptours/cards-numbered-card' ];
const TEMPLATE = [
	[ 'adaptours/cards-numbered-card', {} ],
	[ 'adaptours/cards-numbered-card', {} ],
	[ 'adaptours/cards-numbered-card', {} ],
];

const BACKGROUND_OPTIONS = [
	{ label: __( 'Blanc cassé', 'adaptours' ), value: 'surface' },
	{ label: __( 'Beige', 'adaptours' ), value: 'surface-alt' },
	{ label: __( 'Pêche', 'adaptours' ), value: 'highlight-soft' },
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const background = BACKGROUND_OPTIONS.some( ( o ) => o.value === attributes.background )
			? attributes.background
			: 'surface-alt';
		const blockProps = useBlockProps( {
			className: `cards-numbered cards-numbered--bg-${ background }`,
		} );
		const innerProps = useInnerBlocksProps(
			{ className: 'cards-numbered__grid' },
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
					<PanelBody title={ __( 'Texte', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Texte à droite du titre', 'adaptours' ) }
							help={ __( 'Petit texte affiché à droite du titre. Laissez vide pour aucun.', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( description ) => setAttributes( { description } ) }
							rows={ 3 }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Mise en page', 'adaptours' ) }>
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
					<div className="cards-numbered__inner">
						<header className="cards-numbered__head">
							<div className="cards-numbered__intro">
								{ !! attributes.eyebrow && (
									<p className="cards-numbered__eyebrow eyebrow">{ attributes.eyebrow }</p>
								) }
								{ hasTitle && (
									<h2 className="cards-numbered__title">
										{ attributes.title_part_1 }{ ' ' }
										<span className="accent">{ attributes.title_part_2 }</span>
									</h2>
								) }
							</div>
							{ !! attributes.description && (
								<p className="cards-numbered__desc">{ attributes.description }</p>
							) }
						</header>
						<ol { ...innerProps } />
					</div>
				</section>
			</>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
