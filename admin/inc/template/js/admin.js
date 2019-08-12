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
	
	/* activate file fields to automatically start uploads */
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


/*
* open upload gallery
*/

function open_uploads_gallery( callback )
{
	open_modal( admin_uri + '/uploads/uploads_gallery.php' );
}


/*
* retrive if modal window exists (is open)
*/
function modal_exists()
{
	return !!document.getElementById( 'modal' );
}

/*
* open modal window
*/
function open_modal( url, vars )
{
	close_modal();
	
	var modal = document.createElement( 'DIV' );
	modal.id = 'modal';
	document.body.appendChild( modal );
	
	var modal_bkg = document.createElement( 'DIV' );
	modal_bkg.id = 'modal-bkg';
	document.body.appendChild( modal_bkg );
	
	var ajax = new ok_ajax;

	ajax.on_success( set_modal_content );
	ajax.on_fail( failure_callback );
	
	ajax.send( "post", url, vars, "html");
}

/*
* append content to modal
*/
function set_modal_content( html )
{
	if( !modal_exists )
		return false;
	
	var modal = document.getElementById( 'modal' );
	ok_append( html, modal );
}

/*
* close modal window
*/
function close_modal()
{
	var modal = document.getElementById( 'modal' );
	if( modal )
	{
		var modal_bkg = document.getElementById( 'modal-bkg' );
		modal.parentNode.removeChild( modal );
		modal_bkg.parentNode.removeChild( modal_bkg );
	}
}


/*
* upload files, included large ones
*/
function ok_upload( upload_field )
{
	var reader = {};
	var file = {};
	var slice_size = 1000 * 1024;
	var file_input = upload_field;

	function start_upload( e )
	{
		e.preventDefault();
		
		reader = new FileReader();
		file = file_input.files[0];

		upload_file( 0 );
	}
	
	file_input.addEventListener( 'change', start_upload );

	function upload_file( start )
	{
		var next_slice = start + slice_size + 1;
		var blob = file.slice( start, next_slice );

		reader.addEventListener( 'loadend', on_load_end );
		reader.readAsDataURL( blob );
	}

	function on_load_end( e )
	{
		if ( e.target.readyState !== FileReader.DONE )
			return;
		
		var ajax = new ok_ajax();
		
		if( success_callback )
			ajax.on_success( on_success );
		if( failure_callback )
			ajax.on_fail( on_error );
		
		ajax.send( "post", ajax_uri, form_values, "json");

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {
				action: 'dbi_upload_file',
				file_data: event.target.result,
				file: file.name,
				file_type: file.type,
				nonce: dbi_vars.upload_file_nonce
			},
			error: on_error,
			success: on_success,
		} );
	};

	function on_error( jqXHR, textStatus, errorThrown )
	{
		console.log( jqXHR, textStatus, errorThrown );
	}
	
	function on_success( data )
	{
		var size_done = start + slice_size;
		var percent_done = Math.floor( ( size_done / file.size ) * 100 );
		
		if( next_slice < file.size )
		{
			// Update upload progress
			$( '#dbi-upload-progress' ).html( 'Uploading File - ' + percent_done + '%' );

			// More to upload, call function recursively
			upload_file( next_slice );
		} else {
			// Update upload progress
			$( '#dbi-upload-progress' ).html( 'Upload Complete!' );
		}
	}
}

