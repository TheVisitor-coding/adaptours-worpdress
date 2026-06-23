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
					<PanelBody title={ __( 'En-tête', 'adaptours' ) }>
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
						<TextareaControl
							label={ __( 'Texte d’introduction', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Photo de fond', 'adaptours' ) }>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => setAttributes( { hero_image: media.id } ) }
								allowedTypes={ [ 'image' ] }
								value={ attributes.hero_image }
								render={ ( { open } ) => (
									<div>
										<Button variant="secondary" onClick={ open }>
											{ attributes.hero_image
												? __( 'Changer la photo', 'adaptours' )
												: __( 'Choisir une photo', 'adaptours' ) }
										</Button>
										{ !! attributes.hero_image && (
											<Button
												variant="link"
												isDestructive
												onClick={ () => setAttributes( { hero_image: 0 } ) }
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
