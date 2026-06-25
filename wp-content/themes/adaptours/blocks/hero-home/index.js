/**
 * Bloc adaptours/hero-home — composant d'édition (en-tête page d'accueil).
 * Rendu front = render.php. Édition via Inspector + aperçu ServerSideRender
 * (le titre bichrome, le trust strip et le collage sont rendus côté serveur).
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

// Sélecteur d'image d'une vignette (mémorise l'ID, aperçu rendu côté serveur).
const PolaroidControl = ( { index, value, onChange } ) => (
	<MediaUploadCheck>
		<MediaUpload
			onSelect={ ( media ) => onChange( media.id ) }
			allowedTypes={ [ 'image' ] }
			value={ value }
			render={ ( { open } ) => (
				<div style={ { marginBottom: '12px' } }>
					<p style={ { margin: '0 0 4px' } }>
						{ sprintf( __( 'Vignette %d', 'adaptours' ), index ) }
					</p>
					<Button variant="secondary" onClick={ open }>
						{ value
							? __( 'Changer la photo', 'adaptours' )
							: __( 'Choisir une photo', 'adaptours' ) }
					</Button>
					{ !! value && (
						<Button variant="link" isDestructive onClick={ () => onChange( 0 ) }>
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
							help={ __( 'Le ou les mots du titre mis en avant.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Ligne manuscrite — début', 'adaptours' ) }
							value={ attributes.title_script }
							onChange={ ( v ) => setAttributes( { title_script: v } ) }
							help={ __( 'Texte manuscrit avant les destinations qui défilent (ex. « d’ici »). Laissez vide pour masquer la ligne.', 'adaptours' ) }
						/>
						<TextareaControl
							label={ __( 'Destinations qui défilent (une par ligne)', 'adaptours' ) }
							value={ attributes.rotator_words }
							onChange={ ( v ) => setAttributes( { rotator_words: v } ) }
							help={ __( 'La première s’affiche, les suivantes défilent en boucle (ex. « jusqu’à Bali », « jusqu’au Kenya »). Laissez une seule ligne pour ne rien faire défiler.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Introduction', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Texte d’introduction', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Boutons', 'adaptours' ) }>
						<TextControl
							label={ __( 'Bouton principal — texte', 'adaptours' ) }
							value={ attributes.cta_primary_label }
							onChange={ ( v ) => setAttributes( { cta_primary_label: v } ) }
						/>
						<TextControl
							label={ __( 'Bouton principal — lien', 'adaptours' ) }
							value={ attributes.cta_primary_url }
							onChange={ ( v ) => setAttributes( { cta_primary_url: v } ) }
							help={ __( 'Laissez vide pour pointer vers la page Devis.', 'adaptours' ) }
						/>
						<TextControl
							label={ __( 'Bouton secondaire — texte', 'adaptours' ) }
							value={ attributes.cta_secondary_label }
							onChange={ ( v ) => setAttributes( { cta_secondary_label: v } ) }
						/>
						<TextControl
							label={ __( 'Bouton secondaire — lien', 'adaptours' ) }
							value={ attributes.cta_secondary_url }
							onChange={ ( v ) => setAttributes( { cta_secondary_url: v } ) }
							help={ __( 'Laissez vide pour pointer vers la liste des destinations.', 'adaptours' ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Vignettes (collage)', 'adaptours' ) } initialOpen={ false }>
						<p style={ { marginTop: 0 } }>
							{ __( 'Les 4 photos du collage décoratif autour du titre. Affichées sur grand écran uniquement ; laissées vides, un fond de remplacement s’affiche.', 'adaptours' ) }
						</p>
						{ [ 1, 2, 3, 4 ].map( ( n ) => (
							<PolaroidControl
								key={ n }
								index={ n }
								value={ attributes[ `polaroid_${ n }` ] }
								onChange={ ( id ) => setAttributes( { [ `polaroid_${ n }` ]: id } ) }
							/>
						) ) }
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
