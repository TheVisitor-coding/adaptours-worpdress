/**
 * Bloc adaptours/page-header — composant d'édition (côté éditeur). Archétype : plat-texte.
 *
 * Tous les champs s'éditent dans le panneau latéral (surtitre, titre bichrome,
 * introduction, boutons) ; le canvas affiche l'aperçu du rendu serveur (ServerSideRender).
 * Bloc dynamique : le rendu FRONT est dans render.php → save = null.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

// L'introduction pouvait contenir du HTML (italique/lien de l'ancienne édition inline) :
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
		: '';

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
					<PanelBody title={ __( 'Texte d’introduction', 'adaptours' ) }>
						<TextareaControl
							label={ __( 'Introduction', 'adaptours' ) }
							help={ __( 'Quelques phrases affichées sous le titre. Laissez vide pour aucune.', 'adaptours' ) }
							value={ htmlToPlainText( attributes.description ) }
							onChange={ ( description ) => setAttributes( { description } ) }
							rows={ 4 }
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
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						EmptyResponsePlaceholder={ () => (
							<p>{ __( 'Cet en-tête est vide : remplissez les champs dans la colonne de droite.', 'adaptours' ) }</p>
						) }
					/>
				</div>
			</>
		);
	},
	save: () => null,
} );
