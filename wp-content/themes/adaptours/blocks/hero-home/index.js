/**
 * Bloc adaptours/hero-home — composant d'édition (en-tête page d'accueil).
 * Rendu front = render.php. Édition via Inspector + aperçu ServerSideRender
 * (le titre bichrome, le trust strip et le collage sont rendus côté serveur).
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Titre', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							value={ attributes.eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							help={ __( 'Petit texte au-dessus du titre.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Titre — début', 'adaptours' ) }
							value={ attributes.title_part_1 }
							onChange={ ( v ) => setAttributes( { title_part_1: v } ) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ attributes.title_part_2 }
							onChange={ ( v ) => setAttributes( { title_part_2: v } ) }
							help={ __( 'Le ou les mots du titre mis en avant.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Ligne manuscrite', 'adaptours' ) }
							value={ attributes.title_script }
							onChange={ ( v ) => setAttributes( { title_script: v } ) }
							help={ __( 'Dernière ligne du titre, en écriture manuscrite. Laissez vide pour la masquer.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Introduction', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Texte d’introduction', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Boutons', 'adaptours' ) }>
						<TextControl
							label={ __( 'Bouton principal — texte', 'adaptours' ) }
							value={ attributes.cta_primary_label }
							onChange={ ( v ) => setAttributes( { cta_primary_label: v } ) }
						/>
						<TextControl
							label={ __( 'Bouton principal — lien', 'adaptours' ) }
							value={ attributes.cta_primary_url }
							onChange={ ( v ) => setAttributes( { cta_primary_url: v } ) }
							help={ __( 'Laissez vide pour pointer vers la page Devis.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Bouton secondaire — texte', 'adaptours' ) }
							value={ attributes.cta_secondary_label }
							onChange={ ( v ) => setAttributes( { cta_secondary_label: v } ) }
						/>
						<TextControl
							label={ __( 'Bouton secondaire — lien', 'adaptours' ) }
							value={ attributes.cta_secondary_url }
							onChange={ ( v ) => setAttributes( { cta_secondary_url: v } ) }
							help={ __( 'Laissez vide pour pointer vers la liste des destinations.', 'adaptours' ) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender block={ metadata.name } attributes={ attributes } />
				</div>
			</>
		);
	},
	save: () => null,
} );
