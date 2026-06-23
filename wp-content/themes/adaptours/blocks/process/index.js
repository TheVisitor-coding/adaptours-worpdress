/**
 * Bloc adaptours/process — composant d'édition (« Du premier mot au dernier souvenir »).
 * Rendu front = render.php. Édition via Inspector (en-tête + 3 étapes) + aperçu ServerSideRender.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __, sprintf } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

const StepPanel = ( { n, attributes, setAttributes } ) => (
	<PanelBody
		title={ sprintf( /* translators: %d = numéro d'étape. */ __( 'Étape %d', 'adaptours' ), n ) }
		initialOpen={ false }
	>
		<TextControl
			label={ __( 'Titre', 'adaptours' ) }
			value={ attributes[ `process_${ n }_title` ] }
			onChange={ ( v ) => setAttributes( { [ `process_${ n }_title` ]: v } ) }
		/>
		<TextareaControl
			label={ __( 'Texte', 'adaptours' ) }
			value={ attributes[ `process_${ n }_description` ] }
			onChange={ ( v ) => setAttributes( { [ `process_${ n }_description` ]: v } ) }
		/>
		<TextareaControl
			label={ __( 'Points clés', 'adaptours' ) }
			value={ attributes[ `process_${ n }_features` ] }
			onChange={ ( v ) => setAttributes( { [ `process_${ n }_features` ]: v } ) }
			help={ __( 'Un point par ligne (chaque ligne s’affiche avec une coche).', 'adaptours' ) }
			rows={ 3 }
		/>
		<TextControl
			label={ __( 'Durée indicative', 'adaptours' ) }
			value={ attributes[ `process_${ n }_meta` ] }
			onChange={ ( v ) => setAttributes( { [ `process_${ n }_meta` ]: v } ) }
			help={ __( 'Ex. « ~ 48 h ». Laissez vide pour masquer.', 'adaptours' ) }
		/>
		<TextControl
			label={ __( 'Bouton — texte (facultatif)', 'adaptours' ) }
			value={ attributes[ `process_${ n }_cta_label` ] }
			onChange={ ( v ) => setAttributes( { [ `process_${ n }_cta_label` ]: v } ) }
		/>
		<TextControl
			label={ __( 'Bouton — lien (facultatif)', 'adaptours' ) }
			type="url"
			value={ attributes[ `process_${ n }_cta_url` ] }
			onChange={ ( v ) => setAttributes( { [ `process_${ n }_cta_url` ]: v } ) }
		/>
	</PanelBody>
);

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Titre', 'adaptours' ) }>
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
					</PanelBody>
					<StepPanel n={ 1 } attributes={ attributes } setAttributes={ setAttributes } />
					<StepPanel n={ 2 } attributes={ attributes } setAttributes={ setAttributes } />
					<StepPanel n={ 3 } attributes={ attributes } setAttributes={ setAttributes } />
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender block={ metadata.name } attributes={ attributes } />
				</div>
			</>
		);
	},
	save: () => null,
} );
