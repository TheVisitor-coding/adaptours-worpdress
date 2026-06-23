<?php
/**
 * Métabox galerie native (CPT destination).
 *
 * Sélection multiple via la médiathèque WP, stockée en post meta `_adaptours_gallery_ids`
 * (tableau d'IDs d'attachements) et consommée par le bloc adaptours/destination-gallery.
 * Aucune dépendance ACF.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ADAPTOURS_GALLERY_META  = '_adaptours_gallery_ids';
const ADAPTOURS_GALLERY_NONCE = 'adaptours_gallery_nonce';

/**
 * Déclare la métabox sur l'écran d'édition d'une destination.
 */
function adaptours_gallery_add_metabox() {
	add_meta_box(
		'adaptours_gallery',
		__( 'Galerie de la destination', 'adaptours' ),
		'adaptours_gallery_render_metabox',
		'destination',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'adaptours_gallery_add_metabox' );

/**
 * Affiche la métabox : aperçu des images sélectionnées + champ caché (CSV d'IDs).
 *
 * @param WP_Post $post Post courant.
 */
function adaptours_gallery_render_metabox( $post ) {
	wp_nonce_field( 'adaptours_gallery_save', ADAPTOURS_GALLERY_NONCE );

	$ids = get_post_meta( $post->ID, ADAPTOURS_GALLERY_META, true );
	$ids = is_array( $ids ) ? array_map( 'absint', $ids ) : array();
	?>
	<div class="adaptours-gallery" data-adaptours-gallery>
		<p class="description">
			<?php esc_html_e( 'Sélectionnez les images de la galerie depuis la médiathèque. L\'ordre est conservé.', 'adaptours' ); ?>
		</p>

		<ul class="adaptours-gallery__preview" data-gallery-preview>
			<?php foreach ( $ids as $id ) : ?>
				<?php $thumb = wp_get_attachment_image( $id, 'thumbnail' ); ?>
				<?php if ( $thumb ) : ?>
					<li data-id="<?php echo esc_attr( $id ); ?>"><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() retourne du HTML sûr. ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

		<p>
			<button type="button" class="button" data-gallery-select>
				<?php esc_html_e( 'Sélectionner des images', 'adaptours' ); ?>
			</button>
			<button type="button" class="button-link" data-gallery-clear>
				<?php esc_html_e( 'Tout retirer', 'adaptours' ); ?>
			</button>
		</p>

		<input
			type="hidden"
			name="adaptours_gallery_ids"
			data-gallery-input
			value="<?php echo esc_attr( implode( ',', $ids ) ); ?>"
		/>
	</div>
	<?php
}

/**
 * Charge la médiathèque WP et le script de la métabox sur l'écran destination.
 *
 * @param string $hook Hook de la page admin courante.
 */
function adaptours_gallery_enqueue( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'destination' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();

	$js = <<<'JS'
( function() {
	var root = document.querySelector( '[data-adaptours-gallery]' );
	if ( ! root || typeof wp === 'undefined' || ! wp.media ) {
		return;
	}

	var input   = root.querySelector( '[data-gallery-input]' );
	var preview = root.querySelector( '[data-gallery-preview]' );
	var frame;

	function currentIds() {
		return ( input.value || '' ).split( ',' ).map( function( v ) {
			return parseInt( v, 10 );
		} ).filter( function( v ) {
			return v > 0;
		} );
	}

	function render( attachments ) {
		input.value = attachments.map( function( a ) { return a.id; } ).join( ',' );
		preview.innerHTML = '';
		attachments.forEach( function( a ) {
			var size = ( a.sizes && a.sizes.thumbnail ) ? a.sizes.thumbnail.url : a.url;
			var li = document.createElement( 'li' );
			li.setAttribute( 'data-id', a.id );
			var img = document.createElement( 'img' );
			img.src = size;
			img.alt = a.alt || '';
			li.appendChild( img );
			preview.appendChild( li );
		} );
	}

	root.querySelector( '[data-gallery-select]' ).addEventListener( 'click', function( e ) {
		e.preventDefault();
		if ( frame ) {
			frame.open();
			return;
		}
		frame = wp.media( {
			title: '__GALLERY_TITLE__',
			multiple: 'add',
			library: { type: 'image' }
		} );
		frame.on( 'open', function() {
			var selection = frame.state().get( 'selection' );
			currentIds().forEach( function( id ) {
				var att = wp.media.attachment( id );
				att.fetch();
				selection.add( att );
			} );
		} );
		frame.on( 'select', function() {
			render( frame.state().get( 'selection' ).toJSON() );
		} );
		frame.open();
	} );

	root.querySelector( '[data-gallery-clear]' ).addEventListener( 'click', function( e ) {
		e.preventDefault();
		input.value = '';
		preview.innerHTML = '';
	} );
}() );
JS;

	$js = str_replace(
		'__GALLERY_TITLE__',
		esc_js( __( 'Galerie de la destination', 'adaptours' ) ),
		$js
	);

	wp_add_inline_script( 'media-editor', $js );
}
add_action( 'admin_enqueue_scripts', 'adaptours_gallery_enqueue' );

/**
 * Enregistre la galerie à la sauvegarde d'une destination.
 *
 * @param int $post_id ID du post sauvegardé.
 */
function adaptours_gallery_save( $post_id ) {
	if ( ! isset( $_POST[ ADAPTOURS_GALLERY_NONCE ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST[ ADAPTOURS_GALLERY_NONCE ] ), 'adaptours_gallery_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['adaptours_gallery_ids'] ) ? wp_unslash( $_POST['adaptours_gallery_ids'] ) : '';
	$ids = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );

	// Ne conserver que les IDs pointant réellement sur un attachement.
	$ids = array_values(
		array_filter(
			$ids,
			static function ( $id ) {
				return 'attachment' === get_post_type( $id );
			}
		)
	);

	if ( empty( $ids ) ) {
		delete_post_meta( $post_id, ADAPTOURS_GALLERY_META );
		return;
	}

	update_post_meta( $post_id, ADAPTOURS_GALLERY_META, $ids );
}
add_action( 'save_post_destination', 'adaptours_gallery_save' );
