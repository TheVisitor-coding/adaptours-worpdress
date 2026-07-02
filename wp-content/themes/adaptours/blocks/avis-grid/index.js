import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	ComboboxControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// Importé pour que wp-scripts compile le SCSS en `style-index.css` (style front +
// éditeur, référencé par la clé "style" du block.json compilé).
import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();

		// Liste des avis publiés pour le picker « avis du mois ».
		const avisRecords = useSelect(
			( select ) =>
				select( coreStore ).getEntityRecords( 'postType', 'avis', {
					per_page: -1,
					status: 'publish',
					_fields: 'id,title',
				} ),
			[]
		);

		const options = [
			{
				value: '0',
				label: __( 'Automatique (avis du mois)', 'adaptours' ),
			},
			...( avisRecords || [] ).map( ( a ) => ( {
				value: String( a.id ),
				label: a.title?.rendered || `#${ a.id }`,
			} ) ),
		];

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Textes', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							value={ attributes.eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
							help={ __( 'Petit texte au-dessus du titre.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Titre', 'adaptours' ) }
							value={ attributes.title }
							onChange={ ( v ) => setAttributes( { title: v } ) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ attributes.title_accent }
							onChange={ ( v ) => setAttributes( { title_accent: v } ) }
							help={ __(
								'Le ou les mots du titre à mettre en orange.',
								'adaptours'
							) }
						/>
						<TextareaControl
							label={ __( 'Description', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
							help={ __( 'Texte d’introduction, à droite du titre.', 'adaptours' ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Avis du mois', 'adaptours' ) }>
						<ComboboxControl
							label={ __( 'Avis affiché en grand', 'adaptours' ) }
							value={ String( attributes.featured_avis_id || 0 ) }
							options={ options }
							onChange={ ( v ) =>
								setAttributes( {
									featured_avis_id: v ? Number( v ) : 0,
								} )
							}
							help={ __(
								'Sur « Automatique », c’est l’avis marqué « avis du mois » qui s’affiche.',
								'adaptours'
							) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Encart du bas', 'adaptours' ) }>
						<TextControl
							label={ __( 'Texte', 'adaptours' ) }
							value={ attributes.band_text }
							onChange={ ( v ) => setAttributes( { band_text: v } ) }
							help={ __( 'Phrase affichée en bas de la section.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Texte du bouton', 'adaptours' ) }
							value={ attributes.cta_label }
							onChange={ ( v ) => setAttributes( { cta_label: v } ) }
						/>
						<TextControl
							label={ __( 'Lien du bouton', 'adaptours' ) }
							type="url"
							value={ attributes.cta_url }
							onChange={ ( v ) => setAttributes( { cta_url: v } ) }
							help={ __(
								'Laissez vide pour aller vers la page Devis.',
								'adaptours'
							) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
					/>
				</div>
			</>
		);
	},
	save: () => null,
} );
