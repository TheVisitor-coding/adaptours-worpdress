/**
 * Hero d'accueil — rotator de destinations.
 */
( function () {
	'use strict';

	var rotator = document.querySelector( '.hero-home__rotator' );
	if ( ! rotator ) {
		return;
	}

	var wordEl = rotator.querySelector( '.hero-home__rotator-word' );
	if ( ! wordEl || typeof wordEl.animate !== 'function' ) {
		return; // pas de Web Animations API → on garde le mot statique
	}

	var words;
	try {
		words = JSON.parse( rotator.getAttribute( 'data-rotator' ) || '[]' );
	} catch ( e ) {
		words = [];
	}

	var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	if ( reduced || ! Array.isArray( words ) || words.length < 2 ) {
		return; 
	}

	// Mesureur caché : hérite la police/taille de la ligne pour mesurer la largeur d'un mot.
	var measurer = document.createElement( 'span' );
	measurer.setAttribute( 'aria-hidden', 'true' );
	measurer.style.cssText =
		'position:absolute;visibility:hidden;white-space:nowrap;pointer-events:none;left:0;top:0';
	rotator.appendChild( measurer );

	var measure = function ( text ) {
		measurer.textContent = text;
		return measurer.getBoundingClientRect().width;
	};

	var index = 0;

	var lockWidth = function ( px ) {
		rotator.style.width = px + 'px';
	};

	lockWidth( measure( words[ index ] ) );

	// Recale la largeur après chargement des polices (Caveat) et au redimensionnement, sans animer.
	var resync = function () {
		var prev = rotator.style.transition;
		rotator.style.transition = 'none';
		lockWidth( measure( words[ index ] ) );
		void rotator.offsetWidth; // reflow
		rotator.style.transition = prev;
	};
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( resync );
	}
	window.addEventListener( 'resize', resync, { passive: true } );

	var OUT = { duration: 440, easing: 'cubic-bezier(0.4, 0, 1, 1)', fill: 'forwards' };
	var IN = { duration: 600, easing: 'cubic-bezier(0.16, 1, 0.3, 1)', fill: 'forwards' };
	var HOLD = 2800;

	var swap = function () {
		if ( document.hidden ) {
			return; // évite de défiler dans un onglet inactif
		}

		var next = ( index + 1 ) % words.length;

		lockWidth( measure( words[ next ] ) );

		var out = wordEl.animate(
			[
				{ opacity: 1, transform: 'translateY(0)' },
				{ opacity: 0, transform: 'translateY(-0.5em)' },
			],
			OUT
		);

		out.onfinish = function () {
			index = next;
			wordEl.textContent = words[ next ];
			wordEl.animate(
				[
					{ opacity: 0, transform: 'translateY(0.6em)' },
					{ opacity: 1, transform: 'translateY(0)' },
				],
				IN
			);
		};
	};

	setInterval( swap, HOLD );
}() );
