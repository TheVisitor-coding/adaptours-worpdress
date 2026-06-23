/**
 * Page Devis — décoration du formulaire.
 *
 * Vanilla, sans dépendance ni build (fichier statique enqueué par inc/enqueue.php sur le
 * template Devis). Amélioration progressive : sans JS, les champs nombre natifs restent
 * utilisables.
 *
 * Responsabilités :
 *   1. Steppers −/+ : remplace chaque <input type="number"> ciblé par une UI à 3 boutons
 *      (respecte min/max ; met à jour la valeur réelle + déclenche input/change pour la
 *      logique conditionnelle CF7 et la validation).
 *   2. Garde-fou « enfants ≤ total » : le nombre d'enfants ne peut pas dépasser le nombre
 *      total de voyageurs (la validation serveur reste celle de CF7).
 */
( function () {
	'use strict';

	var form = document.querySelector( '.devis-form' );
	if ( ! form ) {
		return;
	}

	function clamp( value, min, max ) {
		if ( isFinite( min ) && value < min ) {
			value = min;
		}
		if ( isFinite( max ) && value > max ) {
			value = max;
		}
		return value;
	}

	function setValue( input, next ) {
		var min = parseFloat( input.getAttribute( 'min' ) );
		var max = parseFloat( input.getAttribute( 'max' ) );
		next = clamp( next, min, max );

		if ( String( next ) === input.value ) {
			return;
		}
		input.value = String( next );
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	function decorate( input ) {
		var row = input.closest( '.devis-form__stepper-row' );
		var labelEl = row ? row.querySelector( '.devis-form__label' ) : null;
		var labelText = labelEl ? labelEl.textContent.trim() : '';

		var min = parseFloat( input.getAttribute( 'min' ) );
		var max = parseFloat( input.getAttribute( 'max' ) );

		var stepper = document.createElement( 'span' );
		stepper.className = 'devis-stepper';
		stepper.setAttribute( 'role', 'group' );
		if ( labelText ) {
			stepper.setAttribute( 'aria-label', labelText );
		}

		var minus = document.createElement( 'button' );
		minus.type = 'button';
		minus.className = 'devis-stepper__btn';
		minus.setAttribute( 'aria-label', 'Diminuer' );
		minus.textContent = '−';

		var value = document.createElement( 'span' );
		value.className = 'devis-stepper__value';
		value.setAttribute( 'aria-live', 'polite' );

		var plus = document.createElement( 'button' );
		plus.type = 'button';
		plus.className = 'devis-stepper__btn';
		plus.setAttribute( 'aria-label', 'Augmenter' );
		plus.textContent = '+';

		var sync = function () {
			var current = parseFloat( input.value );
			if ( ! isFinite( current ) ) {
				current = isFinite( min ) ? min : 0;
			}
			value.textContent = String( current );
			minus.disabled = isFinite( min ) && current <= min;
			plus.disabled = isFinite( max ) && current >= max;
		};

		minus.addEventListener( 'click', function () {
			setValue( input, ( parseFloat( input.value ) || 0 ) - 1 );
		} );
		plus.addEventListener( 'click', function () {
			setValue( input, ( parseFloat( input.value ) || 0 ) + 1 );
		} );
		input.addEventListener( 'input', sync );
		input.addEventListener( 'change', sync );

		stepper.appendChild( minus );
		stepper.appendChild( value );
		stepper.appendChild( plus );

		input.parentNode.insertBefore( stepper, input );
		input.style.display = 'none';
		sync();
	}

	// Garde-fou « enfants ≤ total ».
	function wireChildrenCap() {
		var total = form.querySelector( 'input[name="devis-total"]' );
		var enfants = form.querySelector( 'input[name="devis-enfants"]' );
		if ( ! total || ! enfants ) {
			return;
		}
		var cap = function () {
			var t = parseFloat( total.value );
			var e = parseFloat( enfants.value );
			if ( isFinite( t ) && isFinite( e ) && e > t ) {
				setValue( enfants, t );
			}
		};
		total.addEventListener( 'change', cap );
		enfants.addEventListener( 'change', cap );
	}

	function init() {
		var inputs = form.querySelectorAll( '.devis-form__stepper input[type="number"]' );
		Array.prototype.forEach.call( inputs, decorate );
		wireChildrenCap();
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
}() );
