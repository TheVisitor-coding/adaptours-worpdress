/**
 * Bloc adaptours/quote — composant d'édition (côté éditeur). Archétype : plat-texte.
 *
 * Tous les champs s'éditent dans le panneau latéral (citation, auteur, extrait en orange,
 * couleur de fond) ; le canvas affiche l'aperçu du rendu serveur (ServerSideRender).
 * Bloc dynamique : rendu FRONT dans render.php → save = null.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

const BACKGROUND_OPTIONS = [
	{ label: __( 'Blanc cassé', 'adaptours' ), value: 'surface' },
	{ label: __( 'Beige', 'adaptours' ), value: 'surface-alt' },
	{ label: __( 'Pêche', 'adaptours' ), value: 'highlight-soft' },
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Contenu', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Citation', 'adaptours' ) }
							help={ __( 'Le texte de la citation, sans guillemets : ils sont ajoutés automatiquement.', 'adaptours' ) }
							value={ attributes.quote }
							onChange={ ( quote ) => setAttributes( { quote } ) }
							rows={ 4 }
						/>
						<TextControl
							label={ __( 'Auteur', 'adaptours' ) }
							help={ __( 'Exemple : Camille & Théo — Mexique, mars 2026. Laissez vide pour aucun.', 'adaptours' ) }
							value={ attributes.author }
							onChange={ ( author ) => setAttributes( { author } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Mise en avant', 'adaptours' ) }>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							help={ __( 'Recopiez ici un court extrait de la citation à souligner. Laissez vide pour aucun.', 'adaptours' ) }
							value={ attributes.quote_accent }
							onChange={ ( quote_accent ) => setAttributes( { quote_accent } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Mise en page', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Couleur de fond', 'adaptours' ) }
							help={ __( 'La couleur derrière cette section.', 'adaptours' ) }
							value={ attributes.background }
							options={ BACKGROUND_OPTIONS }
							onChange={ ( background ) => setAttributes( { background } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						EmptyResponsePlaceholder={ () => (
							<p>{ __( 'Cette citation est vide : remplissez les champs dans la colonne de droite.', 'adaptours' ) }</p>
						) }
					/>
				</div>
			</>
		);
	},
	save: () => null,
} );
