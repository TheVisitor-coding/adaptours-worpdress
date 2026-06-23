/**
 * Initialise GLightbox sur la galerie destination.
 *
 * Vanilla statique (hors build wp-scripts), chargé uniquement sur le single destination.
 * Cible les liens `.adaptours-glightbox` ; le regroupement prev/next se fait via
 * l'attribut data-gallery posé dans le rendu du bloc.
 */
( function () {
	function init() {
		if ( typeof GLightbox === 'undefined' ) {
			return;
		}
		if ( ! document.querySelector( '.adaptours-glightbox' ) ) {
			return;
		}
		GLightbox( {
			selector: '.adaptours-glightbox',
			touchNavigation: true,
			loop: true,
			zoomable: false,
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
}() );
