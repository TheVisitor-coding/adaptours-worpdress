/**
 * Bloc adaptours/cards-numbered-card — composant d'édition d'une carte numérotée.
 *
 * Édition inline : titre + description en RichText. Le numéro 01..NN est automatique
 * (compteur CSS), non éditable. Pas de style.scss propre : le layout est porté par le
 * bloc parent adaptours/cards-numbered.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'cards-numbered__card' } );

		return (
			<li { ...blockProps }>
				<span className="cards-numbered__num" aria-hidden="true" />
				<RichText
					tagName="h3"
					className="cards-numbered__card-title"
					value={ attributes.card_title }
					allowedFormats={ [] }
					onChange={ ( card_title ) => setAttributes( { card_title } ) }
					placeholder={ __( 'Titre de la carte', 'adaptours' ) }
				/>
				<RichText
					tagName="p"
					className="cards-numbered__card-desc"
					value={ attributes.description }
					allowedFormats={ [] }
					onChange={ ( description ) => setAttributes( { description } ) }
					placeholder={ __( 'Description…', 'adaptours' ) }
				/>
			</li>
		);
	},
	save: () => null,
} );
