<?php

/*
* META-DATA FUNCTIONS
*/


/*
* get meta-data
* if meta-key is empty, returns an array with all meta datas for the given object
*/
function get_meta( $table_name, $id, $meta_key=null, $single=false )
{
	if( empty( $table_name ) )
	{
		trigger_error( "Get meta: no table name provided" );
		return false;
	}
	
	if( empty( $id ) )
	{
		trigger_error( "Get meta: no id provided for table " . $table_name . " and meta_key " . $meta_key );
		return false;
	}
	
	if( empty( $meta_key ) && $single === true )
	{
		trigger_error( "Get meta: you must specify a meta_key to get a single value returned" );
		return false;
	}
	
	global $ok_db;

	$args = [
		"where" => [
			"relation" => "AND",
			[
				"key" => "table_name",
				"compare" => "=",
				"value" => $table_name,
			],
			[
				"key" => "id",
				"compare" => "=",
				"value" => $id,
			],
		],
	];
	
	if( !empty( $meta_key ) )
	{
		$args["where"][] = [
			"key" => "meta_key",
			"compare" => "=",
			"value" => $meta_key,
			];
			
		$args["limit"] = 1;
	}
	$st = $ok_db->select( $ok_db->prefix . 'meta', $args );
	
	$meta = $ok_db->fetchAll( $st );

	if( empty($meta) ) return false;
	
	if( $single == true )
		return $meta[0]->meta_value;

	if( !empty( $meta_key ) )
		return $meta[0];
		
	return $meta;
}


/*
* update meta-data
* returns meta_id or false
*/

function update_meta( $table_name, $id, $meta_key, $meta_value )
{
	if( empty( $table_name ) )
	{
		trigger_error( "Update meta: no table name provided" );
		return false;
	}
	
	if( empty( $id ) )
	{
		trigger_error( "Update meta: no id provided" );
		return false;
	}
	
	if( empty( $meta_key ) )
	{
		trigger_error( "Update meta: no meta key provided" );
		return false;
	}
	
	global $ok_db;

	$fields = [
		"table_name" => $table_name,
		"id" => $id,
		"meta_key" => $meta_key,
		"meta_value" => $meta_value,
	];
	
	// check if meta already exists
	$meta = get_meta( $table_name, $id, $meta_key );
	
	if( !empty( $meta->meta_id ) )
		$fields[ 'meta_id' ] = $meta->meta_id;
	
	$ok_db->insert( $ok_db->prefix . 'meta', $fields, [ 'on duplicate key update' => $fields ] );
	return $ok_db->last_insert_id();
}

/*
* delete meta
* if no meta_key, delete all the meta of the given object
*/

function delete_meta( $table_name, $id, $meta_key = null )
{
	if( empty( $table_name ) )
	{
		trigger_error( "Get meta: no table name provided" );
		return false;
	}
	
	if( empty( $id ) )
	{
		trigger_error( "Get meta: no id provided" );
		return false;
	}
	
	global $ok_db;

	$args = [
		"where" => [
			"relation" => "AND",
			[
				"key" => "table_name",
				"compare" => "=",
				"value" => $table_name,
			],
			[
				"key" => "id",
				"compare" => "=",
				"value" => $id,
			],
		],
	];
	
	if( !empty( $meta_key ) )
	{
		$args["where"][] = [
			"key" => "meta_key",
			"compare" => "=",
			"value" => $meta_key,
			];
			
		$args["limit"][] = 1;
	}
	
	$st = $ok_db->delete( $ok_db->prefix . 'meta', $args );
	
}