import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Notice } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

import './style.scss';

registerBlockType( metadata.name, {
	edit: () => {
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Formulaire de devis', 'adaptours' ) }>
						<Notice status="info" isDismissible={ false }>
							{ __(
								'Le formulaire et les questions sont gérés par l’équipe Adaptours. Il n’y a rien à régler ici.',
								'adaptours'
							) }
						</Notice>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ServerSideRender block={ metadata.name } />
				</div>
			</>
		);
	},
	save: () => null,
} );
