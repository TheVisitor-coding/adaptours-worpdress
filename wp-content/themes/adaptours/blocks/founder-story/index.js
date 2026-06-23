/**
 * Bloc adaptours/founder-story — composant d'édition (récit de la fondatrice).
 * Rendu front = render.php. Édition via Inspector + aperçu ServerSideRender
 * (titre/citation bichromes et polaroïds rendus côté serveur).
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
					<PanelBody title={ __( 'Titre', 'adaptours' ) }>
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
					</PanelBody>
					<PanelBody title={ __( 'Texte', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Paragraphe d’introduction', 'adaptours' ) }
							value={ attributes.intro }
							onChange={ ( v ) => setAttributes( { intro: v } ) }
							rows={ 3 }
						/>
						<TextControl
							label={ __( 'Citation — début', 'adaptours' ) }
							value={ attributes.quote_part_1 }
							onChange={ ( v ) => setAttributes( { quote_part_1: v } ) }
						/>
						<TextControl
							label={ __( 'Citation — fin en orange', 'adaptours' ) }
							value={ attributes.quote_part_2 }
							onChange={ ( v ) => setAttributes( { quote_part_2: v } ) }
							help={ __( 'Derniers mots de la citation, mis en avant.', 'adaptours' ) }
						/>
						<TextareaControl
							label={ __( 'Paragraphe de conclusion', 'adaptours' ) }
							value={ attributes.outro }
							onChange={ ( v ) => setAttributes( { outro: v } ) }
							rows={ 3 }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Signature', 'adaptours' ) }>
						<TextControl
							label={ __( 'Nom', 'adaptours' ) }
							value={ attributes.signature_name }
							onChange={ ( v ) => setAttributes( { signature_name: v } ) }
						/>
						<TextControl
							label={ __( 'Rôle', 'adaptours' ) }
							value={ attributes.signature_role }
							onChange={ ( v ) => setAttributes( { signature_role: v } ) }
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
