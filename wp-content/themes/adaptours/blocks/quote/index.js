/**
 * Bloc adaptours/quote — composant d'édition (côté éditeur). Archétype : plat-texte.
 *
 * Citation et auteur édités INLINE via RichText. L'extrait à souligner en orange se
 * recopie dans un petit champ du panneau latéral (mis en avant par adaptours_bichrome
 * côté rendu). Bloc dynamique : rendu FRONT dans render.php → save = null.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'quote' } );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Mise en avant', 'adaptours' ) }>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							help={ __( 'Recopiez ici un court extrait de la citation à souligner. Laissez vide pour aucun.', 'adaptours' ) }
							value={ attributes.quote_accent }
							onChange={ ( quote_accent ) => setAttributes( { quote_accent } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<figure className="quote__inner">
						<span className="quote__mark" aria-hidden="true">&ldquo;</span>
						<blockquote className="quote__text">
							<RichText
								tagName="p"
								value={ attributes.quote }
								allowedFormats={ [] }
								onChange={ ( quote ) => setAttributes( { quote } ) }
								placeholder={ __( 'Votre citation…', 'adaptours' ) }
							/>
						</blockquote>
						<RichText
							tagName="figcaption"
							className="quote__author"
							value={ attributes.author }
							allowedFormats={ [] }
							onChange={ ( author ) => setAttributes( { author } ) }
							placeholder={ __( 'Auteur — contexte', 'adaptours' ) }
						/>
					</figure>
				</section>
			</>
		);
	},
	save: () => null,
} );
