/**
 * Bloc adaptours/dual-cta — composant d'édition (deux cartes d'appel à l'action).
 * Rendu front = render.php. Édition via Inspector + aperçu ServerSideRender (titres
 * bichromes et replis de liens rendus côté serveur).
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
					<PanelBody title={ __( 'Carte 1 — Voyager', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							value={ attributes.card_1_eyebrow }
							onChange={ ( v ) => setAttributes( { card_1_eyebrow: v } ) }
						/>
						<TextControl
							label={ __( 'Titre — début', 'adaptours' ) }
							value={ attributes.card_1_title_1 }
							onChange={ ( v ) => setAttributes( { card_1_title_1: v } ) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ attributes.card_1_title_2 }
							onChange={ ( v ) => setAttributes( { card_1_title_2: v } ) }
						/>
						<TextareaControl
							label={ __( 'Texte', 'adaptours' ) }
							value={ attributes.card_1_description }
							onChange={ ( v ) => setAttributes( { card_1_description: v } ) }
							rows={ 3 }
						/>
						<TextControl
							label={ __( 'Bouton — texte', 'adaptours' ) }
							value={ attributes.card_1_cta_label }
							onChange={ ( v ) => setAttributes( { card_1_cta_label: v } ) }
						/>
						<TextControl
							label={ __( 'Bouton — lien', 'adaptours' ) }
							value={ attributes.card_1_cta_url }
							onChange={ ( v ) => setAttributes( { card_1_cta_url: v } ) }
							help={ __( 'Laissez vide pour pointer vers la page Devis.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Carte 2 — Nous rejoindre', 'adaptours' ) } initialOpen={ false }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							value={ attributes.card_2_eyebrow }
							onChange={ ( v ) => setAttributes( { card_2_eyebrow: v } ) }
						/>
						<TextControl
							label={ __( 'Titre — début', 'adaptours' ) }
							value={ attributes.card_2_title_1 }
							onChange={ ( v ) => setAttributes( { card_2_title_1: v } ) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ attributes.card_2_title_2 }
							onChange={ ( v ) => setAttributes( { card_2_title_2: v } ) }
						/>
						<TextareaControl
							label={ __( 'Texte', 'adaptours' ) }
							value={ attributes.card_2_description }
							onChange={ ( v ) => setAttributes( { card_2_description: v } ) }
							rows={ 3 }
						/>
						<TextControl
							label={ __( 'Bouton — texte', 'adaptours' ) }
							value={ attributes.card_2_cta_label }
							onChange={ ( v ) => setAttributes( { card_2_cta_label: v } ) }
							help={ __( 'Laissez vide pour afficher l’e-mail général du site.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Bouton — lien', 'adaptours' ) }
							value={ attributes.card_2_cta_url }
							onChange={ ( v ) => setAttributes( { card_2_cta_url: v } ) }
							help={ __( 'Laissez vide pour écrire à l’e-mail général du site.', 'adaptours' ) }
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
