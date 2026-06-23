/**
 * Bloc adaptours/team-intro — composant d'édition (présentation de l'équipe).
 * Rendu front = render.php. Édition via Inspector + aperçu ServerSideRender.
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
					<PanelBody title={ __( 'Texte', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							value={ attributes.eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
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
						/>
						<TextareaControl
							label={ __( 'Paragraphe 1', 'adaptours' ) }
							value={ attributes.paragraph_1 }
							onChange={ ( v ) => setAttributes( { paragraph_1: v } ) }
							rows={ 4 }
						/>
						<TextareaControl
							label={ __( 'Paragraphe 2', 'adaptours' ) }
							value={ attributes.paragraph_2 }
							onChange={ ( v ) => setAttributes( { paragraph_2: v } ) }
							rows={ 4 }
						/>
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
							help={ __( 'Laissez vide pour aller vers la page « Qui sommes-nous ».', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Visuel', 'adaptours' ) }>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setAttributes( { main_image: media.id } ) }
								allowedTypes={ [ 'image' ] }
								value={ attributes.main_image }
								render={ ( { open } ) => (
									<div>
										<Button variant="secondary" onClick={ open }>
											{ attributes.main_image
												? __( 'Changer le visuel', 'adaptours' )
												: __( 'Choisir un visuel', 'adaptours' ) }
										</Button>
										{ !! attributes.main_image && (
											<Button variant="link" isDestructive onClick={ () => setAttributes( { main_image: 0 } ) }>
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
