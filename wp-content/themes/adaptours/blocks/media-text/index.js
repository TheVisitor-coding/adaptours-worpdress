/**
 * Bloc adaptours/media-text — composant d'édition (côté éditeur). Archétype : media-texte.
 *
 * Texte édité INLINE (RichText : surtitre, titre bichrome, corps multi-paragraphes,
 * libellés de boutons). Image choisie en place (MediaPlaceholder / Remplacer). Liens des
 * boutons via la barre d'outils. Position de l'image + texte alternatif dans le panneau
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
	__experimentalLinkControl as LinkControl,
} from '@wordpress/block-editor';
import {
	ToolbarGroup,
	ToolbarButton,
	Popover,
	PanelBody,
	SelectControl,
	TextareaControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const position = attributes.media_position === 'left' ? 'left' : 'right';
		const blockProps = useBlockProps( {
			className: `media-text media-text--media-${ position }`,
		} );
		const [ editingLink, setEditingLink ] = useState( null );
		const urlKey = editingLink === 'primary' ? 'cta_primary_url' : 'cta_secondary_url';

		const onSelectImage = ( media ) =>
			setAttributes( {
				image_id: media.id,
				image_url: media.url,
				image_alt: media.alt || attributes.image_alt,
			} );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Mise en page', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Position de l’image', 'adaptours' ) }
							value={ position }
							options={ [
								{ label: __( 'À droite', 'adaptours' ), value: 'right' },
								{ label: __( 'À gauche', 'adaptours' ), value: 'left' },
							] }
							onChange={ ( media_position ) => setAttributes( { media_position } ) }
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

				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							icon="admin-links"
							label={ __( 'Lien du bouton principal', 'adaptours' ) }
							onClick={ () => setEditingLink( 'primary' ) }
							isActive={ !! attributes.cta_primary_url }
						/>
						<ToolbarButton
							icon="admin-links"
							label={ __( 'Lien du bouton secondaire', 'adaptours' ) }
							onClick={ () => setEditingLink( 'secondary' ) }
							isActive={ !! attributes.cta_secondary_url }
						/>
					</ToolbarGroup>
					{ !! attributes.image_url && (
						<MediaReplaceFlow
							mediaId={ attributes.image_id }
							mediaURL={ attributes.image_url }
							allowedTypes={ [ 'image' ] }
							onSelect={ onSelectImage }
						/>
					) }
				</BlockControls>

				{ editingLink && (
					<Popover onClose={ () => setEditingLink( null ) }>
						<LinkControl
							value={ { url: attributes[ urlKey ] } }
							onChange={ ( next ) => setAttributes( { [ urlKey ]: ( next && next.url ) || '' } ) }
						/>
					</Popover>
				) }

				<section { ...blockProps }>
					<div className="media-text__inner">
						<div className="media-text__body">
							<RichText
								tagName="p"
								className="media-text__eyebrow eyebrow"
								value={ attributes.eyebrow }
								allowedFormats={ [] }
								onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
								placeholder={ __( 'Surtitre (optionnel)', 'adaptours' ) }
							/>
							<h2 className="media-text__title">
								<RichText
									tagName="span"
									value={ attributes.title_part_1 }
									allowedFormats={ [] }
									onChange={ ( title_part_1 ) => setAttributes( { title_part_1 } ) }
									placeholder={ __( 'Titre…', 'adaptours' ) }
								/>{ ' ' }
								<RichText
									tagName="span"
									className="accent"
									value={ attributes.title_part_2 }
									allowedFormats={ [] }
									onChange={ ( title_part_2 ) => setAttributes( { title_part_2 } ) }
									placeholder={ __( 'mot(s) en orange', 'adaptours' ) }
								/>
							</h2>
							<RichText
								tagName="div"
								className="media-text__text"
								multiline="p"
								value={ attributes.body }
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
								onChange={ ( body ) => setAttributes( { body } ) }
								placeholder={ __( 'Votre texte…', 'adaptours' ) }
							/>
							<div className="media-text__cta">
								<RichText
									tagName="span"
									className="button button--primary"
									value={ attributes.cta_primary_label }
									allowedFormats={ [] }
									onChange={ ( cta_primary_label ) => setAttributes( { cta_primary_label } ) }
									placeholder={ __( 'Bouton principal', 'adaptours' ) }
								/>
								<RichText
									tagName="span"
									className="button button--secondary"
									value={ attributes.cta_secondary_label }
									allowedFormats={ [] }
									onChange={ ( cta_secondary_label ) => setAttributes( { cta_secondary_label } ) }
									placeholder={ __( 'Bouton secondaire', 'adaptours' ) }
								/>
							</div>
						</div>

						<figure className="media-text__media">
							{ attributes.image_url ? (
								<img
									className="media-text__img"
									src={ attributes.image_url }
									alt={ attributes.image_alt }
								/>
							) : (
								<MediaPlaceholder
									icon="format-image"
									labels={ { title: __( 'Image', 'adaptours' ) } }
									allowedTypes={ [ 'image' ] }
									onSelect={ onSelectImage }
								/>
							) }
						</figure>
					</div>
				</section>
			</>
		);
	},
	save: () => null,
} );
