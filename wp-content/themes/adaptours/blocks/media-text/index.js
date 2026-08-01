/**
 * Bloc adaptours/media-text — composant d'édition (côté éditeur). Archétype : media-texte.
 *
 * Tous les champs s'éditent dans le panneau latéral (surtitre, titre bichrome, texte,
 * boutons, image, position, couleur de fond) ; le canvas affiche l'aperçu du rendu
 * serveur (ServerSideRender). Bloc dynamique : rendu FRONT dans render.php → save = null.
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
	SelectControl,
	Button,
	BaseControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

const BACKGROUND_OPTIONS = [
	{ label: __( 'Blanc cassé', 'adaptours' ), value: 'surface' },
	{ label: __( 'Beige', 'adaptours' ), value: 'surface-alt' },
	{ label: __( 'Pêche', 'adaptours' ), value: 'highlight-soft' },
];

// Le corps pouvait contenir du HTML (paragraphes de l'ancienne édition inline) :
// on l'affiche en texte simple dans le champ ; le HTML d'origine reste rendu tel quel
// tant que le champ n'est pas modifié.
const htmlToPlainText = ( html ) =>
	html
		? html
				.replace( /<\/p>\s*<p[^>]*>/gi, '\n\n' )
				.replace( /<br\s*\/?>/gi, '\n' )
				.replace( /<[^>]+>/g, '' )
				.replace( /&nbsp;/g, ' ' )
				.replace( /&#(\d+);/g, ( m, n ) => String.fromCharCode( n ) )
				.replace( /&amp;/g, '&' )
				.replace( /&lt;/g, '<' )
				.replace( /&gt;/g, '>' )
				.trim()
		: '';

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

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Titre', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							help={ __( 'Petit texte au-dessus du titre. Laissez vide pour aucun.', 'adaptours' ) }
							value={ attributes.eyebrow }
							onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
						/>
						<TextControl
							label={ __( 'Titre — début', 'adaptours' ) }
							value={ attributes.title_part_1 }
							onChange={ ( title_part_1 ) => setAttributes( { title_part_1 } ) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							help={ __( 'La fin du titre, affichée en orange.', 'adaptours' ) }
							value={ attributes.title_part_2 }
							onChange={ ( title_part_2 ) => setAttributes( { title_part_2 } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Texte', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Texte', 'adaptours' ) }
							help={ __( 'Laissez une ligne vide entre deux paragraphes.', 'adaptours' ) }
							value={ htmlToPlainText( attributes.body ) }
							onChange={ ( body ) => setAttributes( { body } ) }
							rows={ 8 }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Boutons', 'adaptours' ) }>
						<TextControl
							label={ __( 'Bouton principal — texte', 'adaptours' ) }
							help={ __( 'Laissez le texte vide pour masquer le bouton.', 'adaptours' ) }
							value={ attributes.cta_primary_label }
							onChange={ ( cta_primary_label ) => setAttributes( { cta_primary_label } ) }
						/>
						<TextControl
							label={ __( 'Bouton principal — adresse', 'adaptours' ) }
							type="url"
							help={ __( 'La page ouverte au clic (par exemple /devis/).', 'adaptours' ) }
							value={ attributes.cta_primary_url }
							onChange={ ( cta_primary_url ) => setAttributes( { cta_primary_url } ) }
						/>
						<TextControl
							label={ __( 'Bouton secondaire — texte', 'adaptours' ) }
							help={ __( 'Laissez le texte vide pour masquer le bouton.', 'adaptours' ) }
							value={ attributes.cta_secondary_label }
							onChange={ ( cta_secondary_label ) => setAttributes( { cta_secondary_label } ) }
						/>
						<TextControl
							label={ __( 'Bouton secondaire — adresse', 'adaptours' ) }
							type="url"
							help={ __( 'La page ouverte au clic.', 'adaptours' ) }
							value={ attributes.cta_secondary_url }
							onChange={ ( cta_secondary_url ) => setAttributes( { cta_secondary_url } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Image', 'adaptours' ) }>
						<MediaField
							label={ __( 'Image', 'adaptours' ) }
							help={ __( 'L’image affichée à côté du texte.', 'adaptours' ) }
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
					</PanelBody>
					<PanelBody title={ __( 'Mise en page', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Position de l’image', 'adaptours' ) }
							value={ attributes.media_position === 'left' ? 'left' : 'right' }
							options={ [
								{ label: __( 'À droite', 'adaptours' ), value: 'right' },
								{ label: __( 'À gauche', 'adaptours' ), value: 'left' },
							] }
							onChange={ ( media_position ) => setAttributes( { media_position } ) }
						/>
						<SelectControl
							label={ __( 'Couleur de fond', 'adaptours' ) }
							help={ __( 'La couleur derrière cette section.', 'adaptours' ) }
							value={ attributes.background }
							options={ BACKGROUND_OPTIONS }
							onChange={ ( background ) => setAttributes( { background } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						EmptyResponsePlaceholder={ () => (
							<p>{ __( 'Cette section est vide : remplissez les champs dans la colonne de droite.', 'adaptours' ) }</p>
						) }
					/>
				</div>
			</>
		);
	},
	save: () => null,
} );
