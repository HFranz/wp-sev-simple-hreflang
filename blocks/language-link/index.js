( function ( hooks ) {
	// core/navigation restricts its own children to a fixed allowedBlocks
	// list, so a plain "parent" declaration in block.json isn't enough to
	// make this block insertable there — it has to be added explicitly.
	hooks.addFilter(
		'blocks.registerBlockType',
		'sev-simple-hreflang/extend-navigation-allowed-blocks',
		function ( settings, name ) {
			if ( 'core/navigation' !== name ) {
				return settings;
			}

			var allowedBlocks = settings.allowedBlocks || [];

			if ( allowedBlocks.indexOf( 'sev-simple-hreflang/language-link' ) >= 0 ) {
				return settings;
			}

			return Object.assign( {}, settings, {
				allowedBlocks: allowedBlocks.concat( [ 'sev-simple-hreflang/language-link' ] ),
			} );
		}
	);
} )( window.wp.hooks );

( function ( blocks, blockEditor, element, components, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;

	function HreflangLanguageLinkEdit( props ) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var blockProps = useBlockProps( { className: 'wp-block-navigation-item' } );
		var label = attributes.label || attributes.hreflang || __( 'Language link', 'sev-simple-hreflang' );
		var previewChildren = [];

		if ( attributes.icon ) {
			previewChildren.push(
				el( 'span', { className: 'wp-block-navigation-item__icon', key: 'icon' }, attributes.icon )
			);
		}

		previewChildren.push( el( 'span', { className: 'wp-block-navigation-item__label', key: 'label' }, label ) );

		return el(
			element.Fragment,
			null,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: __( 'hreflang settings', 'sev-simple-hreflang' ) },
					el( TextControl, {
						label: __( 'Target hreflang code', 'sev-simple-hreflang' ),
						placeholder: 'en-US',
						help: __( 'This item only appears on pages that have a matching hreflang alternate.', 'sev-simple-hreflang' ),
						value: attributes.hreflang,
						onChange: function ( value ) {
							setAttributes( { hreflang: value } );
						},
					} ),
					el( TextControl, {
						label: __( 'Label', 'sev-simple-hreflang' ),
						placeholder: attributes.hreflang || __( 'e.g. English', 'sev-simple-hreflang' ),
						value: attributes.label,
						onChange: function ( value ) {
							setAttributes( { label: value } );
						},
					} ),
					el( TextControl, {
						label: __( 'Icon (optional)', 'sev-simple-hreflang' ),
						placeholder: __( 'e.g. 🇬🇧', 'sev-simple-hreflang' ),
						help: __( 'Any short text or emoji, shown before the label.', 'sev-simple-hreflang' ),
						value: attributes.icon,
						onChange: function ( value ) {
							setAttributes( { icon: value } );
						},
					} ),
					el( TextControl, {
						label: __( 'Tooltip (optional)', 'sev-simple-hreflang' ),
						placeholder: __( 'e.g. Go to the English version of this page.', 'sev-simple-hreflang' ),
						value: attributes.tooltip,
						onChange: function ( value ) {
							setAttributes( { tooltip: value } );
						},
					} )
				)
			),
			el(
				'li',
				blockProps,
				el(
					'a',
					{
						className: 'wp-block-navigation-item__content',
						href: '#',
						title: attributes.tooltip || undefined,
						onClick: function ( event ) { event.preventDefault(); },
					},
					previewChildren
				)
			)
		);
	}

	blocks.registerBlockType( 'sev-simple-hreflang/language-link', {
		edit: HreflangLanguageLinkEdit,
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.i18n );
