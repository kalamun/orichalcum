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

	if( isset( $_POST['twitter_consumer_secret'] ) )
		update_option( "twitter_consumer_secret", $_POST['twitter_consumer_secret'], "*" );

	if( isset( $_POST['twitter_consumer_key'] ) )
		update_option( "twitter_consumer_key", $_POST['twitter_consumer_key'], "*" );

	if( isset( $_POST['twitter_access_token'] ) )
		update_option( "twitter_access_token", $_POST['twitter_access_token'], "*" );

	if( isset( $_POST['twitter_access_token_secret'] ) )
		update_option( "twitter_access_token_secret", $_POST['twitter_access_token_secret'], "*" );

	if( isset( $_POST['twitter_return_url'] ) )
		update_option( "twitter_return_url", $_POST['twitter_return_url'], "*" );

	ok_redirect( "" );
}



add_action( "save_user", "save_user" );

function save_user()
{
	if( !isset( $_POST['add-new-user'] ) ) return false;
	
	if( !ok_verify_nonce( $_POST['nonce'], 'add-new-user' ) ) return false;
	
	$_POST['date_created'] = date( "Y-m-d H:i:s" );
	$_POST['date_last_login'] = null;
	$_POST['type'] = "ADMIN";

	ok_insert_user( $_POST );

	ok_redirect( "" );
}