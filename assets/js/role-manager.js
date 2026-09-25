( function () {
	'use strict';
	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.wpfep-role-notice' ).forEach( function ( notice ) { window.setTimeout( function () { notice.remove(); }, 4000 ); } );
		var button = document.querySelector( '.wpfep-add-user-toggle' );
		var panel = document.getElementById( 'wpfep-add-user-panel' );
		if ( button && panel ) {
			button.addEventListener( 'click', function () { panel.hidden = ! panel.hidden; button.setAttribute( 'aria-expanded', String( ! panel.hidden ) ); button.textContent = panel.hidden ? wpfepRoleManager.addUserText : wpfepRoleManager.hideAddUserText; } );
		}
		var searchForm = document.querySelector( '.wpfep-user-search-form' );
		var searchInput = document.getElementById( 'wpfep-user-search' );
		var searchTimer;
		if ( searchForm && searchInput ) {
			searchInput.addEventListener( 'input', function () {
				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( function () { searchForm.submit(); }, 400 );
			} );
		}
		document.querySelectorAll( '.wpfep-role-select' ).forEach( function ( select ) {
			select.addEventListener( 'change', function () {
				var previousRole = select.dataset.currentRole;
				select.disabled = true;
				fetch( wpfepRoleManager.ajaxUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: new URLSearchParams( { action: 'wpfep_update_role', user_id: select.dataset.userId, role: select.value, nonce: wpfepRoleManager.nonce } ) } )
					.then( function ( response ) { return response.json(); } )
					.then( function ( response ) { if ( ! response.success ) { throw new Error( response.data && response.data.message ? response.data.message : wpfepRoleManager.genericErrorText ); } select.dataset.currentRole = response.data.role; var badge = select.closest( 'tr' ).querySelector( '.wpfep-role-badge' ); badge.className = 'wpfep-role-badge wpfep-role-' + response.data.role; badge.textContent = response.data.roleName; } )
					.catch( function ( error ) { select.value = previousRole; window.alert( error.message ); } )
					.finally( function () { select.disabled = false; } );
			} );
		} );
	} );
}() );
