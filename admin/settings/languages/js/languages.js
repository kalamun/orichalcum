ok_add_event( document, 'DOMContentLoaded', languages_init_languages );

function languages_init_languages()
{
	for( var i=0, c=document.querySelectorAll( '.remove-language' ); c[i]; i++ )
	{
		ok_add_event( c[i], 'click', languages_remove_language );
	}
}

function languages_add_language()
{
	console.log( 'add' );
	
	//abort previous calls
	if( xhr )
		xhr.abort();
	
//	input.parentNode.className = input.parentNode.className.replace( ' loading', '' );
//	input.parentNode.className += ' loading';
	
	var form_data = new FormData();                  
	form_data.append('action', 'do_ajax');
	form_data.append('fn', 'print-language');
	form_data.append('s', 'it_IT');
	
	var xhr = new ok_ajax();
	xhr.on_success( function(php_script_response) {
			console.log( '============ ajax response =============' );
			console.log( php_script_response );
		} );
	xhr.send( "get", ajax_uri, form_data, "html" );
}


function languages_remove_language( e )
{
	e.preventDefault();
	
	for( var container = this; container && container.tagName != 'TR'; container = container.parentNode ) {}
	
	if( container )
	{
		container.parentNode.removeChild( container );
	}
	
	show_auto_submit();
}