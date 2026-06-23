/**
 * Bloc adaptours/team-grid — composant d'édition (section équipe, parent InnerBlocks).
 *
 * En-tête (surtitre / titre bichrome / description) et bande basse édités INLINE via RichText ;
 * les personnes sont des blocs enfants « adaptours/team-grid-member ». Le lien du bouton se
 * règle dans le panneau latéral. save n'émet que les membres ; render.php les enveloppe.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	useInnerBlocksProps,
	RichText,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

const ALLOWED = [ 'adaptours/team-grid-member' ];
const TEMPLATE = [
	[ 'adaptours/team-grid-member', { name: 'Prénom Nom', role: 'Fondatrice', tagline: 'a posé la première pierre' } ],
	[ 'adaptours/team-grid-member', { name: 'Prénom Nom', role: 'Logistique', tagline: 'expert logistique fauteuil' } ],
	[ 'adaptours/team-grid-member', { name: 'Prénom Nom', role: 'Sur place', tagline: 'parle balinais' } ],
	[ 'adaptours/team-grid-member', { name: 'Prénom Nom', role: 'Destinations', tagline: 'trouve les pépites' } ],
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'team-grid' } );
		const innerProps = useInnerBlocksProps(
			{ className: 'team-grid__grid' },
			{
				allowedBlocks: ALLOWED,
				template: TEMPLATE,
				templateLock: false,
				orientation: 'horizontal',
			}
		);

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Bouton « on recrute ? »', 'adaptours' ) }>
						<TextControl
							label={ __( 'Lien du bouton', 'adaptours' ) }
							value={ attributes.cta_url }
							onChange={ ( v ) => setAttributes( { cta_url: v } ) }
							help={ __( 'Par défaut, mène à la section Recrutement plus bas dans la page.', 'adaptours' ) }
						/>
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<div className="team-grid__inner">
						<header className="team-grid__head">
							<div className="team-grid__intro">
								<RichText
									tagName="p"
									className="team-grid__eyebrow"
									value={ attributes.eyebrow }
									allowedFormats={ [] }
									onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
									placeholder={ __( 'Surtitre', 'adaptours' ) }
								/>
								<h2 className="team-grid__title">
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
								</h2>
							</div>
							<RichText
								tagName="p"
								className="team-grid__desc"
								value={ attributes.description }
								allowedFormats={ [] }
								onChange={ ( description ) => setAttributes( { description } ) }
								placeholder={ __( 'Texte à droite du titre', 'adaptours' ) }
							/>
						</header>

						<ul { ...innerProps } />

						<div className="team-grid__band">
							<RichText
								tagName="p"
								className="team-grid__band-text"
								value={ attributes.band_text }
								allowedFormats={ [] }
								onChange={ ( band_text ) => setAttributes( { band_text } ) }
								placeholder={ __( 'on recrute, par curiosité ?', 'adaptours' ) }
							/>
							<RichText
								tagName="span"
								className="button button--secondary team-grid__band-cta"
								value={ attributes.cta_label }
								allowedFormats={ [] }
								onChange={ ( cta_label ) => setAttributes( { cta_label } ) }
								placeholder={ __( 'Texte du bouton', 'adaptours' ) }
							/>
						</div>
					</div>
				</section>
			</>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
