/**
 * Bloc adaptours/kpi-bar — composant d'édition (côté éditeur).
 *
 * Édition INLINE dans le canvas (choix client) : chaque valeur/libellé se saisit
 * directement sur le bloc via RichText en texte brut (allowedFormats vide → aucune
 * balise injectée, cohérent avec esc_html() côté render.php). Le rendu FRONT est
 * assuré par render.php (bloc dynamique) : `save` ne renvoie donc rien.
 *
 * Le markup reproduit celui de render.php (ul/li + __value/__label) pour un aperçu
 * fidèle. `columns` est un attribut structurel (default 4), NON exposé à la cliente.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// Importé pour que wp-scripts compile le SCSS en `style-index.css` (style front,
// référencé par la clé "style" du block.json compilé). Voir block.json.
import './style.scss';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const columns = attributes.columns || 4;
		const blockProps = useBlockProps( {
			className: `is-cols-${ columns }`,
			style: { '--adaptours-kpi-cols': columns },
		} );

		const slots = [];
		for ( let i = 1; i <= columns; i++ ) {
			slots.push( i );
		}

		return (
			<section { ...blockProps }>
				<ul className="wp-block-adaptours-kpi-bar__list">
					{ slots.map( ( i ) => (
						<li
							key={ i }
							className="wp-block-adaptours-kpi-bar__item"
						>
							<RichText
								tagName="span"
								className="wp-block-adaptours-kpi-bar__value"
								value={ attributes[ `kpi_${ i }_value` ] }
								allowedFormats={ [] }
								onChange={ ( value ) =>
									setAttributes( {
										[ `kpi_${ i }_value` ]: value,
									} )
								}
								placeholder={ __( 'Chiffre', 'adaptours' ) }
							/>
							<RichText
								tagName="span"
								className="wp-block-adaptours-kpi-bar__label"
								value={ attributes[ `kpi_${ i }_label` ] }
								allowedFormats={ [] }
								onChange={ ( value ) =>
									setAttributes( {
										[ `kpi_${ i }_label` ]: value,
									} )
								}
								placeholder={ __( 'Libellé', 'adaptours' ) }
							/>
						</li>
					) ) }
				</ul>
			</section>
		);
	},
	save: () => null,
} );
