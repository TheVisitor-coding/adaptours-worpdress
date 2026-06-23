/**
 * Bloc adaptours/section-practical — composant d'édition. Parent InnerBlocks.
 *
 * Header (surtitre / titre / description) édité dans le panneau latéral ; les cartes sont
 * des blocs enfants « adaptours/practical-card ». save n'émet que les cartes ; render.php
 * les enveloppe dans la grille + ajoute le header. La numérotation 01..NN est en CSS.
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

const ALLOWED = [ 'adaptours/practical-card' ];
const TEMPLATE = [
	[ 'adaptours/practical-card', { icon: 'visa', card_title: 'Visa & formalités' } ],
	[ 'adaptours/practical-card', { icon: 'sante', card_title: 'Santé & vaccins' } ],
	[ 'adaptours/practical-card', { icon: 'decalage', card_title: 'Décalage horaire' } ],
	[ 'adaptours/practical-card', { icon: 'vol', card_title: 'Vol & escale' } ],
	[ 'adaptours/practical-card', { icon: 'budget', card_title: 'Budget sur place' } ],
	[ 'adaptours/practical-card', { icon: 'langue', card_title: 'Langue & culture' } ],
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();
		const innerProps = useInnerBlocksProps(
			{ className: 'section-practical__grid' },
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
							help={ __( 'Texte affiché à droite du titre.', 'adaptours' ) }
						/>
					</PanelBody>
				</InspectorControls>

				<section { ...blockProps }>
					<div className="section-practical__inner">
						<header className="section-practical__head">
							<div className="section-practical__intro">
								{ attributes.eyebrow && (
									<p className="section-practical__eyebrow">
										{ attributes.eyebrow }
									</p>
								) }
								{ ( attributes.title || attributes.title_accent ) && (
									<h2 className="section-practical__title">
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
								<p className="section-practical__desc">
									{ attributes.description }
								</p>
							) }
						</header>
						<ul { ...innerProps } />
					</div>
				</section>
			</>
		);
	},
	save: () => <InnerBlocks.Content />,
} );
