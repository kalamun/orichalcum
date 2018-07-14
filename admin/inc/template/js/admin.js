/* on DOM is loaded */

ok_add_event( document, 'DOMContentLoaded', on_dom_loaded );
function on_dom_loaded()
{

	/* navigation menu */
	if( document.getElementsByTagName( 'header' )[0] )
	{
		ok_add_event( document.getElementsByTagName( 'header' )[0], 'mouseover', on_mouse_over_header );
		ok_add_event( document.getElementsByTagName( 'header' )[0], 'mouseout', on_mouse_out_header );
	}
	
}


/* navigation menu */
function on_mouse_over_header()
{
	on_mouse_out_header();
	document.body.className += ' menu-expanded';
}

function on_mouse_out_header()
{
	document.body.className = document.body.className.replace( 'menu-expanded', '' );
	document.body.className = document.body.className.replace( / +/, '' );
}

