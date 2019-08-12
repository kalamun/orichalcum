"strict mode";

ok_add_event( document, 'DOMContentLoaded', init_content_editor );


function init_content_editor()
{
	let textarea = document.querySelectorAll( 'textarea.content_editor' );
	
	for( let i=0; textarea[i]; i++ )
	{
		let content = new ok_content_editor();
		content.init( textarea[i] );
	}
}

function ok_content_editor()
{
	let textarea = null,
		container = null,
		editor = null;
	
	this.init = function( t )
	{
		textarea = t;
		
		// create container
		container = document.createElement('DIV');
		container.className = 'content_editor_container';
		
		textarea.parentNode.insertBefore( container, textarea );
		container.appendChild( textarea );
		
		// create editor
		editor = document.createElement('DIV');
		editor.className = 'editor';
		container.appendChild( editor );
		if( textarea.value != "" )
			editor.innerHTML = textarea.value;
		else {
			var p = document.createElement('P');
			p.appendChild( document.createTextNode( textarea.getAttribute( "placeholder" ) ) )
			editor.appendChild( p );
		}
		
		editor.contentEditable = "true";
		//document.execCommand("insertBrOnReturn", false, "true");
		document.execCommand("returnInParagraphCreatesNewParagraph", false, "true");
		
		// set editor events
		editor.addEventListener( 'keydown', on_key_down, true );
		editor.addEventListener( 'keyup', on_key_up, true );
	}
	
	function on_key_down( e )
	{
		if( e.ctrlKey && e.key == "b" ) {
			e.preventDefault();
			set_bold();
		} else if( e.ctrlKey && e.key == "i" ) {
			e.preventDefault();
			set_italic();
/*		} else if( e.ctrlKey && e.key == "u" ) {
			e.preventDefault();
			set_underline();
*/
		} else if( e.ctrlKey && e.key == "1" ) {
			e.preventDefault();
			set_tag( "h1" );
		} else if( e.ctrlKey && e.key == "2" ) {
			e.preventDefault();
			set_tag( "h2" );
		} else if( e.ctrlKey && e.key == "3" ) {
			e.preventDefault();
			set_tag( "h3" );
		} else if( e.ctrlKey && e.key == "4" ) {
			e.preventDefault();
			set_tag( "h4" );
		} else if( e.ctrlKey && e.key == "5" ) {
			e.preventDefault();
			set_tag( "h5" );
		} else if( e.ctrlKey && e.key == "6" ) {
			e.preventDefault();
			set_tag( "h6" );
		} else if( e.ctrlKey && e.key == "7" ) {
			e.preventDefault();
			set_tag( "p" );
		} else if( e.ctrlKey && e.key == "u" ) {
			e.preventDefault();
			open_uploads_gallery( insert_file );
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

	function set_tag( tag )
	{
		document.execCommand("formatblock", false, "<" + tag + ">");
	}
	
	function insert_file( file )
	{
		
	}
}

