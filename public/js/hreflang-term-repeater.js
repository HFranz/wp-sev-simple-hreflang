( function () {
	'use strict';

	function createRow( index, removeLabel ) {
		var row = document.createElement( 'div' );
		row.className = 'sevmatic-hreflang-row';

		var hreflangInput = document.createElement( 'input' );
		hreflangInput.type = 'text';
		hreflangInput.name = 'sevmatic_hreflang[' + index + '][hreflang]';
		hreflangInput.placeholder = 'en-US';

		var hrefInput = document.createElement( 'input' );
		hrefInput.type = 'url';
		hrefInput.name = 'sevmatic_hreflang[' + index + '][href]';
		hrefInput.placeholder = 'https://example.com/en/category/';

		var removeButton = document.createElement( 'button' );
		removeButton.type = 'button';
		removeButton.className = 'button sevmatic-hreflang-remove-row';
		removeButton.textContent = removeLabel;

		row.appendChild( hreflangInput );
		row.appendChild( hrefInput );
		row.appendChild( removeButton );

		return row;
	}

	document.addEventListener( 'click', function ( event ) {
		var addButton = event.target.closest( '.sevmatic-hreflang-add-row' );

		if ( addButton ) {
			event.preventDefault();

			var container = document.getElementById( addButton.dataset.target );

			if ( ! container ) {
				return;
			}

			container.appendChild( createRow( container.children.length, addButton.dataset.removeLabel ) );
			return;
		}

		var removeButton = event.target.closest( '.sevmatic-hreflang-remove-row' );

		if ( removeButton ) {
			event.preventDefault();
			removeButton.closest( '.sevmatic-hreflang-row' ).remove();
		}
	} );
} )();
