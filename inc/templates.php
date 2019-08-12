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
* print head section
*/
function ok_head()
{
	// print enqueued scripts
	$scripts = "";
	
	if( !empty( $GLOBALS['ok_scripts'] ) )
	{
		foreach( $GLOBALS['ok_scripts'] as $script )
		{
			if( $script['position'] != "head" ) continue;
			
			if( !isset( $script['attributes']['charset'] ) )
				$script['attributes']['charset'] = "UTF-8";
			
			$attributes = "";
			foreach( $script['attributes'] as $param => $value )
			{
				$attributes .= ' ' . $param . '="' . esc_attr($value) .'"';
			}
			
			$url = $script['url'];
			if( !empty( $script['version'] ) )
			{
				$url .= strpos( $url, "?" ) === false ? "?" : "&";
				$url .= $script['version'];
			}
			
			$scripts .= '<script type="text/javascript" src="' . $url . '"' . $attributes . '></script>' . "\n";
		}
	}
	
	// print enqueued styles
	$styles = "";
	
	if( !empty( $GLOBALS['ok_styles'] ) )
	{
		foreach( $GLOBALS['ok_styles'] as $style )
		{
			if( $style['position'] != "head" ) continue;
			
			if( !isset( $style['attributes']['media'] ) )
				$style['attributes']['media'] = "*";
			
			$attributes = "";
			foreach( $style['attributes'] as $param => $value )
			{
				$attributes .= ' ' . $param . '="' . esc_attr($value) .'"';
			}
			
			$url = $style['url'];
			if( !empty( $style['version'] ) )
			{
				$url .= strpos( $url, "?" ) === false ? "?" : "&";
				$url .= $style['version'];
			}
			
			$styles .= '<link rel="stylesheet" href="' . $url . '"' . $attributes . '">' . "\n";
		}
	}
	
	echo $scripts . $styles;
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
* escape text for texrtarea purpose
*/
function esc_textarea( $attr )
{
	$attr = htmlspecialchars( $attr );
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

	if( empty( $attr['name'] ) && empty( $attr['id'] ) )
		$attr['name'] = 'unnamed';
	
	if( empty( $attr['id'] ) )
	{
		$attr['id'] = $attr['name'] . '-' . rand( 10000, 99999 );
	}
	
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
		if( $key == "checked" && $value == false )
			continue;
		
		$html .= ' ' . $key . '="' . esc_attr( $value ) .'"';
	}
	$html .= '>';
	
	// always print the label for checkboxes, 'cause it helps to visually customize them
	if( ( !empty( $label ) || $attr['type'] == "checkbox" || $attr['type'] == "radio" ) && !$label_printed )
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
* returns a textarea
* all attribute names in lowercase
* optional label position accepts: before | after
*/
function ok_textarea( $attr, $label = "", $editor = false )
{
	$html = '';
	$label_printed = false;

	if( empty( $attr['name'] ) && empty( $attr['id'] ) )
		$attr['name'] = 'unnamed';
	
	if( empty( $attr['id'] ) )
	{
		$attr['id'] = $attr['name'] . '-' . rand( 10000, 99999 );
	}
	
	if( $editor != false )
	{
		if( empty( $attr['class'] ) )
			$attr['class'] = "";
		
		$attr['class'] .= " " . $editor;
		$attr['class'] = trim( $attr['class'] );
	}
	
	if( !empty($label) )
	{
		$html .= '<label';
		if( !empty( $attr['class'] ) ) $html .= ' class="' . esc_attr( $attr['class'] ) . '"';
		if( !empty( $attr['id'] ) ) $html .= ' for="' . esc_attr( $attr['id'] ) . '"';
		$html .= '>' . $label .'</label>';
	}

	$html .= '<textarea';
	foreach( $attr as $key => $value )
	{
		if( $key == "checked" && $value == false )
			continue;
		
		if( $key == "value" )
			continue;
		
		$html .= ' ' . $key . '="' . esc_attr( $value ) .'"';
	}
	$html .= '>';
	
	if( isset( $attr['value'] ) )
		$html .= esc_textarea( $attr['value'] );
	
	$html .= '</textarea>';
		
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


/*
* format date from yyyy-mm-dd to dd/mm/yyyy
*/
function decode_date( $date )
{
	$date = trim( $date );
	$date = strtotime( $date );
	return date('d/m/Y', $date );
}

/*
* format date and time
*/
function decode_datetime( $date )
{
	$date = trim( $date );
	$date = strtotime( $date );
	return date('d/m/Y H:i', $date );
}

/*
* return a nice date output
*/
function get_nice_date( $date )
{
	$date = trim( $date );
	$date = strtotime( $date );
	
	if( date("Y-m-d") == date("Y-m-d", $date) ) $output = "Oggi";
	elseif( date("Y-m-d", time()-86400) == date("Y-m-d", $date) ) $output = "Ieri";
	elseif( date("Y-m-d", time()+86400) == date("Y-m-d", $date) ) $output = "Domani";
	elseif( date("Y-m-d", time()-(86400*7)) == date("Y-m-d", $date) ) $output = "una settimana fa";
	elseif( date("Y-m-d", time()+(86400*7)) == date("Y-m-d", $date) ) $output = "tra una settimana";
	elseif( date("Y-m-d", time()+(86400*14)) == date("Y-m-d", $date) ) $output = "tra due settimane";
	elseif( date("Y-m-d", time()+(86400*21)) == date("Y-m-d", $date) ) $output = "tra tre settimane";
	elseif( date("Y-m-d", time()+(86400*28)) == date("Y-m-d", $date) ) $output = "tra quattro settimane";
	elseif( date("Y-m-d", time()+(86400*30)) == date("Y-m-d", $date) ) $output = "tra un mese";
	elseif( time() < $date && time()+(86400*30) > $date ) $output = "tra ". round( ($date-time()) / 86400 ) ." giorni";
	elseif( time() > $date && time()-(86400*30) < $date ) $output = round( (time()-$date) / 86400 ) ." giorni fa";
	else $output = strftime('%d %B %Y', $date );
	
	return $output;
}

