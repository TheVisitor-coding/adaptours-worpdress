/**
 * Bloc adaptours/team-grid-member — composant d'édition d'une personne de l'équipe.
 *
 * Édition inline : nom + poste + petite phrase en RichText, photo via la médiathèque.
 * Pas de style.scss propre : le layout est porté par le bloc parent adaptours/team-grid.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps( { className: 'team-grid__card' } );
		const { photo_id: photoId, name, role, tagline } = attributes;

		const media = useSelect(
			( select ) => ( photoId ? select( coreStore ).getMedia( photoId ) : null ),
			[ photoId ]
		);
		const photoUrl =
			media?.media_details?.sizes?.medium?.source_url || media?.source_url || '';

		return (
			<li { ...blockProps }>
				<MediaUploadCheck>
					<MediaUpload
						allowedTypes={ [ 'image' ] }
						value={ photoId }
						onSelect={ ( m ) => setAttributes( { photo_id: m.id } ) }
						render={ ( { open } ) => (
							<button
								type="button"
								className={ `team-grid__photo${ photoUrl ? '' : ' team-grid__photo--placeholder' }` }
								onClick={ open }
							>
								{ photoUrl ? <img src={ photoUrl } alt="" /> : <span>{ __( 'Photo', 'adaptours' ) }</span> }
							</button>
						) }
					/>
				</MediaUploadCheck>
				<div className="team-grid__card-body">
					<RichText
						tagName="h3"
						className="team-grid__name"
						value={ name }
						allowedFormats={ [] }
						onChange={ ( v ) => setAttributes( { name: v } ) }
						placeholder={ __( 'Nom', 'adaptours' ) }
					/>
					<RichText
						tagName="p"
						className="team-grid__role"
						value={ role }
						allowedFormats={ [] }
						onChange={ ( v ) => setAttributes( { role: v } ) }
						placeholder={ __( 'Poste', 'adaptours' ) }
					/>
					<p className="team-grid__tagline">
						<span className="team-grid__tagline-mark" aria-hidden="true">✦</span>
						<RichText
							tagName="span"
							className="team-grid__tagline-text"
							value={ tagline }
							allowedFormats={ [] }
							onChange={ ( v ) => setAttributes( { tagline: v } ) }
							placeholder={ __( 'Petite phrase', 'adaptours' ) }
						/>
					</p>
				</div>
			</li>
		);
	},
	save: () => null,
} );
