/**
 * Bloc adaptours/content-storytelling — composant d'édition (texte + 3 polaroïds).
 * Rendu front = render.php. Édition via Inspector (textes + 3 photos) + aperçu ServerSideRender.
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
import { __, sprintf } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

const PhotoControl = ( { n, value, setAttributes } ) => (
	<MediaUploadCheck>
		<MediaUpload
			onSelect={ ( media ) => setAttributes( { [ `photo_${ n }` ]: media.id } ) }
			allowedTypes={ [ 'image' ] }
			value={ value }
			render={ ( { open } ) => (
				<div style={ { marginBottom: '12px' } }>
					<Button variant="secondary" onClick={ open }>
						{ value
							? sprintf( /* translators: %d = numéro de photo. */ __( 'Changer la photo %d', 'adaptours' ), n )
							: sprintf( __( 'Choisir la photo %d', 'adaptours' ), n ) }
					</Button>
					{ !! value && (
						<Button variant="link" isDestructive onClick={ () => setAttributes( { [ `photo_${ n }` ]: 0 } ) }>
							{ __( 'Retirer', 'adaptours' ) }
						</Button>
					) }
				</div>
			) }
		/>
	</MediaUploadCheck>
);

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Texte', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre (facultatif)', 'adaptours' ) }
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
							label={ __( 'Texte', 'adaptours' ) }
							value={ attributes.body }
							onChange={ ( v ) => setAttributes( { body: v } ) }
							rows={ 6 }
							help={ __( 'Laissez une ligne vide entre deux paragraphes.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Photos', 'adaptours' ) }>
						<PhotoControl n={ 1 } value={ attributes.photo_1 } setAttributes={ setAttributes } />
						<PhotoControl n={ 2 } value={ attributes.photo_2 } setAttributes={ setAttributes } />
						<PhotoControl n={ 3 } value={ attributes.photo_3 } setAttributes={ setAttributes } />
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
