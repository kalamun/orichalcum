<?php

/*
* AJAX HANDLER
*/

define( 'IS_AJAX', true );

// init orichalcum
require_once( 'init.php' );
ok_init();

// include referer in backend, if referer is located in admin area
if( is_backend() || is_admin() )
{
	$referer = $_SERVER['HTTP_REFERER'];
	$referer = parse_url( $referer )['path'];
	
	ok_admin_include_libraries( $referer );
}


do_action( 'ok_ajax_do_ajax' );