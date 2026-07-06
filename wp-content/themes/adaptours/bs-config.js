/**
 * Configuration BrowserSync — mode développement du thème Adaptours.
 *
 * Proxy le front wp-env (localhost:8888) sur localhost:3000 et surveille les
 * sorties de build + les fichiers PHP pour rafraîchir le navigateur :
 * - CSS : injecté à chaud (sans rechargement de page) ;
 * - JS / PHP / theme.json : rechargement complet.
 *
 * Le PHP est monté en bind-mount par wp-env (édition « live »), donc aucune
 * recompilation n'est nécessaire — BrowserSync ne fait que déclencher le reload.
 *
 * Démarrage via `npm run dev` (orchestre sass --watch + webpack watch + ce proxy).
 */

module.exports = {
	proxy: {
		target: 'http://localhost:8888',
		proxyReq: [
			// WordPress/PHP renvoie du HTML gzippé ; BrowserSync ne peut injecter son
			// script de rechargement que dans une réponse non compressée. On demande donc
			// l'upstream en clair (sinon : aucun auto-reload, snippet absent).
			function ( proxyReq ) {
				proxyReq.setHeader( 'Accept-Encoding', 'identity' );
			},
		],
	},
	port: 3000,
	open: false,
	notify: true,
	ui: false,
	ghostMode: false,
	reloadDelay: 200,
	injectChanges: true,
	files: [
		'assets/build/**/*.css', // main.css + styles de blocs → injection à chaud.
		'assets/build/**/*.js',  // bundles de blocs → reload.
		'assets/js/**/*.js',     // scripts vanilla (header, galerie, devis…) → reload.
		'**/*.php',              // templates, parts, render.php → reload.
		'theme.json',            // tokens / réglages éditeur → reload.
	],
	watchOptions: {
		ignoreInitial: true,
	},
	// Ne jamais surveiller les dépendances ni le vendor.
	// (BrowserSync applique ces ignores aux globs ci-dessus.)
	ignore: [ 'node_modules/**', 'assets/vendor/**' ],
};
