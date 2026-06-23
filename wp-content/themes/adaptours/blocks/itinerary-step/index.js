/**
 * Bloc adaptours/itinerary-step — composant d'édition d'une étape.
 *
 * Édition inline (ergonomie cliente) : titre + description en RichText, image via la
 * médiathèque. Le mot en orange se règle dans le panneau latéral. La numérotation « Jour N »
 * est automatique (compteur CSS), non éditable. Pas de style.scss propre : le layout est
 * porté par le bloc parent adaptours/itinerary.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'itinerary__step' } );
		const { title, title_accent: titleAccent, description, thumbnail_id: thumbnailId } = attributes;

		const media = useSelect(
			( select ) =>
				thumbnailId ? select( coreStore ).getMedia( thumbnailId ) : null,
			[ thumbnailId ]
		);
		const thumbUrl =
			media?.media_details?.sizes?.thumbnail?.source_url ||
			media?.source_url ||
			'';

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Étape', 'adaptours' ) }>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ titleAccent }
							onChange={ ( v ) => setAttributes( { title_accent: v } ) }
							help={ __(
								'Le ou les mots du titre de l’étape à mettre en orange (facultatif).',
								'adaptours'
							) }
						/>
					</PanelBody>
				</InspectorControls>

				<li { ...blockProps }>
					<span className="itinerary__badge" aria-hidden="true">
						<span className="itinerary__badge-label">
							{ __( 'Jour', 'adaptours' ) }
						</span>
						<span className="itinerary__badge-num" />
					</span>

					<div className="itinerary__content">
						<RichText
							tagName="h3"
							className="itinerary__step-title"
							value={ title }
							allowedFormats={ [] }
							onChange={ ( v ) => setAttributes( { title: v } ) }
							placeholder={ __( 'Titre de l’étape…', 'adaptours' ) }
						/>
						<RichText
							tagName="p"
							className="itinerary__step-desc"
							value={ description }
							allowedFormats={ [] }
							onChange={ ( v ) => setAttributes( { description: v } ) }
							placeholder={ __( 'Que se passe-t-il ce jour-là ?', 'adaptours' ) }
						/>
					</div>

					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ thumbnailId }
							onSelect={ ( m ) => setAttributes( { thumbnail_id: m.id } ) }
							render={ ( { open } ) => (
								<button
									type="button"
									className="itinerary__thumb itinerary__thumb--button"
									onClick={ open }
								>
									{ thumbUrl ? (
										<img src={ thumbUrl } alt="" />
									) : (
										<span>{ __( 'Image', 'adaptours' ) }</span>
									) }
								</button>
							) }
						/>
					</MediaUploadCheck>
				</li>
			</>
		);
	},
	save: () => null,
} );
