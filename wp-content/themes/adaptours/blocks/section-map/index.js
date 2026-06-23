/**
 * Bloc adaptours/section-map — composant d'édition.
 *
 * Contenu mixte : textes éditoriaux (surtitre / titre / texte de vol) édités dans le
 * panneau latéral, et données destination (carte + bande d'infos) lues côté serveur
 * depuis la fiche. Aperçu via ServerSideRender (le contexte post est transmis pour lire
 * les bons champs de la destination).
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();

		// Contexte post (la destination éditée) transmis au rendu serveur.
		const postId = useSelect(
			( select ) => select( 'core/editor' )?.getCurrentPostId(),
			[]
		);

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Textes', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							value={ attributes.eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							help={ __( 'Petit texte au-dessus du titre.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Titre', 'adaptours' ) }
							value={ attributes.title }
							onChange={ ( v ) => setAttributes( { title: v } ) }
							help={ __(
								'Par exemple « De Paris à ». Le mot suivant (la destination) se met en orange ci-dessous.',
								'adaptours'
							) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ attributes.title_accent }
							onChange={ ( v ) => setAttributes( { title_accent: v } ) }
							help={ __(
								'Le nom de la destination, mis en orange à la fin du titre (ex. : Denpasar).',
								'adaptours'
							) }
						/>
						<TextareaControl
							label={ __( 'Texte sur le vol', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
							help={ __(
								'Quelques lignes sur le trajet, affichées à droite du titre.',
								'adaptours'
							) }
						/>
						<TextControl
							label={ __( 'Distance', 'adaptours' ) }
							value={ attributes.distance_label }
							onChange={ ( v ) => setAttributes( { distance_label: v } ) }
							help={ __(
								'Laissez vide pour calculer automatiquement la distance à partir de la fiche destination.',
								'adaptours'
							) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						urlQueryArgs={ postId ? { post_id: postId } : undefined }
					/>
				</div>
			</>
		);
	},
	save: () => null,
} );
