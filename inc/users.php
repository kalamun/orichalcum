<?php

/*
* USER FUNCTIONS
*/


/*
* returns boolean if user is logged
*/
function is_user_logged_in()
{
	if( !empty( $_SESSION['ok_current_user']->user_id ) ) return true;
	return false;
}


/*
* returns boolean if user is admin
*/
function is_admin()
{
	if( !empty( $_SESSION['ok_current_user']->type ) && $_SESSION['ok_current_user']->type == "ADMIN" ) return true;
	return false;
}

/*
* authenticate and logs in
*/
function ok_signon( $credentials = [] )
{
	if( empty( $credentials ) )
	{
		$credentials[ 'username' ] = $_POST['username'];
		$credentials[ 'password' ] = $_POST['password'];
	}
	
	// get user by username
	$user = get_user_by( "username", $credentials['username'] );
	
	// check if user exists
	if( empty( $user ) || empty( $user->username ) ) return false;
	
	// check the type of the user
	if( !empty( $credentials[ 'type' ] ) && $credentials[ 'type' ] != $user->type ) return false;
	
	// match password
	if( !ok_verify_password( $credentials[ 'password' ], $user->password ) ) return false;

	// create user variables
	if( !session_id() ) session_start();
	$_SESSION['ok_current_user'] = $user;
	
	// update last login date
	global $ok_db;
	
	$ok_db->update( $ok_db->prefix . "users", [ "date_last_login" => date("Y-m-d H:i:s") ], [ "where" => [ [ "key" => "user_id", "compare" => "=", "value" => $user->user_id ] ], "limit" => 1 ] );
	
	return true;
}

function ok_logout()
{
	if( !is_user_logged_in() ) return false;
	unset( $_SESSION['ok_current_user'] );
	return true;
}

/*
* get users list from db
*/
function get_users( $args = [] )
{
	global $ok_db;

	if( empty( $args['orderby'] ) ) $args["orderby"] = "username";
	
	$ok_db->select( $ok_db->prefix . 'users', $args );
	
	return $ok_db->fetchAll();
}


/*
* get a single user
*/
function get_user_by( $param, $value )
{
	global $ok_db;
	
	$args = [
		"where" => [
			"relation" => "AND",
			[
				"key" => $param,
				"compare" => "LIKE",
				"value" => $value,
			],
		],
		"limit" => 1,
	];
	
	$st = $ok_db->select( $ok_db->prefix . 'users', $args );
	
	$config = [];
	$row = $ok_db->fetch( $st );

	if( empty( $row ) ) return false;

	return new ok_user( $row );
}


/*
* get current user
*/
function ok_get_current_user()
{
	if( isset( $_SESSION['ok_current_user'] ) )
		return $_SESSION['ok_current_user'];
	return false;
}


/*
* get current user id
*/
function get_current_user_id()
{
	if( isset( $_SESSION['ok_current_user']->user_id ) )
		return $_SESSION['ok_current_user']->user_id;
	return false;
}


/*
* insert / update user
*/
function ok_insert_user( $userdata )
{
	global $ok_db;
	
	if( empty( $userdata['username'] ) && empty( $userdata['user_id'] ) )
	{
		trigger_error( 'Username must be provided' );
		return false;
	}
	
	if( empty( $userdata['password'] ) && empty( $userdata['user_id'] ) )
	{
		trigger_error( 'Password must be provided' );
		return false;
	}
	
	$fields = [];
	
	if( isset( $userdata['username'] ) ) $fields['username'] = $userdata['username'];
	if( isset( $userdata['password'] ) ) $fields['password'] = ok_hash_password( $userdata['password'] );
	if( isset( $userdata['first_name'] ) ) $fields['first_name'] = $userdata['first_name'];
	if( isset( $userdata['last_name'] ) ) $fields['last_name'] = $userdata['last_name'];
	if( isset( $userdata['email'] ) ) $fields['email'] = $userdata['email'];
	if( isset( $userdata['date_modified'] ) ) $fields['date_modified'] = $userdata['date_modified'];
		else $fields['date_modified'] = date("Y-m-d H:i:s");
	if( isset( $userdata['date_last_login'] ) ) $fields['date_last_login'] = $userdata['date_last_login'];
	if( isset( $userdata['type'] ) ) $fields['type'] = $userdata['type'];
	if( isset( $userdata['lang'] ) ) $fields['lang'] = $userdata['lang'];

	if( isset( $userdata['user_id'] ) ) $fields['user_id'] = intval( $userdata['user_id'] );
	
	// fields to be populated only on user creation
	if( empty( $userdata['user_id'] ) )
	{
		if( !empty( $userdata['date_created'] ) ) $fields['date_created'] = $userdata['date_created'];
		
		// status could be: ACT = active; PND = pending or suspended; DEL = deleted
		if( !isset( $userdata['status'] ) ) $fields['status'] = 'PND';
		else $fields['status'] = $userdata['status'];
		
		if( !empty( $userdata['type'] ) && $userdata['type'] == "ADMIN" )
			$fields['type'] = "ADMIN";
		else
			$fields['type'] = "GUEST";
		
		if( isset( $userdata['lang'] ) ) $fields['lang'] = get_option( "default_language", "en_EN", "*" );
		
		$fields['token'] = hash( "md4", $userdata['username'] . date( "YmdHis" ) . rand(100,999) );
	
	// update
	} else {
		
		// load user
		$user = (array) get_user_by( "user_id", $userdata['user_id'] );
		
		// update
		$fields = $fields + $user;
		
	}
	
	print_r( $fields );

	$ok_db->insert( $ok_db->prefix . 'users', $fields, [ 'on duplicate key update' => $fields ] );

	return $ok_db->last_insert_id();
}


/*
* change status of a user from ACT to DEL
*/
function ok_delete_user( $user_id )
{
	global $ok_db;

	$fields = [
		"status" => "DEL",
		];

	$args = [
			"where" =>
			[
				[
					"key" => "user_id",
					"compare" => "=",
					"value" => $user_id,
				],
			],
			"limit" => 1,
		];
		
	$ok_db->update( $ok_db->prefix . 'users', $fields, $args );
}

/*
* hash passwords
*/
function ok_hash_password( $password )
{
	return password_hash( $password, PASSWORD_DEFAULT );
}


/*
* verifies that a password matches a hash
*/
function ok_verify_password( $password, $hash )
{
	return password_verify( $password, $hash );
}


/*
* get user meta data
*/
function get_user_meta( $user_id, $meta_key=null, $single=false )
{
	return get_meta( "users", $user_id, $meta_key, $single );
}


/*
* update user meta data
* returns meta id
*/
function update_user_meta( $user_id, $meta_key, $meta_value )
{
	return update_meta( "users", $user_id, $meta_key, $meta_value );
}


/*
* delete user meta data
* returns boolean
*/
function delete_user_meta( $user_id, $meta_key=null )
{
	return delete_meta( "users", $user_id, $meta_key );
}




/*****************
* CLASSES
*****************/

/*
* user class
*/
class ok_user
{
			
	function __construct( $user_db )
	{
		foreach( $user_db as $key => $value)
		{
			$this->{ $key } = $user_db->{ $key };
		}
	}
	
}