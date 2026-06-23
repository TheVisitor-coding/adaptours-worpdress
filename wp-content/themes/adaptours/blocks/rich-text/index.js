/**
 * Bloc adaptours/rich-text — composant d'édition (côté éditeur). Archétype : prose éditoriale.
 *
 * En-tête (surtitre + titre bichrome) édité INLINE via RichText. Le corps est une zone
 * libre InnerBlocks restreinte aux blocs de texte natifs (paragraphe, sous-titre, liste)
 * → édition « comme un traitement de texte ». Bloc dynamique : render.php enveloppe le
 * corps (clé "style"). save = <InnerBlocks.Content/>.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

const ALLOWED = [ 'core/paragraph', 'core/heading', 'core/list' ];
const TEMPLATE = [
	[ 'core/paragraph', { placeholder: __( 'Votre texte…', 'adaptours' ) } ],
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'rich-text' } );

		return (
			<section { ...blockProps }>
				<div className="rich-text__inner">
					<header className="rich-text__head">
						<RichText
							tagName="p"
							className="rich-text__eyebrow eyebrow"
							value={ attributes.eyebrow }
							allowedFormats={ [] }
							onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
							placeholder={ __( 'Surtitre (optionnel)', 'adaptours' ) }
						/>
						<h2 className="rich-text__title">
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

					<div className="rich-text__body">
						<InnerBlocks
							allowedBlocks={ ALLOWED }
							template={ TEMPLATE }
							templateLock={ false }
						/>
					</div>
				</div>
			</section>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
