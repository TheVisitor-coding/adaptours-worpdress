/**
 * Bloc adaptours/card-grid-card — composant d'édition d'une carte illustrée.
 *
 * Tous les champs s'éditent dans le panneau latéral (image, titre, texte, lien) ; le
 * canvas affiche un aperçu statique de la carte. L'aperçu a besoin de l'URL de l'image :
 * le champ image met à jour image_id + image_url + image_alt.
 * Pas de style.scss propre : le layout est porté par le bloc parent adaptours/card-grid.
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
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( {
			className: `card-grid__card${ attributes.url ? ' card-grid__card--linked' : '' }`,
		} );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Carte', 'adaptours' ) }>
						<BaseControl
							label={ __( 'Image', 'adaptours' ) }
							help={ __( 'La photo qui remplit la carte.', 'adaptours' ) }
							__nextHasNoMarginBottom
						>
							<div>
								<MediaUploadCheck>
									<MediaUpload
										onSelect={ ( media ) =>
											setAttributes( {
												image_id: media.id,
												image_url: media.url,
												image_alt: media.alt || attributes.image_alt,
											} )
										}
										allowedTypes={ [ 'image' ] }
										value={ attributes.image_id }
										render={ ( { open } ) => (
											<Button variant="secondary" onClick={ open }>
												{ attributes.image_id
													? __( 'Changer l’image', 'adaptours' )
													: __( 'Choisir une image', 'adaptours' ) }
											</Button>
										) }
									/>
								</MediaUploadCheck>
								{ !! attributes.image_id && (
									<Button
										variant="link"
										isDestructive
										onClick={ () => setAttributes( { image_id: 0, image_url: '' } ) }
									>
										{ __( 'Retirer', 'adaptours' ) }
									</Button>
								) }
							</div>
						</BaseControl>
						<TextareaControl
							label={ __( 'Description de l’image', 'adaptours' ) }
							help={ __( 'Décrivez l’image en quelques mots (pour l’accessibilité).', 'adaptours' ) }
							value={ attributes.image_alt }
							onChange={ ( image_alt ) => setAttributes( { image_alt } ) }
							rows={ 2 }
						/>
						<TextControl
							label={ __( 'Titre', 'adaptours' ) }
							value={ attributes.card_title }
							onChange={ ( card_title ) => setAttributes( { card_title } ) }
						/>
						<TextControl
							label={ __( 'Texte court', 'adaptours' ) }
							help={ __( 'Une phrase affichée sous le titre. Laissez vide pour aucune.', 'adaptours' ) }
							value={ attributes.text }
							onChange={ ( text ) => setAttributes( { text } ) }
						/>
						<TextControl
							label={ __( 'Lien de la carte', 'adaptours' ) }
							type="url"
							help={ __( 'La page ouverte au clic sur la carte. Laissez vide pour une carte non cliquable.', 'adaptours' ) }
							value={ attributes.url }
							onChange={ ( url ) => setAttributes( { url } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<li { ...blockProps }>
					<div className={ `card-grid__media${ attributes.image_url ? '' : ' card-grid__media--placeholder' }` }>
						{ !! attributes.image_url && (
							<img
								className="card-grid__img"
								src={ attributes.image_url }
								alt={ attributes.image_alt }
							/>
						) }
						<span className="card-grid__scrim" aria-hidden="true" />
						<div className="card-grid__caption">
							<h3 className="card-grid__card-title">
								{ attributes.card_title || __( 'Titre de la carte', 'adaptours' ) }
							</h3>
							{ !! attributes.text && (
								<p className="card-grid__card-text">{ attributes.text }</p>
							) }
						</div>
					</div>
				</li>
			</>
		);
	},
	save: () => null,
} );
