/**
 * Bloc adaptours/section-promise — composant d'édition (« Le voyage, pensé autour de vous »).
 * Rendu front = render.php. Édition via Inspector + aperçu ServerSideRender (titre bichrome,
 * atouts illustrés et collage rendus côté serveur).
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	Button,
	BaseControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

const MediaField = ( { label, help, value, onChange } ) => (
	<BaseControl label={ label } help={ help } __nextHasNoMarginBottom>
		<div>
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ ( media ) => onChange( media.id ) }
					allowedTypes={ [ 'image' ] }
					value={ value }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ value
								? __( 'Changer l’image', 'adaptours' )
								: __( 'Choisir une image', 'adaptours' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ !! value && (
				<Button variant="link" isDestructive onClick={ () => onChange( 0 ) }>
					{ __( 'Retirer', 'adaptours' ) }
				</Button>
			) }
		</div>
	</BaseControl>
);

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
							help={ __( 'La fin du titre, mise en avant en orange.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Texte', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Texte de présentation', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
							rows={ 8 }
							help={ __( 'Laissez une ligne vide entre deux paragraphes.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Images', 'adaptours' ) }>
						<MediaField
							label={ __( 'Grande image', 'adaptours' ) }
							help={ __( 'La grande carte du collage, à gauche.', 'adaptours' ) }
							value={ attributes.image_main }
							onChange={ ( id ) => setAttributes( { image_main: id } ) }
						/>
						<MediaField
							label={ __( 'Petite image', 'adaptours' ) }
							help={ __( 'La petite carte posée par-dessus, en bas à droite.', 'adaptours' ) }
							value={ attributes.image_inset }
							onChange={ ( id ) => setAttributes( { image_inset: id } ) }
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
