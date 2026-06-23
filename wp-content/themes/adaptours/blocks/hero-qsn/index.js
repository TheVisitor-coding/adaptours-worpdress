/**
 * Bloc adaptours/hero-qsn — composant d'édition (en-tête Qui sommes-nous).
 * Rendu front = render.php. Édition via Inspector + aperçu ServerSideRender
 * (titre bichrome et photo rendus côté serveur).
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button } from '@wordpress/components';
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
					<PanelBody title={ __( 'Bouton', 'adaptours' ) }>
						<TextControl
							label={ __( 'Texte du bouton', 'adaptours' ) }
							value={ attributes.cta_label }
							onChange={ ( v ) => setAttributes( { cta_label: v } ) }
						/>
						<TextControl
							label={ __( 'Lien du bouton', 'adaptours' ) }
							value={ attributes.cta_url }
							onChange={ ( v ) => setAttributes( { cta_url: v } ) }
							help={ __( 'Par défaut, mène à la section Équipe plus bas dans la page.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Photo principale', 'adaptours' ) }>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setAttributes( { main_image: media.id } ) }
								allowedTypes={ [ 'image' ] }
								value={ attributes.main_image }
								render={ ( { open } ) => (
									<div>
										<Button variant="secondary" onClick={ open }>
											{ attributes.main_image
												? __( 'Changer la photo', 'adaptours' )
												: __( 'Choisir une photo', 'adaptours' ) }
										</Button>
										{ !! attributes.main_image && (
											<Button
												variant="link"
												isDestructive
												onClick={ () => setAttributes( { main_image: 0 } ) }
											>
												{ __( 'Retirer', 'adaptours' ) }
											</Button>
										) }
									</div>
								) }
							/>
						</MediaUploadCheck>
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
