<?php
/**
 * Sélecteur de langue du header (dropdown)
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'pll_the_languages' ) ) {
	return;
}

$adaptours_langs = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
if ( ! is_array( $adaptours_langs ) || count( $adaptours_langs ) < 2 ) {
	return;
}

// Code drapeau = basename du PNG Polylang (fr, us, es…), repli sur la région de la locale.
$adaptours_flag_code = static function ( $lang ) {
	if ( ! empty( $lang['flag'] ) ) {
		$basename = pathinfo( (string) wp_parse_url( $lang['flag'], PHP_URL_PATH ), PATHINFO_FILENAME );
		if ( '' !== $basename ) {
			return $basename;
		}
	}
	if ( ! empty( $lang['locale'] ) ) {
		$parts = preg_split( '/[-_]/', (string) $lang['locale'] );
		return isset( $parts[1] ) ? $parts[1] : $parts[0];
	}
	return isset( $lang['slug'] ) ? $lang['slug'] : '';
};

// Langue active (pour le drapeau et le libellé du trigger).
$adaptours_current = null;
foreach ( $adaptours_langs as $adaptours_lang ) {
	if ( ! empty( $adaptours_lang['current_lang'] ) ) {
		$adaptours_current = $adaptours_lang;
		break;
	}
}
if ( null === $adaptours_current ) {
	$adaptours_current = reset( $adaptours_langs );
}
?>
<div class="site-header__lang" data-lang-switcher>
	<button
		type="button"
		class="site-header__lang-toggle"
		aria-haspopup="true"
		aria-expanded="false"
		aria-controls="site-lang-menu"
	>
		<span class="site-header__lang-flag" aria-hidden="true"><?php echo adaptours_flag_svg( $adaptours_flag_code( $adaptours_current ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — SVG déjà échappé par adaptours_flag_svg() ?></span>
		<span class="screen-reader-text">
			<?php
			/* translators: %s: nom de la langue active. */
			printf( esc_html__( 'Choisir la langue — actuelle : %s', 'adaptours' ), esc_html( $adaptours_current['name'] ) );
			?>
		</span>
		<svg class="site-header__lang-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="1 1 5 5 9 1"/></svg>
	</button>

	<ul class="site-header__lang-menu" id="site-lang-menu">
		<?php foreach ( $adaptours_langs as $adaptours_lang ) : ?>
			<?php
			$adaptours_is_current = ! empty( $adaptours_lang['current_lang'] );
			$adaptours_flag       = adaptours_flag_svg( $adaptours_flag_code( $adaptours_lang ) );
			?>
			<li class="site-header__lang-item">
				<?php if ( $adaptours_is_current ) : ?>
					<span class="site-header__lang-link is-current" aria-current="true">
						<span class="site-header__lang-flag" aria-hidden="true"><?php echo $adaptours_flag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — SVG déjà échappé ?></span>
						<span class="site-header__lang-name"><?php echo esc_html( $adaptours_lang['name'] ); ?></span>
					</span>
				<?php else : ?>
					<a
						class="site-header__lang-link"
						href="<?php echo esc_url( $adaptours_lang['url'] ); ?>"
						lang="<?php echo esc_attr( $adaptours_lang['locale'] ); ?>"
						hreflang="<?php echo esc_attr( $adaptours_lang['locale'] ); ?>"
					>
						<span class="site-header__lang-flag" aria-hidden="true"><?php echo $adaptours_flag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — SVG déjà échappé ?></span>
						<span class="site-header__lang-name"><?php echo esc_html( $adaptours_lang['name'] ); ?></span>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
