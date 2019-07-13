<?php

/*
* INIT ORICHALCUM
*/

/*
* bootstrap: init minimum functions
*/
function ok_bootstrap()
{
	define( "ORICHALCUM", true );

	// define base path
	define( "ROOT_DIR", get_site_directory() );
	define( "ROOT_URI", get_site_directory_uri() );
	define( "ADMIN_SUBDIR", "admin" );

	// load config
	require_once( ROOT_DIR . '/ok-config.php' );

	// load error api
	require_once( ROOT_DIR . '/inc/errors.php' );
	
	// connect to db
	require_once( ROOT_DIR . '/inc/db.php' );
	$GLOBALS['ok_db'] = new ok_pdo( $GLOBALS['ok_db_params'] );

	// set timezone
	set_timezone();

	// boot the cache api
	require_once( ROOT_DIR . '/inc/cache.php' );
	$GLOBALS['ok_cache'] = new ok_cache();

	// users
	require_once( ROOT_DIR . '/inc/users.php' );

	// start session only if a session cookie is provided
	if( !empty($_COOKIE["PHPSESSID"]) ) session_start();

	// fix https support in some server
	if( empty( $_SERVER['HTTPS'] ) && stripos( get_site_url(), 'https://' ) === 0 ) $_SERVER['HTTPS'] = 'on';
	
	// fix remote address in case of forwarding
	$headers = apache_request_headers();
	if( !empty( $header['X-Forwarded-For'] ) )
		$_SERVER['REMOTE_ADDR'] = $header['X-Forwarded-For'];
	elseif( !empty( $header['X-FORWARDED-FOR'] ) )
		$_SERVER['REMOTE_ADDR'] = $header['X-FORWARDED-FOR'];
}


/*
* init
*/
function ok_init()
{	
	if( !defined( "ORICHALCUM" ) ) ok_bootstrap();

	// init language
	require_once( ROOT_DIR . '/inc/languages.php' );
	init_language();

	// load config from db
	preload_config();
	
	// templates functions
	require_once( ROOT_DIR . '/inc/templates.php' );
	
	// include functions.php if exists
	$functions = get_template_directory() . '/functions.php';
	if( file_exists( $functions ) ) include_once( $functions );

	// admin functions
	if( is_backend() || ( defined( "IS_AJAX" ) && IS_AJAX == true && is_admin() ) )
	{
		require_once( ROOT_DIR . '/' . ADMIN_SUBDIR . '/inc/admin.php' );
		ok_init_admin();
	}
	
	// meta-data functions
	require_once( ROOT_DIR . '/inc/meta.php' );
	
	// posts functions
	require_once( ROOT_DIR . '/inc/posts.php' );

	// hook init
	do_action( "init" );
}


/*
* set apache request headers on servers that doesn't support it
*/
if( !function_exists( 'apache_request_headers' ) )
{
	function apache_request_headers()
	{
		$headers = [];
		foreach( $_SERVER as $key => $value )
		{
			if( substr($key, 0, 5) == 'HTTP_' )
				$headers[ str_replace( ' ', '-', ucwords( str_replace( '_', ' ', strtolower( substr( $key, 5 ) ) ) ) ) ] = $value;
		}
		return $headers;
	}
}


/*
* set timezone
*/
function set_timezone( $timezone = false )
{
	if( empty( $timezone ) )
	{
		$timezone = get_option( "timezone", "Europe/Paris", "*" );
	}
	
	date_default_timezone_set($timezone);
	
	global $ok_db;
	$ok_db->query( "SET time_zone = :timezone", [ ":timezone" => date("P") ] );
}


/*
* get the current url as requested on browser
*/
function get_request_url( $options = [] )
{
	$url = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	// remove query vars if requested
	if( !isset( $options['query_vars'] ) ) $options['query_vars'] = true;
	if( empty( $options['query_vars'] ) )
		$url = preg_replace( "/\?.*/", "", $url );

	return $url;
}


/*
* get the parsed data of the current url as requested on browser
*/
function get_request_url_parsed()
{
	return parse_url( get_request_url() );
}


/*
* get an array of uri parts
*/
function get_request_uri_parts()
{
	$url = parse_url( get_request_url() )[ 'path' ];
	$url = trim( $url, "/" );
	$url = substr( $url, strlen( get_site_directory_uri() ) );
	return explode( "/", $url );
}


/*
* Get the site root path based on the position of ok-config.php
* the site root never ends with slash
*/

function get_site_directory()
{
	if( defined( "ROOT_DIR" ) ) return ROOT_DIR;
	
	$basedir = getcwd();
	
	while( $basedir = realpath( $basedir ) )
	{
		$basedir = str_replace( "\\", "/", $basedir );
		$basedir = rtrim( $basedir, "/" );
		if( file_exists( $basedir . '/ok-config.php' ) )
			return $basedir;
		
		$basedir .= '/..';
	}
	
	return $basedir;
}


/*
* Get the site basedir based on the position of ok-config.php
* the site root never ends with slash
*/

function get_site_directory_uri()
{
	if( defined( "ROOT_URI" ) ) return ROOT_URI;
	if( defined( "ROOT_DIR" ) ) $root_dir = ROOT_DIR;
	else $root_dir = get_site_directory();
	
	$base_dir = str_replace( $_SERVER['DOCUMENT_ROOT'], "", $root_dir );
	return $base_dir;
}


/*
* returns ajax handler url
*/
function get_ajax_url()
{
	return get_site_directory_uri() . '/inc/ajax-handler.php';
}


/*
* get admin full path
*/
function get_admin_directory()
{
	return get_site_directory() . '/' . ADMIN_SUBDIR;
}


/*
* get admin basedir
*/
function get_admin_directory_uri()
{
	return get_site_directory_uri() . '/' . ADMIN_SUBDIR;
}


/*
* get theme full path
*/
function get_template_directory()
{
	return get_site_directory() . '/themes/' . get_option( "theme", "", "*" );
}


/*
* get theme uri
*/
function get_template_directory_uri()
{
	return get_site_directory_uri() . '/themes/' . get_option( "theme", "", "*" );
}


/*
* return the site name
*/
function get_site_name()
{
	return get_option( "site_name", "" );
}


/*
* return the site url
*/
function get_site_url()
{
	$site_url = get_option( "site_url", "", "*" );
	$site_url = trim( $site_url, " /" );
	return $site_url . get_site_directory_uri();
}

function get_admin_url()
{
	return get_site_url() . '/' . ADMIN_SUBDIR;
}

/*
* load configuration variables from db
*/
function preload_config( $lang=null )
{
	if( $lang === null ) $lang = get_language_code();
	
	global $ok_db;

	$args = [
		"where" => [
			"relation" => "OR",
			[
				"key" => "lang",
				"compare" => "=",
				"value" => "*",
			],
		],
	];
	
	if( $lang != "*" )
	{
		$args["where"][] = [
			"key" => "lang",
			"compare" => "=",
			"value" => $lang,
			];
	}
	
	$st = $ok_db->select( $ok_db->prefix . 'config', $args );
	
	$config = [];
	while( $row = $ok_db->fetch( $st ) )
	{
		$GLOBALS['ok_cache']->set( 'config-' . $row->param, $row->value, $row->lang );
	}
}


/*
* get option value from config db
*/

function get_option( $param, $default = "", $lang = null, $returns_value = true )
{
	// search into cache
	if( isset( $GLOBALS['ok_cache'] ) && $returns_value == true )
	{
		$value = $GLOBALS['ok_cache']->get( $param, $lang );
		if( $value !== null ) return $value;
	}

	// if not cached items, search into db
	if( $lang === null && $param != 'default_language' ) $lang = get_language_code();
	if( empty( $lang ) ) $lang = '*';
	
	global $ok_db;
	
	$args = [
		"where" => [
			[
				"key" => "param",
				"compare" => "=",
				"value" => $param,
			],
			[
				"key" => "lang",
				"compare" => "=",
				"value" => $lang,
			],
		],
		"limit" => 1,
	];
	
	$st = $ok_db->select( $ok_db->prefix . 'config', $args );
	$option = $ok_db->fetch( $st );
	
	if( !empty( $option->value ) )
	{
		// cache and return
		if( isset( $GLOBALS['ok_cache'] ) ) $GLOBALS['ok_cache']->set( $param, $option->value, $lang );

		if( $returns_value == true )
			return $option->value;
		else
			return $option;
	}
	
	// nothing found into db? return default value
	if( $returns_value == true )
		return $default;
	
	return false;
}

/*
* update an option or create a new one if it doesn't exists
*/
function update_option( $param, $value, $lang = null )
{
	if( $lang === null && $param != 'default_language' ) $lang = get_language_code();
	if( empty( $lang ) ) $lang = '*';
	
	global $ok_db;

	$option = get_option( $param, "", $lang, false );

	$fields = [
		"param" => $param,
		"value" => $value,
		"lang" => $lang,
	];
	
	if( !empty( $option->config_id ) )
		$fields['config_id'] = $option->config_id;
	
	$ok_db->insert( $ok_db->prefix . 'config', $fields, [ 'on duplicate key update' => $fields ] );
	$config_id = $ok_db->last_insert_id();

	// refresh cache
	if( isset( $GLOBALS['ok_cache'] ) ) $GLOBALS['ok_cache']->set( $param, $value, $lang );

	return $config_id;
}


/*
* create nonce value based on time, user token, remote ip and action
* valid for beetween one and two hours
*/
function ok_create_nonce( $action )
{
	$nonce = "";
	
	if( is_user_logged_in() )
		$token = ok_get_current_user()->token;
	else
		$token = NONCE_TOKEN_SALT;
	
	$nonce = date( "Y-m-d-H" ) . $token . $_SERVER['REMOTE_ADDR'] . $action;
	
	// I choose mp4 because it's faster than others and this is not a critical scenario
	$nonce = hash( "md4", $nonce );
	
	return $nonce;
}

/*
* check if a nonce is valid or not
*/
function ok_verify_nonce( $nonce, $action )
{
	if( is_user_logged_in() )
		$token = ok_get_current_user()->token;
	else
		$token = NONCE_TOKEN_SALT;
	
	$nonce_match = date( "Y-m-d-H" ) . $token . $_SERVER['REMOTE_ADDR'] . $action;
	$nonce_match = hash( "md4", $nonce_match );

	if( $nonce == $nonce_match ) return true;
	
	// check one hour before
	$nonce_match = date( "Y-m-d-H", time()-3600 ) . $token . $_SERVER['REMOTE_ADDR'] . $action;
	$nonce_match = hash( "md4", $nonce_match );

	if( $nonce == $nonce_match ) return true;
	
	return false;
}


/*
* enqueue script
*/
function ok_enqueue_script( $id, $url, $version = "", $attributes = [], $position = "head" )
{
	if( !isset( $GLOBALS['ok_scripts'] ) )
		$GLOBALS['ok_scripts'] = [];
	
	if( !is_array( $attributes ) )
		$attributes = [];
	
	$GLOBALS['ok_scripts'][ $id ] = [ "url" => $url, "version" => $version, "attributes" => $attributes, "position" => $position ];
}

/*
* enqueue styles
*/
function ok_enqueue_style( $id, $url, $version = "", $attributes = [], $position = "head" )
{
	if( !isset( $GLOBALS['ok_scripts'] ) )
		$GLOBALS['ok_styles'] = [];
	
	if( !is_array( $attributes ) )
		$attributes = [];
	
	$GLOBALS['ok_styles'][ $id ] = [ "url" => $url, "version" => $version, "attributes" => $attributes, "position" => $position ];
}


/*************
* HOOKS
*************/

/*
* add an action to the function
*/
function add_action( $tag, $function_to_add, $priority = null )
{
	if( empty( $tag ) )
	{
		trigger_error( 'Unable to add action: tag is empty' );
		return false;
	}
	
	if( empty( $function_to_add ) || !function_exists( $function_to_add ) )
	{
		trigger_error( 'Unable to add action: function does not exist' );
		return false;
	}
	
	if( !isset( $GLOBALS['ok_cache'] ) )
	{
		require_once( ROOT_DIR . '/inc/cache.php' );
		$GLOBALS['ok_cache'] = new ok_cache();
	}

	
	$functions = $GLOBALS['ok_cache']->get( 'hook-' . $tag, "*" );
	
	if( empty( $functions ) ) $functions = [];
		
	$new_functions = [];
	
	// when no priority set, get last and add 10
	if( $priority === null )
	{
		if( empty( $functions ) ) $priority = 0;
		else {
			end( $functions );
			$priority = intval( key( $functions ) ) + 10;
		}
	}
	
	$priority = intval( $priority);
	
	
	// add function to functions list, until the new element priority
	foreach( $functions as $order => $function_name )
	{
		if( $order < $priority ) $new_functions[ $order ] = $function_name;
		else break;
	}
	
	$new_functions[ $priority ] = $function_to_add;
	
	// in case of overriding, slide following functions
	$offset = isset( $functions[ $priority ] ) ? 1 : 0;

	foreach( $functions as $order => $function_name )
	{
		if( $order >= $priority ) 
		{
			// try to preserve orders also in case of override
			$new_order = isset( $new_functions[ $order ] ) ? $order + $offset : $order;
			$new_functions[ $order + $offset ] = $function_name;
		}
	}
	
	// save in cache
	$GLOBALS['ok_cache']->set( 'hook-' . $tag, $new_functions, "*" );
}

/*
* execute action, additional arguments will be passed to the executed function in the same order
*/
function do_action( $tag )
{
	// get functions list
	$functions = $GLOBALS['ok_cache']->get( 'hook-' . $tag, "*" );
	
	if( empty( $functions ) ) $functions = [];
	
	foreach( $functions as $function_name )
	{
		// get additional arguments
		$args = [];
		for( $i=1; $i < func_num_args(); $i++)
		{
			$args[] = func_get_arg($i);
        }

		call_user_func_array( $function_name, $args );
	}
}
