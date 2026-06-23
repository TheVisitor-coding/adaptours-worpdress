import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// Importé pour que wp-scripts compile le SCSS en `style-index.css`.
import './style.scss';

registerBlockType( metadata.name, {
	edit: () => {
		const blockProps = useBlockProps();

		// Contexte post (la destination en cours d'édition) transmis au rendu serveur,
		// afin que render.php lise le bon champ `avis_mis_en_avant`.
		const postId = useSelect(
			( select ) => select( 'core/editor' )?.getCurrentPostId(),
			[]
		);

		return (
			<div { ...blockProps }>
				<p className="avis-spotlight__editor-hint">
					{ __(
						'L’avis affiché ici se choisit dans la fiche de cette destination, champ « Avis mis en avant ».',
						'adaptours'
					) }
				</p>
				<ServerSideRender
					block={ metadata.name }
					attributes={ {} }
					urlQueryArgs={ postId ? { post_id: postId } : undefined }
				/>
			</div>
		);
	},
	save: () => null,
} );
