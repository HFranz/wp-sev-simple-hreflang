( function ( plugins, editPost, element, components, data, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var PluginDocumentSettingPanel = editPost.PluginDocumentSettingPanel;
	var PanelRow = components.PanelRow;
	var TextControl = components.TextControl;
	var Button = components.Button;
	var useSelect = data.useSelect;
	var useDispatch = data.useDispatch;

	var META_KEY = 'hreflang_alternates';

	function HreflangPanel() {
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var editPostMeta = useDispatch( 'core/editor' ).editPost;

		var alternates = meta[ META_KEY ] || [];

		function updateAlternates( nextAlternates ) {
			editPostMeta( { meta: Object.assign( {}, meta, { [ META_KEY ]: nextAlternates } ) } );
		}

		function updateRow( index, field, value ) {
			var next = alternates.slice();
			next[ index ] = Object.assign( {}, next[ index ], { [ field ]: value } );
			updateAlternates( next );
		}

		function removeRow( index ) {
			var next = alternates.slice();
			next.splice( index, 1 );
			updateAlternates( next );
		}

		function addRow() {
			updateAlternates( alternates.concat( [ { hreflang: '', href: '' } ] ) );
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'sev-hreflang-panel',
				title: __( 'hreflang (alternative Sprachversionen)', 'sev-hreflang' ),
				icon: 'translation',
			},
			alternates.map( function ( row, index ) {
				return el(
					Fragment,
					{ key: index },
					el(
						PanelRow,
						null,
						el( TextControl, {
							label: __( 'hreflang', 'sev-hreflang' ),
							placeholder: 'en-US',
							value: row.hreflang || '',
							onChange: function ( value ) {
								updateRow( index, 'hreflang', value );
							},
						} )
					),
					el(
						PanelRow,
						null,
						el( TextControl, {
							label: __( 'URL', 'sev-hreflang' ),
							type: 'url',
							placeholder: 'https://example.com/en/page/',
							value: row.href || '',
							onChange: function ( value ) {
								updateRow( index, 'href', value );
							},
						} )
					),
					el(
						PanelRow,
						null,
						el(
							Button,
							{
								isDestructive: true,
								variant: 'link',
								onClick: function () {
									removeRow( index );
								},
							},
							__( 'Zeile entfernen', 'sev-hreflang' )
						)
					)
				);
			} ),
			el(
				Button,
				{ variant: 'secondary', onClick: addRow },
				__( 'Sprachversion hinzufügen', 'sev-hreflang' )
			)
		);
	}

	plugins.registerPlugin( 'sev-hreflang-sidebar', {
		render: HreflangPanel,
	} );
} )( window.wp.plugins, window.wp.editPost, window.wp.element, window.wp.components, window.wp.data, window.wp.i18n );
