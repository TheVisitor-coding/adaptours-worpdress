/**
 * Bloc adaptours/cards-numbered — composant d'édition. Parent InnerBlocks.
 *
 * En-tête (surtitre / titre bichrome / description) édité INLINE via RichText ; les cartes
 * sont des blocs enfants « adaptours/cards-numbered-card ». save n'émet que les cartes ;
 * render.php les enveloppe dans une <ol> + ajoute l'en-tête. Numérotation 01..NN en CSS.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	RichText,
	InnerBlocks,
} from '@wordpress/block-editor';
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

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'cards-numbered' } );
		const innerProps = useInnerBlocksProps(
			{ className: 'cards-numbered__grid' },
			{
				allowedBlocks: ALLOWED,
				template: TEMPLATE,
				templateLock: false,
				orientation: 'horizontal',
			}
		);

		return (
			<section { ...blockProps }>
				<div className="cards-numbered__inner">
					<header className="cards-numbered__head">
						<div className="cards-numbered__intro">
							<RichText
								tagName="p"
								className="cards-numbered__eyebrow eyebrow"
								value={ attributes.eyebrow }
								allowedFormats={ [] }
								onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
								placeholder={ __( 'Surtitre (optionnel)', 'adaptours' ) }
							/>
							<h2 className="cards-numbered__title">
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
						</div>
						<RichText
							tagName="p"
							className="cards-numbered__desc"
							value={ attributes.description }
							allowedFormats={ [] }
							onChange={ ( description ) => setAttributes( { description } ) }
							placeholder={ __( 'Texte à droite du titre (optionnel)', 'adaptours' ) }
						/>
					</header>
					<ol { ...innerProps } />
				</div>
			</section>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
