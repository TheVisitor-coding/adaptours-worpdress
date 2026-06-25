/**
 * Header du site — interactions front.
 *
 * Vanilla, sans dépendance ni build (fichier statique enqueué par inc/enqueue.php).
 * Trois responsabilités :
 *   1. Burger mobile : ouverture/fermeture du drawer (aria-expanded + classe body),
 *      fermeture par Échap ou clic hors-zone.
 *   2. Sélecteur de langue : ouverture/fermeture du dropdown (aria-expanded + classe
 *      .site-header__lang--open), fermeture par Échap ou clic hors-zone.
 *   3. Variante transparente (single-destination) : si <body> porte
 *      .has-transparent-header, bascule .site-header--solid au scroll
 *      (IntersectionObserver sur le hero, repli sur scrollY). No-op sinon.
 */
( function () {
	'use strict';

	var header = document.querySelector( '.site-header' );
	if ( ! header ) {
		return;
	}

	// 1. Burger
	var burger = header.querySelector( '.site-header__burger' );
	var panel = document.getElementById( 'site-primary-nav' );

	if ( burger && panel ) {
		var closeNav = function () {
			burger.setAttribute( 'aria-expanded', 'false' );
			document.body.classList.remove( 'site-header--nav-open' );
		};

		burger.addEventListener( 'click', function () {
			var isOpen = burger.getAttribute( 'aria-expanded' ) === 'true';
			burger.setAttribute( 'aria-expanded', String( ! isOpen ) );
			document.body.classList.toggle( 'site-header--nav-open', ! isOpen );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				closeNav();
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if (
				document.body.classList.contains( 'site-header--nav-open' ) &&
				! panel.contains( event.target ) &&
				! burger.contains( event.target )
			) {
				closeNav();
			}
		} );
	}

	// 2. Sélecteur de langue (dropdown)
	var langSwitcher = header.querySelector( '[data-lang-switcher]' );
	var langToggle = langSwitcher && langSwitcher.querySelector( '.site-header__lang-toggle' );

	if ( langSwitcher && langToggle ) {
		var closeLang = function () {
			langToggle.setAttribute( 'aria-expanded', 'false' );
			langSwitcher.classList.remove( 'site-header__lang--open' );
		};

		langToggle.addEventListener( 'click', function () {
			var isOpen = langToggle.getAttribute( 'aria-expanded' ) === 'true';
			langToggle.setAttribute( 'aria-expanded', String( ! isOpen ) );
			langSwitcher.classList.toggle( 'site-header__lang--open', ! isOpen );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				closeLang();
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if (
				langSwitcher.classList.contains( 'site-header__lang--open' ) &&
				! langSwitcher.contains( event.target )
			) {
				closeLang();
			}
		} );
	}

	// 3. Variante transparente (single-destination)
	if ( ! document.body.classList.contains( 'has-transparent-header' ) ) {
		return;
	}

	var setSolid = function ( solid ) {
		header.classList.toggle( 'site-header--solid', solid );
	};

	// Sélecteur du hero figé à l'étape single-destination ; repli scrollY sinon.
	var hero = document.querySelector( '.single-destination__hero' );

	if ( hero && 'IntersectionObserver' in window ) {
		var observer = new IntersectionObserver(
			function ( entries ) {
				setSolid( ! entries[ 0 ].isIntersecting );
			},
			{ rootMargin: '-80px 0px 0px 0px' }
		);
		observer.observe( hero );
	} else {
		var onScroll = function () {
			setSolid( window.scrollY > 80 );
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}
}() );
