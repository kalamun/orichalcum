/* on DOM is loaded */

ok_add_event( document, 'DOMContentLoaded', on_dom_loaded );
function on_dom_loaded()
{
	/* display submit when something changes on forms where submit is set as auto-appearing */
	var submit = document.querySelector( 'section.submit.onchange' );
	if( submit )
	{
		for( var i = 0, f = document.getElementsByTagName( 'form' ); f[i]; i++ )
		{
			for( var c = 0, input = f[i].getElementsByTagName( 'input' ); input[c]; c++ )
			{
				ok_add_event( input[c], 'change', show_auto_submit );
			}
			for( var c = 0, input = f[i].getElementsByTagName( 'select' ); input[c]; c++ )
			{
				ok_add_event( input[c], 'change', show_auto_submit );
			}
			for( var c = 0, input = f[i].getElementsByTagName( 'textarea' ); input[c]; c++ )
			{
				ok_add_event( input[c], 'change', show_auto_submit );
			}
			
			ok_add_event( f[i], 'submit', ok_post );
		}
	}
}


function show_auto_submit()
{
	var submit = document.querySelector( 'section.submit.onchange' );
		console.log(submit);
	
	if( submit.className.indexOf( ' visible' ) == -1 )
		submit.className += ' visible';
}

function hide_auto_submit()
{
	var submit = document.querySelector( 'section.submit.onchange' );
	
	if( submit.className.indexOf( ' visible' ) > -1 )
		submit.className = submit.className.replace( ' visible', '' );
}

/*
* post a form via AJAX
*/
success_callback = null;
failure_callback = null;

function ok_post( form )
{
	// variable form is an event, function called by form
	if( form.target )
	{
		form.preventDefault();
		form = this;
	}
		
	if( !form )
	{
		if( failure_callback )
			failure_callback();
		return false;
	}
	
	var submit_button = form.querySelector( 'input[type=submit]' );
	if( submit_button )
	{
		submit_button.setAttribute( 'disabled', 'disabled' );
	}
	
	var form_values = ok_serialize_form( form );

	var ajax = new ok_ajax();
	
	if( success_callback )
		ajax.on_success( success_callback );
	if( failure_callback )
		ajax.on_fail( failure_callback );
	
	ajax.send( "post", form.action, form_values, "html");

}
