ok_add_event( document, 'DOMContentLoaded', posts_init );

function posts_init()
{
	ok_add_event( document.getElementById( 'update-post' ), 'submit', posts_update_post );	
}

function posts_update_post( e )
{
	e.preventDefault();
	
	//abort previous calls
	if( xhr )
		xhr.abort();
	
//	input.parentNode.className = input.parentNode.className.replace( ' loading', '' );
//	input.parentNode.className += ' loading';
	
	var form_data = ok_serialize_form( this );
	
	var xhr = new ok_ajax();
	xhr.on_success( function(php_script_response) {
		console.log( '============ ajax response =============' );
		console.log( php_script_response );
		} );
	xhr.send( "post", ajax_uri, form_data, "html" );
}


function post_types_remove_post_type( e )
{
	e.preventDefault();
	
	for( var container = this; container && container.tagName != 'TR'; container = container.parentNode ) {}
	
	if( container )
	{
		container.parentNode.removeChild( container );
	}
	
	show_auto_submit();
}