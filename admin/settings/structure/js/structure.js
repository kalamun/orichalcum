ok_add_event( document, 'DOMContentLoaded', post_types_init_post_types );

function post_types_init_post_types()
{
	for( var i=0, c=document.querySelectorAll( '.remove-post-type' ); c[i]; i++ )
	{
		ok_add_event( c[i], 'click', post_types_remove_post_type );
	}
	
	ok_add_event( document.getElementById( 'add-post-type' ), 'submit', post_types_add_post_type );	
}

function post_types_add_post_type( e )
{
	e.preventDefault();
	
	//abort previous calls
	if( xhr )
		xhr.abort();
	
//	input.parentNode.className = input.parentNode.className.replace( ' loading', '' );
//	input.parentNode.className += ' loading';
	
	var form_data = new FormData();                  
	form_data.append('action', 'do_ajax');
	form_data.append('fn', 'add-post-type');
	
	for( let i=0, c=this.querySelectorAll( 'input,select,textarea' ); c[i]; i++ )
	{
		if( !c[i].name )
			continue;
		
		if( ( c[i].type == 'checkbox' || c[i].type == 'radio' ) && !c[i].checked )
			continue;

		let val = c[i].value;
		form_data.append( c[i].name, val);
	}
	
	var xhr = new ok_ajax();
	xhr.on_success( function(php_script_response) {
			console.log( '============ ajax response =============' );
			console.log( php_script_response );
		} );
	xhr.send( "get", ajax_uri, form_data, "html" );
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