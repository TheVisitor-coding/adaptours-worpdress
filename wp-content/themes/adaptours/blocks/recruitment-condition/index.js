/**
 * Bloc adaptours/recruitment-condition — composant d'édition d'une condition.
 *
 * Édition inline : titre + explication en RichText. Le numéro 01..NN est automatique
 * (compteur CSS). Pas de style.scss propre : le layout est porté par le bloc parent
 * adaptours/recruitment.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'recruitment__condition' } );

		return (
			<li { ...blockProps }>
				<span className="recruitment__num" aria-hidden="true" />
				<div className="recruitment__cond-body">
					<RichText
						tagName="p"
						className="recruitment__cond-title"
						value={ attributes.title }
						allowedFormats={ [] }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder={ __( 'Titre de la condition', 'adaptours' ) }
					/>
					<RichText
						tagName="p"
						className="recruitment__cond-desc"
						value={ attributes.description }
						allowedFormats={ [] }
						onChange={ ( v ) => setAttributes( { description: v } ) }
						placeholder={ __( 'Explication…', 'adaptours' ) }
					/>
				</div>
			</li>
		);
	},
	save: () => null,
} );
