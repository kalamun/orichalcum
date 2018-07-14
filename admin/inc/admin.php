<?php

/*
* FUNCTIONS RESERVED TO ADMIN
*/

/*
* init admin
*/
function ok_init_admin()
{
	// sign on if credential are passed
	ok_admin_login();

	if( !is_user_logged_in() )
	{
		ok_admin_login_page();
		exit;
		
	} elseif( isset( $_GET['logout'] ) ) {
		ok_logout();
		ok_redirect( "?" );
	}
	
	do_action( "init_admin" );
}

/*
* include header
*/
function ok_admin_header()
{
	require_once( get_site_directory() . "/admin/inc/template/header.php" );
}

/*
* include footer
*/
function ok_admin_footer()
{
	require_once( get_site_directory() . "/admin/inc/template/footer.php" );
}

/*
* load login page
*/
function ok_admin_login_page()
{
	$login_template = get_site_directory() . '/admin/inc/template/login.php';
	if( file_exists( $login_template ) )
	{
		include( $login_template );
	}
	exit;
}

/*
* log-in into backend
*/
function ok_admin_login()
{
	if(
		empty( $_POST['login'] )
		|| empty( $_POST['username'] )
		|| empty( $_POST['password'] )
		|| empty( $_POST['nonce'] )
		) return false;

	if( !ok_verify_nonce( $_POST['nonce'], "login" ) ) return false;
	
	$credentials = [
		"username" => $_POST['username'],
		"password" => $_POST['password'],
		"type" => "ADMIN",
	];

	ok_signon( $credentials );
	ok_redirect( "" );
}


/***************
* ADMIN NAV
***************/

/*
* init admin menu if not inited yet
*/
function init_admin_nav()
{
	if( !isset( $GLOBALS['ok_nav_menu'] ) )
		$GLOBALS['ok_nav_menu'] = new admin_nav();
}

/*
* get admin menu
* returns an array of elements, each of them is an object with:
* -> label
* -> url
* -> selected (boolean)
* -> ancestor (boolean)
* -> submenu (array)
*/
function get_admin_nav()
{
	init_admin_nav();
	return $GLOBALS['ok_nav_menu']->get_structure();
}

/*
* print admin menu
*/
function print_admin_nav()
{
	init_admin_nav();
	echo $GLOBALS['ok_nav_menu']->get_html();
}

/*
* add element to admin menu
* $element is an array with these mandatary keys: label, url
* optionally a child key could be passed
* ancestor is currently unsupported
*/
function add_admin_nav_element( $element, $ancestor = null )
{
	init_admin_nav();
	return $GLOBALS['ok_nav_menu']->add_element( $element, $ancestor );
}



/*
* admin nav class
* input options: object | html
*/
class admin_nav
{
	protected $elements;
	
	public function __construct()
	{
		$this->elements = [];
	}
	
	public function add_element( $element, $ancestor = null )
	{
		if( empty( $element['label'] ) )
		{
			trigger_error( 'Admin nav: label must be defined' );
			return false;
		}
		
		if( empty( $element['url'] ) )
		{
			trigger_error( 'Admin nav: url must be defined' );
			return false;
		}
		if( empty( $ancestor ) )
		{
			$this->elements[] = $element;
		}
		
		/* to-do: add child elements */
		
		return true;
	}
	
	public function get_structure()
	{
		$nav = [];
		
		foreach( $this->elements as $e )
		{
			$nav[] = $this->get_structure_node( $e );
		}
		
		return $nav;
	}
	
	private function get_structure_node( $e )
	{
		$element = new stdClass();
		$element->label = $e['label'];
		$element->url = $e['url'];
		$element->selected = $this->is_selected( $e['url'] );
		$element->ancestor = false;
		
		if( !empty( $e['child'] ) )
		{
			$element->child = [];
			foreach( $e['child']  as $e_child )
			{
				$element->child[] = $this->get_structure_node( $e_child );
			}
			
			$element->ancestor = $this->is_ancestor( $element->child );
		}
		
		return $element;
	}
	
	public function get_html()
	{
		$nav = $this->get_structure();

		$html = '<ul>';
		
		foreach( $nav as $e )
		{
			$html .= $this->get_html_node( $e );
		}
		
		$html .= '</ul>';
		return $html;
	}
	
	private function get_html_node( $e )
	{
		$class = "";
		if( $e->selected ) $class .= " selected ";
		if( $e->ancestor ) $class .= " ancestor ";
		$class = trim( $class );
		
		$html = '<li' . ( $class != "" ? ' class="' . $class .'"' : '' ) .'>';
		
		$html .= '<a href="' . $e->url .'">' . $e->label . '</a>';
		
		if( !empty( $e->child ) )
		{
			$html .= '<ul class="sub-menu">';
			
			foreach( $e->child as $child )
			{
				$html .= $this->get_html_node( $child );
			}
			
			$html .= '</ul>';
		}
		
		$html .= '</li>';
		return $html;
	}
	
	/*
	* compare menu element url to browser url
	*/
	private function is_selected( $url )
	{
		$current_url = get_option( 'site_url', "", "*" ) . rtrim( $_SERVER['REQUEST_URI'], "/" );
		
		// remove query vars from current url
		if( strpos( $current_url, "?" ) !== false )
			$current_url = substr( $current_url, 0, strpos( $current_url, "?" ) );
		
		return ( rtrim( $url, "/" ) == $current_url );
	}
	
	/*
	* tells if the current element is an ancestor
	*/
	private function is_ancestor( $child )
	{
		if( empty( $child ) ) return false;
		
		foreach( $child as $element )
		{
			if( $element->selected ) return true;
			if( !empty( $element->child ) ) return $this->is_ancestor( $element->child );
		}
		
		return false;
	}
	
	
}