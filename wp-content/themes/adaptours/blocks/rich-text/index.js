/**
 * Bloc adaptours/rich-text — composant d'édition (côté éditeur). Archétype : prose éditoriale.
 *
 * Hybride : l'en-tête (surtitre + titre bichrome) et la couleur de fond s'éditent dans
 * le panneau latéral (le canvas en affiche un aperçu statique) ; le corps reste une zone
 * libre InnerBlocks restreinte aux blocs de texte natifs (paragraphe, sous-titre, liste)
 * → édition « comme un traitement de texte ». Bloc dynamique : render.php enveloppe le
 * corps (clé "style"). save = <InnerBlocks.Content/>.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// NE PAS retirer : déclenche la compilation du SCSS en 'style-index.css' (clé "style").
import './style.scss';

const ALLOWED = [ 'core/paragraph', 'core/heading', 'core/list' ];
const TEMPLATE = [
	[ 'core/paragraph', { placeholder: __( 'Votre texte…', 'adaptours' ) } ],
];

const BACKGROUND_OPTIONS = [
	{ label: __( 'Blanc cassé', 'adaptours' ), value: 'surface' },
	{ label: __( 'Beige', 'adaptours' ), value: 'surface-alt' },
	{ label: __( 'Pêche', 'adaptours' ), value: 'highlight-soft' },
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const background = BACKGROUND_OPTIONS.some( ( o ) => o.value === attributes.background )
			? attributes.background
			: 'surface';
		const blockProps = useBlockProps( {
			className: `rich-text rich-text--bg-${ background }`,
		} );
		const hasTitle = !! ( attributes.title_part_1 || attributes.title_part_2 );

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
					<PanelBody title={ __( 'Mise en page', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Couleur de fond', 'adaptours' ) }
							help={ __( 'La couleur derrière cette section.', 'adaptours' ) }
							value={ background }
							options={ BACKGROUND_OPTIONS }
							onChange={ ( v ) => setAttributes( { background: v } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<div className="rich-text__inner">
						{ ( attributes.eyebrow || hasTitle ) && (
							<header className="rich-text__head">
								{ !! attributes.eyebrow && (
									<p className="rich-text__eyebrow eyebrow">{ attributes.eyebrow }</p>
								) }
								{ hasTitle && (
									<h2 className="rich-text__title">
										{ attributes.title_part_1 }{ ' ' }
										<span className="accent">{ attributes.title_part_2 }</span>
									</h2>
								) }
							</header>
						) }

						<div className="rich-text__body">
							<InnerBlocks
								allowedBlocks={ ALLOWED }
								template={ TEMPLATE }
								templateLock={ false }
							/>
						</div>
					</div>
				</section>
			</>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
