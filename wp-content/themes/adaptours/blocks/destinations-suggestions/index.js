/**
 * Bloc adaptours/destinations-suggestions — composant d'édition.
 *
 * Textes éditoriaux (surtitre / titre / description / bouton) dans le panneau latéral ;
 * les destinations affichées sont lues côté serveur depuis la fiche. Aperçu ServerSideRender.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();

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
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ attributes.title_accent }
							onChange={ ( v ) => setAttributes( { title_accent: v } ) }
							help={ __(
								'Le ou les mots du titre à mettre en orange.',
								'adaptours'
							) }
						/>
						<TextareaControl
							label={ __( 'Description', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
							help={ __( 'Texte affiché à droite du titre.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Bouton', 'adaptours' ) }>
						<TextControl
							label={ __( 'Texte du bouton', 'adaptours' ) }
							value={ attributes.cta_label }
							onChange={ ( v ) => setAttributes( { cta_label: v } ) }
						/>
						<TextControl
							label={ __( 'Lien du bouton', 'adaptours' ) }
							type="url"
							value={ attributes.cta_url }
							onChange={ ( v ) => setAttributes( { cta_url: v } ) }
							help={ __(
								'Laissez vide pour aller vers la liste de toutes les destinations.',
								'adaptours'
							) }
						/>
						<p className="adaptours-editor-note">
							{ __(
								'Les destinations affichées se choisissent dans la fiche, champ « Destinations suggérées ».',
								'adaptours'
							) }
						</p>
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
