<?php
/**
 * Hooks de présentation transverses (classes <body>, etc.).
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Active la variante header transparent sur le single destination.
 *
 * Pose `has-transparent-header` sur <body> ; parts/header.php rend alors le header
 * transparent et assets/js/header.js le repasse en `.site-header--solid` au scroll.
 *
 * @param string[] $classes Classes CSS du <body>.
 * @return string[]
 */
function adaptours_body_classes( $classes ) {
	if ( is_singular( 'destination' ) ) {
		$classes[] = 'has-transparent-header';
	}
	return $classes;
}
add_filter( 'body_class', 'adaptours_body_classes' );
