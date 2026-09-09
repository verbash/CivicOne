( function ( wp ) {
	var el = wp.element.createElement,
		useBlockProps = wp.blockEditor.useBlockProps,
		SSR = wp.serverSideRender,
		__ = wp.i18n.__;

	wp.blocks.registerBlockType( 'civicone/section-subnav', {
		edit: function () {
			return el(
				'div',
				useBlockProps(),
				el( SSR, { block: 'civicone/section-subnav' } )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
