/**
 * Bloc adaptours/card-grid-card — composant d'édition d'une carte illustrée.
 *
 * Image choisie en place ; titre + texte édités INLINE ; lien posé via la barre d'outils.
 * Pas de style.scss propre : le layout est porté par le bloc parent adaptours/card-grid.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	BlockControls,
	MediaPlaceholder,
	MediaReplaceFlow,
	__experimentalLinkControl as LinkControl,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( {
			className: `card-grid__card${ attributes.url ? ' card-grid__card--linked' : '' }`,
		} );
		const [ editingLink, setEditingLink ] = useState( false );

		const onSelectImage = ( media ) =>
			setAttributes( {
				image_id: media.id,
				image_url: media.url,
				image_alt: media.alt || attributes.image_alt,
			} );

		return (
			<>
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							icon="admin-links"
							label={ __( 'Lien de la carte', 'adaptours' ) }
							onClick={ () => setEditingLink( true ) }
							isActive={ !! attributes.url }
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
					<Popover onClose={ () => setEditingLink( false ) }>
						<LinkControl
							value={ { url: attributes.url } }
							onChange={ ( next ) => setAttributes( { url: ( next && next.url ) || '' } ) }
						/>
					</Popover>
				) }

				<li { ...blockProps }>
					<div className="card-grid__media">
						{ attributes.image_url ? (
							<img className="card-grid__img" src={ attributes.image_url } alt={ attributes.image_alt } />
						) : (
							<MediaPlaceholder
								icon="format-image"
								labels={ { title: __( 'Image', 'adaptours' ) } }
								allowedTypes={ [ 'image' ] }
								onSelect={ onSelectImage }
							/>
						) }
						<span className="card-grid__scrim" aria-hidden="true" />
						<div className="card-grid__caption">
							<RichText
								tagName="h3"
								className="card-grid__card-title"
								value={ attributes.card_title }
								allowedFormats={ [] }
								onChange={ ( card_title ) => setAttributes( { card_title } ) }
								placeholder={ __( 'Titre', 'adaptours' ) }
							/>
							<RichText
								tagName="p"
								className="card-grid__card-text"
								value={ attributes.text }
								allowedFormats={ [] }
								onChange={ ( text ) => setAttributes( { text } ) }
								placeholder={ __( 'Texte court…', 'adaptours' ) }
							/>
						</div>
					</div>
				</li>
			</>
		);
	},
	save: () => null,
} );
