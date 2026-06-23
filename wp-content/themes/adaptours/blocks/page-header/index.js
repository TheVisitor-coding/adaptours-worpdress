/**
 * Bloc adaptours/page-header — composant d'édition (côté éditeur). Archétype : plat-texte.
 *
 * Édition INLINE dans le canvas (choix client) : surtitre, titre (2 parties, le 2e en
 * orange), introduction (italique + lien) se saisissent directement sur le bloc via
 * RichText. Les liens des boutons se posent via les boutons « lien » de la barre
 * d'outils du bloc. Bloc dynamique : le rendu FRONT est dans render.php → save = null.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	BlockControls,
	__experimentalLinkControl as LinkControl,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'page-header' } );
		const [ editingLink, setEditingLink ] = useState( null ); // 'primary' | 'secondary' | null
		const urlKey = editingLink === 'primary' ? 'cta_primary_url' : 'cta_secondary_url';

		return (
			<>
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
					<div className="page-header__inner">
						<RichText
							tagName="p"
							className="page-header__eyebrow eyebrow"
							value={ attributes.eyebrow }
							allowedFormats={ [] }
							onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
							placeholder={ __( 'Surtitre (optionnel)', 'adaptours' ) }
						/>

						<h1 className="page-header__title">
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
						</h1>

						<RichText
							tagName="p"
							className="page-header__desc"
							value={ attributes.description }
							allowedFormats={ [ 'core/italic', 'core/link' ] }
							onChange={ ( description ) => setAttributes( { description } ) }
							placeholder={ __( 'Texte d’introduction…', 'adaptours' ) }
						/>

						<div className="page-header__cta">
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
				</section>
			</>
		);
	},
	save: () => null,
} );
