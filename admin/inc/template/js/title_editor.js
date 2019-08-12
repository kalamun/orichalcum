"strict mode";

ok_add_event( document, 'DOMContentLoaded', init_title_editor );


function init_title_editor()
{
	let textarea = document.querySelectorAll( 'textarea.title_editor' );
	
	for( let i=0; textarea[i]; i++ )
	{
		let title = new ok_title_editor();
		title.init( textarea[i] );
	}
}

function ok_title_editor()
{
	let textarea = null,
		container = null,
		editor = null;
	
	this.init = function( t )
	{
		textarea = t;
		
		// create container
		container = document.createElement('DIV');
		container.className = 'title_editor_container';
		
		textarea.parentNode.insertBefore( container, textarea );
		container.appendChild( textarea );
		
		// create editor
		editor = document.createElement('DIV');
		editor.className = 'editor';
		container.appendChild( editor );
		if( textarea.value != "" )
			editor.innerHTML = textarea.value;
		else
			editor.innerHTML = textarea.getAttribute( 'placeholder' );
		editor.contentEditable = "true";
		
		// set editor events
		editor.addEventListener( 'keydown', on_key_down, true );
		editor.addEventListener( 'keyup', on_key_up, true );
	}
	
	function on_key_down( e )
	{
		if( e.keyCode == 13 ) // Enter
		{
			e.preventDefault();
			document.execCommand('insertHTML', false, '<br>');
		} else if( e.ctrlKey && e.key == "b" ) {
			e.preventDefault();
			set_bold();
		} else if( e.ctrlKey && e.key == "i" ) {
			e.preventDefault();
			set_italic();
		} else if( e.ctrlKey && e.key == "u" ) {
			e.preventDefault();
			set_underline();
		}
	}
	
	function on_key_up( e )
	{
		textarea.value = editor.innerHTML;
	}
	
	function set_bold()
	{
		document.execCommand("bold", false, false);
	}

	function set_italic()
	{
		document.execCommand("italic", false, false);
	}

	function set_underline()
	{
		document.execCommand("underline", false, false);
	}

	
}

