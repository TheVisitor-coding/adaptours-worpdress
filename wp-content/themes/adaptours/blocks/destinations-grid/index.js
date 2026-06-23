/**
 * Bloc adaptours/destinations-grid — composant d'édition (« Des destinations variées »).
 * Rendu front = render.php. Édition via Inspector (4 sélecteurs de destination) + aperçu
 * ServerSideRender (cards + titre bichrome rendus côté serveur depuis le CPT destination).
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	ComboboxControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __, sprintf } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

const SLOTS = [
	__( 'Destination en grand (1re)', 'adaptours' ),
	__( 'Destination 2', 'adaptours' ),
	__( 'Destination 3', 'adaptours' ),
	__( 'Destination 4 (large)', 'adaptours' ),
];

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();
		const selected = attributes.destinations || [];

		const records = useSelect(
			( select ) =>
				select( coreStore ).getEntityRecords( 'postType', 'destination', {
					per_page: -1,
					status: 'publish',
					_fields: 'id,title',
				} ),
			[]
		);

		const options = [
			{ value: '0', label: __( '— Aucune —', 'adaptours' ) },
			...( records || [] ).map( ( d ) => ( {
				value: String( d.id ),
				label: d.title?.rendered || `#${ d.id }`,
			} ) ),
		];

		const setSlot = ( index, value ) => {
			const next = [ ...selected ];
			next[ index ] = value ? Number( value ) : 0;
			setAttributes( { destinations: next } );
		};

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Textes', 'adaptours' ) }>
						<TextControl
							label={ __( 'Surtitre', 'adaptours' ) }
							value={ attributes.eyebrow }
							onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						/>
						<TextControl
							label={ __( 'Titre — début', 'adaptours' ) }
							value={ attributes.title_part_1 }
							onChange={ ( v ) => setAttributes( { title_part_1: v } ) }
						/>
						<TextControl
							label={ __( 'Mot(s) en orange', 'adaptours' ) }
							value={ attributes.title_part_2 }
							onChange={ ( v ) => setAttributes( { title_part_2: v } ) }
						/>
						<TextareaControl
							label={ __( 'Texte', 'adaptours' ) }
							value={ attributes.description }
							onChange={ ( v ) => setAttributes( { description: v } ) }
						/>
						<TextControl
							label={ __( 'Texte du bouton', 'adaptours' ) }
							value={ attributes.cta_label }
							onChange={ ( v ) => setAttributes( { cta_label: v } ) }
						/>
						<TextControl
							label={ __( 'Lien du bouton', 'adaptours' ) }
							type="url"
							value={ attributes.cta_url }
							onChange={ ( v ) => setAttributes( { cta_url: v } ) }
							help={ __( 'Laissez vide pour aller vers la liste des destinations.', 'adaptours' ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Destinations affichées', 'adaptours' ) }>
						{ SLOTS.map( ( label, i ) => (
							<ComboboxControl
								key={ i }
								label={ label }
								value={ String( selected[ i ] || 0 ) }
								options={ options }
								onChange={ ( v ) => setSlot( i, v ) }
							/>
						) ) }
						<p className="components-base-control__help">
							{ sprintf(
								/* translators: %d = nombre de destinations. */
								__( 'Choisissez %d destinations. La 1re s’affiche en grand à gauche.', 'adaptours' ),
								SLOTS.length
							) }
						</p>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender block={ metadata.name } attributes={ attributes } />
				</div>
			</>
		);
	},
	save: () => null,
} );
