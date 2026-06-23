/**
 * Archive Destinations — amélioration progressive des filtres.
 *
 * Vanilla, sans dépendance ni build (fichier statique enqueué conditionnellement par
 * inc/enqueue.php sur l'archive destination). Filtres SANS AJAX (LOT 1) : le formulaire
 * se soumet par rechargement de page.
 *
 * Rôle : soumettre automatiquement le formulaire au changement d'un menu déroulant
 * (continent / budget). La recherche se soumet nativement à la touche Entrée. Sans JS,
 * le bouton « Filtrer » (visuellement masqué) reste actionnable → aucune perte de
 * fonctionnalité.
 */
( function () {
	'use strict';

	var form = document.querySelector( '.archive-destinations__filters' );
	if ( ! form ) {
		return;
	}

	var selects = form.querySelectorAll( '.archive-destinations__select' );
	Array.prototype.forEach.call( selects, function ( select ) {
		select.addEventListener( 'change', function () {
			form.submit();
		} );
	} );
}() );
