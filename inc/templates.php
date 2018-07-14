<?php

/*
* TEMPLATE FUNCTIONS
*/


/*
* tells if the current page is the home page
*/
function is_front_page()
{
	/* to do */
}


/*
* tells if the current page is located into admin dir
*/
function is_backend()
{
	$current_url = rtrim( $_SERVER['REQUEST_URI'], "/" );
	if( stripos( $current_url, get_admin_directory_uri() ) === 0 ) return true;
	return false;
}


/*
* include header
*/
function ok_header( $header = "" )
{
	// sanitize header name
	$heder = trim( $header, "./" );
	if( substr( $header, -4 ) == ".php" ) $header = substr( $header, 0, -4 );
	$heder = trim( $header, "./" );
	
	if( empty( $header ) ) $header = "header.php";
	else $header = "header-" . $header . ".php";
	
	load_template_part( $header );
}

/*
* include footer
*/
function ok_footer( $footer = "" )
{
	// sanitize header name
	$heder = trim( $footer, "./" );
	if( substr( $footer, -4 ) == ".php" ) $footer = substr( $footer, 0, -4 );
	$heder = trim( $footer, "./" );
	
	if( empty( $footer ) ) $footer = "footer.php";
	else $footer = "footer-" . $footer . ".php";
	
	load_template_part( $footer );
}

/*
* load login page
*/
function ok_login_page()
{
	$login_template = get_site_directory() . '/inc/template/login.php';
	if( file_exists( $login_template ) )
	{
		include( $login_template );
	}
	exit;
}


/*
* redirect to a URL, using header location or meta refresh
*/
function ok_redirect( $url )
{
	if( !headers_sent() )
	{
		header( 'Location: ' . $url );
		exit;
	}
	
	?>
	<meta http-equiv="refresh" content="0; url=<?= esc_attr( $url ); ?>">
	<?php
}


/*
* escape html attributes
*/
function esc_attr( $attr )
{
	$attr = str_replace( '"', '&quot;', $attr );
	return $attr;
}


/*
* escape URLs
*/
function esc_url( $url )
{
	$url = urlencode( $url );
	return $url;
}


/*
* returns an input field
* all attribute names in lowercase
* optional label position accepts: before | after
*/
function ok_input( $attr, $label = "", $label_position = null )
{
	$html = '';
	$label_printed = false;
	
	if( !empty($label) && ( ( !empty( $attr['type'] ) && $attr['type'] != 'checkbox' && $attr['type'] != 'radio' && $label_position === null ) || $label_position == "before" ) )
	{
		$html .= '<label';
		if( !empty( $attr['class'] ) ) $html .= ' class="' . esc_attr( $attr['class'] ) . '"';
		if( !empty( $attr['id'] ) ) $html .= ' for="' . esc_attr( $attr['id'] ) . '"';
		$html .= '>' . $label .'</label>';
		$label_printed = true;
	}

	$html .= '<input';
	foreach( $attr as $key => $value )
	{
		$html .= ' ' . $key . '="' . esc_attr( $value ) .'"';
	}
	$html .= '>';
	
	if( !empty( $label ) && !$label_printed )
	{
		$html .= '<label';
		if( !empty( $attr['class'] ) ) $html .= ' class="' . esc_attr( $attr['class'] ) . '"';
		if( !empty( $attr['id'] ) ) $html .= ' for="' . esc_attr( $attr['id'] ) . '"';
		$html .= '>' . $label .'</label>';
		$label_printed = true;
	}
	
	return $html;
}


/*
* load a file into template
*/
function load_template_part( $filename )
{
	// sanitize filename
	trim( $filename, "/." );
	
	$template = get_template_directory() . '/' . $filename;
	
	if( !file_exists( $template ) ) return false;
	
	include( $template );
	return true;
}


/*
* returns the template part content once processed
*/
function get_template_part( $filename )
{
	ob_start();
	load_template_part( $filename );
	return ob_get_clean();
}

