/**
 * Configuration webpack du thème Adaptours — étend la config par défaut de
 * @wordpress/scripts pour le build des blocs (`npm run build:blocks`).
 *
 * Seul ajout : un load-path SASS vers `assets/src/scss` afin que les `style.scss`
 * des blocs puissent faire `@use "abstracts" as *;` (mixins conteneur/breakpoints,
 * source unique partagée avec le build CSS global qui passe déjà
 * `--load-path=assets/src/scss`). Sans cela, sass-loader ne résout pas « abstracts ».
 *
 * sass-loader 16 utilise l'API moderne dart-sass → option `sassOptions.loadPaths`.
 */

const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const SCSS_LOAD_PATH = path.resolve( __dirname, 'assets/src/scss' );

/**
 * Injecte le load-path SASS dans chaque règle utilisant sass-loader.
 *
 * @param {Array} rules Règles webpack (module.rules), éventuellement imbriquées (oneOf).
 */
function addSassLoadPath( rules ) {
	if ( ! Array.isArray( rules ) ) {
		return;
	}
	rules.forEach( ( rule ) => {
		if ( rule.oneOf ) {
			addSassLoadPath( rule.oneOf );
		}
		if ( ! Array.isArray( rule.use ) ) {
			return;
		}
		rule.use.forEach( ( entry ) => {
			if (
				entry &&
				typeof entry === 'object' &&
				typeof entry.loader === 'string' &&
				entry.loader.includes( 'sass-loader' )
			) {
				entry.options = entry.options || {};
				const sassOptions = entry.options.sassOptions || {};
				entry.options.sassOptions = {
					...sassOptions,
					loadPaths: [
						...( sassOptions.loadPaths || [] ),
						SCSS_LOAD_PATH,
					],
				};
			}
		} );
	} );
}

// La config par défaut est soit un objet, soit un tableau [scriptConfig, moduleConfig].
const configs = Array.isArray( defaultConfig ) ? defaultConfig : [ defaultConfig ];
configs.forEach( ( config ) => {
	if ( config && config.module ) {
		addSassLoadPath( config.module.rules );
	}
} );

module.exports = defaultConfig;
