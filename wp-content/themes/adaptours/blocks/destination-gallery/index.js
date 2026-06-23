/**
 * Bloc adaptours/destination-gallery — composant d'édition.
 *
 * Textes éditoriaux (surtitre / titre / accroche) dans le panneau latéral ; les photos
 * sont lues côté serveur depuis la galerie de la fiche destination. Aperçu ServerSideRender.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
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
						<TextControl
							label={ __( 'Petite phrase manuscrite', 'adaptours' ) }
							value={ attributes.tagline }
							onChange={ ( v ) => setAttributes( { tagline: v } ) }
							help={ __( 'Affichée à droite du titre, en écriture manuscrite.', 'adaptours' ) }
						/>
						<p className="adaptours-editor-note">
							{ __(
								'Les photos se choisissent dans « Galerie de la destination », sur la fiche.',
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
