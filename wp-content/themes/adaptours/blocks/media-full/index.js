/**
 * Bloc adaptours/media-full — composant d'édition (côté éditeur). Archétype : média.
 *
 * Tous les champs s'éditent dans le panneau latéral (image, largeur, description,
 * légende) ; le canvas affiche l'aperçu du rendu serveur, ou une invite tant
 * qu'aucune image n'est choisie (render.php ne rend rien sans image).
 * Bloc dynamique : rendu FRONT dans render.php → save = null.
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
	SelectControl,
	TextControl,
	TextareaControl,
	Button,
	BaseControl,
	Placeholder,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
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
		const width = attributes.width === 'boxed' ? 'boxed' : 'full-bleed';

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Image', 'adaptours' ) }>
						<MediaField
							label={ __( 'Image', 'adaptours' ) }
							help={ __( 'La grande image affichée par cette section.', 'adaptours' ) }
							value={ attributes.image_id }
							onChange={ ( image_id ) => setAttributes( { image_id } ) }
						/>
						<TextareaControl
							label={ __( 'Description de l’image', 'adaptours' ) }
							help={ __( 'Décrivez l’image en quelques mots (pour l’accessibilité).', 'adaptours' ) }
							value={ attributes.image_alt }
							onChange={ ( image_alt ) => setAttributes( { image_alt } ) }
							rows={ 2 }
						/>
						<TextControl
							label={ __( 'Légende', 'adaptours' ) }
							help={ __( 'Petit texte affiché en bas de l’image. Laissez vide pour aucune.', 'adaptours' ) }
							value={ attributes.caption }
							onChange={ ( caption ) => setAttributes( { caption } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Mise en page', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Largeur', 'adaptours' ) }
							value={ width }
							options={ [
								{ label: __( 'Pleine largeur', 'adaptours' ), value: 'full-bleed' },
								{ label: __( 'Cadrée', 'adaptours' ), value: 'boxed' },
							] }
							onChange={ ( w ) => setAttributes( { width: w } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					{ attributes.image_id > 0 ? (
						<ServerSideRender block={ metadata.name } attributes={ attributes } />
					) : (
						<Placeholder
							icon="format-image"
							label={ __( 'Image en grand', 'adaptours' ) }
							instructions={ __( 'Choisissez une image dans la colonne de droite.', 'adaptours' ) }
						/>
					) }
				</div>
			</>
		);
	},
	save: () => null,
} );
