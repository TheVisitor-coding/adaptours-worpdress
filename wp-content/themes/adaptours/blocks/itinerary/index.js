/**
 * Bloc adaptours/itinerary — composant d'édition. Parent InnerBlocks.
 *
 * Header (surtitre / titre / description) édité dans le panneau latéral ; les étapes sont
 * des blocs enfants « adaptours/itinerary-step » (ajout / réordonnancement / suppression
 * via l'éditeur). Le rendu front est dans render.php : save n'émet que les étapes
 * (InnerBlocks.Content), render.php les enveloppe dans la liste + ajoute le header.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

const ALLOWED = [ 'adaptours/itinerary-step' ];
const TEMPLATE = [
	[ 'adaptours/itinerary-step' ],
	[ 'adaptours/itinerary-step' ],
	[ 'adaptours/itinerary-step' ],
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();
		const innerProps = useInnerBlocksProps(
			{ className: 'itinerary__list' },
			{
				allowedBlocks: ALLOWED,
				template: TEMPLATE,
				templateLock: false,
				orientation: 'vertical',
			}
		);

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
								'Le ou les mots du titre à mettre en orange (facultatif).',
								'adaptours'
							) }
						/>
						<TextareaControl
							label={ __( 'Description', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
							help={ __( 'Texte affiché à droite du titre.', 'adaptours' ) }
						/>
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<div className="itinerary__inner">
						<header className="itinerary__head">
							<div className="itinerary__intro">
								{ attributes.eyebrow && (
									<p className="itinerary__eyebrow">
										{ attributes.eyebrow }
									</p>
								) }
								{ ( attributes.title || attributes.title_accent ) && (
									<h2 className="itinerary__title">
										{ attributes.title }
										{ attributes.title_accent && (
											<span className="accent">
												{ ' ' }
												{ attributes.title_accent }
											</span>
										) }
									</h2>
								) }
							</div>
							{ attributes.description && (
								<p className="itinerary__desc">
									{ attributes.description }
								</p>
							) }
						</header>
						<ol { ...innerProps } />
					</div>
				</section>
			</>
		);
	},
	// Rendu dynamique : on ne persiste que les étapes ; render.php ajoute la liste + header.
	save: () => <InnerBlocks.Content />,
} );
