/**
 * Bloc adaptours/cards-numbered-card — composant d'édition d'une carte numérotée.
 *
 * Titre et description s'éditent dans le panneau latéral (carte sélectionnée) ; le canvas
 * affiche un aperçu statique. Le numéro 01..NN est automatique (compteur CSS), non
 * éditable. Pas de style.scss propre : le layout est porté par le bloc parent
 * adaptours/cards-numbered.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'cards-numbered__card' } );
		const isEmpty = ! attributes.card_title && ! attributes.description;

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Carte', 'adaptours' ) }>
						<TextControl
							label={ __( 'Titre de la carte', 'adaptours' ) }
							value={ attributes.card_title }
							onChange={ ( card_title ) => setAttributes( { card_title } ) }
						/>
						<TextareaControl
							label={ __( 'Description', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( description ) => setAttributes( { description } ) }
							rows={ 3 }
						/>
					</PanelBody>
				</InspectorControls>

				<li { ...blockProps }>
					<span className="cards-numbered__num" aria-hidden="true" />
					{ isEmpty ? (
						<p className="cards-numbered__card-desc">
							{ __( 'Carte vide : remplissez les champs dans la colonne de droite.', 'adaptours' ) }
						</p>
					) : (
						<>
							{ !! attributes.card_title && (
								<h3 className="cards-numbered__card-title">{ attributes.card_title }</h3>
							) }
							{ !! attributes.description && (
								<p className="cards-numbered__card-desc">{ attributes.description }</p>
							) }
						</>
					) }
				</li>
			</>
		);
	},
	save: () => null,
} );
