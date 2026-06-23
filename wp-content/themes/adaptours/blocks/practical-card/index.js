/**
 * Bloc adaptours/practical-card — composant d'édition d'une carte pratique.
 *
 * Édition inline : titre + description en RichText, icône choisie dans le panneau latéral.
 * Le numéro 01..NN est automatique (compteur CSS), non éditable. Pas de style.scss propre :
 * le layout est porté par le bloc parent adaptours/section-practical.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// Liste fermée d'icônes — miroir de inc/icons.php (adaptours_get_icons()).
const ICON_OPTIONS = [
	{ value: '', label: __( '— Aucune —', 'adaptours' ) },
	{ value: 'visa', label: __( 'Visa / formalités', 'adaptours' ) },
	{ value: 'sante', label: __( 'Santé', 'adaptours' ) },
	{ value: 'decalage', label: __( 'Décalage horaire', 'adaptours' ) },
	{ value: 'vol', label: __( 'Vol', 'adaptours' ) },
	{ value: 'budget', label: __( 'Budget', 'adaptours' ) },
	{ value: 'langue', label: __( 'Langue', 'adaptours' ) },
	{ value: 'monnaie', label: __( 'Monnaie', 'adaptours' ) },
	{ value: 'transport', label: __( 'Transport', 'adaptours' ) },
	{ value: 'accessibilite', label: __( 'Accessibilité', 'adaptours' ) },
	{ value: 'rythme', label: __( 'Rythme adapté', 'adaptours' ) },
	{ value: 'accompagnement', label: __( 'Accompagnement', 'adaptours' ) },
	{ value: 'confort', label: __( 'Confort', 'adaptours' ) },
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'section-practical__card' } );
		const { icon, card_title: cardTitle, description } = attributes;

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Carte', 'adaptours' ) }>
						<SelectControl
							label={ __( 'Icône', 'adaptours' ) }
							value={ icon }
							options={ ICON_OPTIONS }
							onChange={ ( v ) => setAttributes( { icon: v } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<li { ...blockProps }>
					<div className="section-practical__card-top">
						<span className="section-practical__card-icon" aria-hidden="true" />
						<span className="section-practical__card-num" aria-hidden="true" />
					</div>
					<RichText
						tagName="h3"
						className="section-practical__card-title"
						value={ cardTitle }
						allowedFormats={ [] }
						onChange={ ( v ) => setAttributes( { card_title: v } ) }
						placeholder={ __( 'Titre (ex. : Visa & formalités)', 'adaptours' ) }
					/>
					<RichText
						tagName="p"
						className="section-practical__card-desc"
						value={ description }
						allowedFormats={ [] }
						onChange={ ( v ) => setAttributes( { description: v } ) }
						placeholder={ __( 'Détail pratique…', 'adaptours' ) }
					/>
				</li>
			</>
		);
	},
	save: () => null,
} );
