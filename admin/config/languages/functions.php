<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

add_action( "save_options", "save_options" );

function save_options()
{
	if( !isset( $_POST['save-options'] ) ) return false;
	
	if( !ok_verify_nonce( $_POST['nonce'], 'save-options' ) ) return false;

	if( isset( $_POST['site_name'] ) )
		update_option( "site_name", $_POST['site_name'] );

	if( isset( $_POST['site_url'] ) )
		update_option( "site_url", $_POST['site_url'], "*" );

	if( isset( $_POST['payoff'] ) )
		update_option( "payoff", $_POST['payoff'] );

	insert_success( "Modifiche salvate con successo" );
	
	ok_redirect( "" );
}