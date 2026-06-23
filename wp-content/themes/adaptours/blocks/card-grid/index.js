/**
 * Bloc adaptours/card-grid — composant d'édition. Parent InnerBlocks.
 *
 * En-tête centré (surtitre / titre bichrome) édité INLINE via RichText ; nombre de
 * colonnes dans le panneau latéral ; les cartes sont des blocs enfants
 * « adaptours/card-grid-card ». save n'émet que les cartes ; render.php les enveloppe
 * dans la grille + ajoute l'en-tête.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	RichText,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
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

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const columns = [ 2, 3, 4 ].includes( attributes.columns ) ? attributes.columns : 3;
		const blockProps = useBlockProps( {
			className: `card-grid is-cols-${ columns }`,
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

		return (
			<>
				<InspectorControls>
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
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<div className="card-grid__inner">
						<header className="card-grid__head">
							<RichText
								tagName="p"
								className="card-grid__eyebrow eyebrow"
								value={ attributes.eyebrow }
								allowedFormats={ [] }
								onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
								placeholder={ __( 'Surtitre (optionnel)', 'adaptours' ) }
							/>
							<h2 className="card-grid__title">
								<RichText
									tagName="span"
									value={ attributes.title_part_1 }
									allowedFormats={ [] }
									onChange={ ( title_part_1 ) => setAttributes( { title_part_1 } ) }
									placeholder={ __( 'Titre…', 'adaptours' ) }
								/>{ ' ' }
								<RichText
									tagName="span"
									className="accent"
									value={ attributes.title_part_2 }
									allowedFormats={ [] }
									onChange={ ( title_part_2 ) => setAttributes( { title_part_2 } ) }
									placeholder={ __( 'mot(s) en orange', 'adaptours' ) }
								/>
							</h2>
						</header>
						<ul { ...innerProps } />
					</div>
				</section>
			</>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
