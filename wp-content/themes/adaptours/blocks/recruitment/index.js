/**
 * Bloc adaptours/recruitment — composant d'édition (section recrutement, parent InnerBlocks).
 *
 * Textes (gauche) et en-tête de la carte des conditions (droite) édités INLINE via RichText ;
 * l'adresse e-mail des CV se règle dans le panneau latéral. Les conditions sont des blocs
 * enfants « adaptours/recruitment-condition ». save n'émet que les conditions ; render.php
 * les enveloppe dans une liste numérotée.
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

const ALLOWED = [ 'adaptours/recruitment-condition' ];
const TEMPLATE = [
	[ 'adaptours/recruitment-condition', { title: 'Sensible au handicap.', description: 'Aide aux gestes de la vie quotidienne, patience, écoute, qualité relationnelle.' } ],
	[ 'adaptours/recruitment-condition', { title: 'Disponible.', description: 'Vous ne travaillez plus, ou vous êtes intérimaire.' } ],
	[ 'adaptours/recruitment-condition', { title: 'Auto-entrepreneur.', description: 'Vous avez votre statut — ou vous êtes prêt·e à le créer.' } ],
	[ 'adaptours/recruitment-condition', { title: 'Notions d’une langue étrangère.', description: 'Anglais ou espagnol — pas besoin d’être bilingue.' } ],
	[ 'adaptours/recruitment-condition', { title: 'De l’initiative.', description: 'Vous savez prendre des décisions et gérer des situations délicates.' } ],
	[ 'adaptours/recruitment-condition', { title: 'Majeur·e.', description: 'Plus de 18 ans.' } ],
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'recruitment' } );
		const innerProps = useInnerBlocksProps(
			{ className: 'recruitment__conditions' },
			{ allowedBlocks: ALLOWED, template: TEMPLATE, templateLock: false }
		);

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Candidatures', 'adaptours' ) }>
						<TextControl
							label={ __( 'Adresse e-mail pour les CV', 'adaptours' ) }
							value={ attributes.cv_email }
							onChange={ ( v ) => setAttributes( { cv_email: v } ) }
							help={ __( 'Laissez vide pour utiliser l’e-mail général du site.', 'adaptours' ) }
						/>
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<div className="recruitment__inner">
						<div className="recruitment__intro">
							<RichText
								tagName="p"
								className="recruitment__eyebrow"
								value={ attributes.eyebrow }
								allowedFormats={ [] }
								onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
								placeholder={ __( 'Surtitre', 'adaptours' ) }
							/>
							<h2 className="recruitment__title">
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
							<RichText
								tagName="p"
								className="recruitment__text"
								value={ attributes.description }
								allowedFormats={ [] }
								onChange={ ( description ) => setAttributes( { description } ) }
								placeholder={ __( 'Présentation du poste…', 'adaptours' ) }
							/>
							<div className="recruitment__cv">
								<span className="recruitment__cv-tab" aria-hidden="true" />
								<p className="recruitment__cv-label">{ __( 'Merci d’envoyer votre CV à', 'adaptours' ) }</p>
								<span className="recruitment__cv-email">
									{ attributes.cv_email || __( '(e-mail général du site)', 'adaptours' ) }
								</span>
							</div>
						</div>

						<div className="recruitment__conditions-card">
							<RichText
								tagName="p"
								className="recruitment__eyebrow"
								value={ attributes.conditions_eyebrow }
								allowedFormats={ [] }
								onChange={ ( conditions_eyebrow ) => setAttributes( { conditions_eyebrow } ) }
								placeholder={ __( 'Surtitre', 'adaptours' ) }
							/>
							<h3 className="recruitment__conditions-title">
								<RichText
									tagName="span"
									value={ attributes.conditions_title_1 }
									allowedFormats={ [] }
									onChange={ ( conditions_title_1 ) => setAttributes( { conditions_title_1 } ) }
									placeholder={ __( 'Titre…', 'adaptours' ) }
								/>{ ' ' }
								<RichText
									tagName="span"
									className="accent"
									value={ attributes.conditions_title_2 }
									allowedFormats={ [] }
									onChange={ ( conditions_title_2 ) => setAttributes( { conditions_title_2 } ) }
									placeholder={ __( 'mot(s) en orange', 'adaptours' ) }
								/>
							</h3>
							<RichText
								tagName="p"
								className="recruitment__conditions-subtitle"
								value={ attributes.conditions_subtitle }
								allowedFormats={ [] }
								onChange={ ( conditions_subtitle ) => setAttributes( { conditions_subtitle } ) }
								placeholder={ __( 'Sous-titre…', 'adaptours' ) }
							/>
							<ol { ...innerProps } />
						</div>
					</div>
				</section>
			</>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
