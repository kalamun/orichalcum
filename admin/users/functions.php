<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

add_action( "save_user", "save_user" );

function save_user()
{
	if( !isset( $_POST['add-new-user'] ) ) return false;
	
	if( !ok_verify_nonce( $_POST['nonce'], 'add-new-user' ) ) return false;
	
	$_POST['date_created'] = date( "Y-m-d H:i:s" );
	$_POST['date_last_login'] = null;
	$_POST['type'] = "GUEST";
	$_POST['status'] = "ACT";

	ok_insert_user( $_POST );

	ok_redirect( "" );
}

add_action( "delete_user", "delete_user" );

function delete_user()
{
	if( !isset( $_POST['delete-user'] ) ) return false;
	
	if( !ok_verify_nonce( $_POST['nonce'], 'delete-user' ) ) return false;
	
	foreach( $_POST['delete-user'] as $user_id => $label ) {}
	
	ok_delete_user( $user_id );

	ok_redirect( "" );
}