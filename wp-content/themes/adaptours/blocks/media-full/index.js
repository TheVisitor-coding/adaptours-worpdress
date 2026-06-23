/**
 * Bloc adaptours/media-full — composant d'édition (côté éditeur). Archétype : média.
 *
 * Image choisie en place (MediaPlaceholder / Remplacer), légende éditée INLINE sous
 * l'image. Largeur (pleine / cadrée) et description (accessibilité) dans le panneau
 * latéral. Bloc dynamique : rendu FRONT dans render.php → save = null.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	BlockControls,
	InspectorControls,
	MediaPlaceholder,
	MediaReplaceFlow,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const width = attributes.width === 'boxed' ? 'boxed' : 'full-bleed';
		const blockProps = useBlockProps( { className: `media-full media-full--${ width }` } );

		const onSelectImage = ( media ) =>
			setAttributes( {
				image_id: media.id,
				image_url: media.url,
				image_alt: media.alt || attributes.image_alt,
			} );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Image', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Largeur', 'adaptours' ) }
							value={ width }
							options={ [
								{ label: __( 'Pleine largeur', 'adaptours' ), value: 'full-bleed' },
								{ label: __( 'Cadrée', 'adaptours' ), value: 'boxed' },
							] }
							onChange={ ( w ) => setAttributes( { width: w } ) }
						/>
						<TextareaControl
							label={ __( 'Description de l’image', 'adaptours' ) }
							help={ __( 'Décrivez l’image en quelques mots (pour l’accessibilité).', 'adaptours' ) }
							value={ attributes.image_alt }
							onChange={ ( image_alt ) => setAttributes( { image_alt } ) }
							rows={ 2 }
						/>
					</PanelBody>
				</InspectorControls>

				{ !! attributes.image_url && (
					<BlockControls>
						<MediaReplaceFlow
							mediaId={ attributes.image_id }
							mediaURL={ attributes.image_url }
							allowedTypes={ [ 'image' ] }
							onSelect={ onSelectImage }
						/>
					</BlockControls>
				) }

				<section { ...blockProps }>
					<figure className="media-full__figure">
						{ attributes.image_url ? (
							<>
								<img
									className="media-full__img"
									src={ attributes.image_url }
									alt={ attributes.image_alt }
								/>
								<RichText
									tagName="figcaption"
									className="media-full__caption"
									value={ attributes.caption }
									allowedFormats={ [] }
									onChange={ ( caption ) => setAttributes( { caption } ) }
									placeholder={ __( 'Légende (optionnelle)', 'adaptours' ) }
								/>
							</>
						) : (
							<MediaPlaceholder
								icon="format-image"
								labels={ { title: __( 'Image', 'adaptours' ) } }
								allowedTypes={ [ 'image' ] }
								onSelect={ onSelectImage }
							/>
						) }
					</figure>
				</section>
			</>
		);
	},
	save: () => null,
} );
