<?php

/*
 * (c)2017 kalamun.org - GPLv3
 */

/*
 * PDO connection
 */


class ok_pdo
{
	protected $db, $st;
	public $params, $prefix;
	
	public function __construct( $connection_params )
	{
		$this->connect( $connection_params );
	}
	
	/*
	* connect to db via PDO
	*/
	public function connect( $params )
	{
		try
		{
			$this->db = new PDO( 'mysql:host='. $params['host'] .';'. ( !empty( $params['port'] ) ? 'port='. $params['port'] .';' : '' ) .'dbname='. $params['name'] .';charset=utf8mb4;', $params['username'], $params['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4") );
			$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$this->db->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );

		} catch(PDOException $e) {
			// die if error occurs
			var_dump($e);
			trigger_error($e->errorInfo[2], E_USER_ERROR);
			return false;
		}
		
		$this->params = (object)$params;
		$this->prefix = isset( $params['table_prefix'] ) ? $params['table_prefix'] : '';
	}
	
	/*
	* escape table and columns name
	*/
	public function esc_name( $name )
	{
		return preg_replace( "/[^\w\d_-]/", "", $name );
	}
	
	/*
	* quote values
	*/
	public function quote( $value )
	{
		return $this->db->quote( $value );
	}
	
	/*
	* select
	* input params: columns, where, groupby, orderby, limit, offset
	*/
	public function select( $table, $args=[] )
	{
		if( empty( $args['columns'] ) ) $args[ 'columns' ] = ["*"];
		if( empty( $args['where'] ) ) $args[ 'where' ] = [];
		if( empty( $args['groupby'] ) ) $args[ 'groupby' ] = [];
		if( empty( $args['orderby'] ) ) $args[ 'orderby' ] = [];
		
		// escape selected columns
		$esc_columns_quoted = [];

		// if no specified columns, select all
		if( empty( $args['columns'] ) ) $args['columns'] = "*";

		if( !is_array( $args['columns'] ) ) $args['columns'] = [ $args['columns'] ];
		foreach($args['columns'] as $col)
		{
			if($col == "*")
			{
				$esc_columns_quoted = [ "*" ];
				break;
			}
			
			$esc_columns_quoted[] = "`". $this->esc_name( $col ) ."`";
		}
		
		// escape where
		$where = $this->create_where_clause( $args['where'] ); // returns an array with "query" and "values"
		
		// escape groupby
		$esc_groupby_quoted = [];
		if( !is_array( $args['groupby'] ) ) $args['groupby'] = [ $args['groupby'] ];
		foreach($args['groupby'] as $col)
		{
			$esc_groupby_quoted[] = "`". $this->esc_name( $col ) ."`";
		}
		
		// escape orderby
		$esc_orderby_quoted = [];
		if( !is_array( $args['orderby'] ) ) $args['orderby'] = [ $args['orderby'] ];
		foreach($args['orderby'] as $col)
		{
			$order = ( strpos($col, " DESC") ? "DESC" : "ASC" );
			$col = str_replace( " ". $order, "", $col );
			$col = trim( $col, "` " );
			$esc_orderby_quoted[] = "`". $this->esc_name( $col ) ."` ". $order;
		}
		
		// create query
		$query = "SELECT ". implode( ",", $esc_columns_quoted ) ." FROM `". $this->esc_name( $table ) ."` ";
		
		if( !empty( $where['query'] ) )
			$query .= " WHERE ". $where['query'] ." ";
		
		if( !empty( $esc_groupby_quoted ) )
			$query .= " GROUP BY ". implode( ",", $esc_groupby_quoted ) ." ";
		
		if( !empty( $esc_orderby_quoted ) )
			$query .= " ORDER BY ". implode( ",", $esc_orderby_quoted ) ." ";
		
		if( isset( $args['limit'] ) ) $query .= " LIMIT ". intval( $args['limit'] ) ." ";
		if( isset( $args['offset'] ) ) $query .= " OFFSET ". intval( $args['offset'] ) ." ";

		// prepare and execute
		try {
			$this->close_cursor();
			$this->st = $this->db->prepare( $query );

			foreach( $where['values'] as $param => $value )
			{
				$this->st->bindValue($param, $value);
			}
			
			$this->st->execute();
			
			return $this->st;

		} catch(PDOException $e) {
			// die if error occurs
			trigger_error($e->errorInfo[2], E_USER_ERROR);
			return false;

		}
	}
	
	
	/*
	* convert where syntax (inspired by WP's media_query) into where SQL clause with placeholders and values list
	*/
	public function create_where_clause( $where, $values=[] )
	{
		$output = [ "query" => "", "values" => $values ];
		
		if( !isset( $where['relation'] ) ) $where['relation'] = "AND";
		
		for( $i=0; !empty($where[$i]); $i++ )
		{
			if( isset( $where[$i]['key'] ) )
			{
				$output["query"] .= " ". $where['relation'] ." `". $this->esc_name( $where[$i]['key'] ) ."` ". $where[$i]['compare'] ." ";
				
				//if( $where[$i]['value'] === null ) $where[$i]['value'] = "null";
				
				if( array_key_exists( 'value', $where[$i] ) )
				{
					$key = $this->esc_name( $where[$i]['key'] );
					for( $j=1; isset($output["values"][ ":". $key ]); $j++ )
					{
						$key = $this->esc_name( $where[$i]['key'] ) . $j;
					}
					$output["query"] .= ":". $key;
					$output["values"][ ":". $key ] = $where[$i]['value'];
				}
				
			} else {
				$subquery = $this->create_where_clause( $where[$i], $output["values"] );
				$output["query"] .= " ". $where['relation'] ." (". $subquery['query'] .")";
				$output["values"] = $subquery['values'];
			}
		}
		
		$output["query"] = substr( $output["query"], strlen( " ". $where['relation'] ) );

		return $output;
	}
	
	/*
	* fetch statement
	*/
	public function fetch( $statement = "" )
	{
		if( empty($statement) ) $statement = $this->st;
		return $statement->fetch( PDO::FETCH_OBJ );
	}

	/*
	* return all the fetched results statement
	*/
	public function fetchAll( $statement = "" )
	{
		if( empty($statement) ) $statement = $this->st;
		return $statement->fetchAll( PDO::FETCH_OBJ );
	}

	/*
	* count affected rows
	*/
	public function last_insert_id()
	{
		return $this->db->lastInsertId();
	}

	/*
	* count affected rows for the current statement
	*/
	public function row_count()
	{
		return $this->st->rowCount();
	}
	
	/*
	* close cursor (unuseful with MySql)
	*/
	public function close_cursor()
	{
		if( !empty($this->st) ) $this->st->closeCursor();
	}

	/*
	* insert a row
	* options accepts: on duplicate key update = [ values ]
	*/
	public function insert( $table, $fields, $options=[] )
	{
		// escape fields
		$esc_fields = [];
		$esc_fields_quoted = [];
		$esc_placeholders = [];
		$esc_values = [];
		
		foreach($fields as $k=>$v)
		{
			$esc_name = $this->esc_name( $k );
			$esc_fields[] = $esc_name;
			$esc_fields_quoted[] = "`". $esc_name ."`";
			$esc_placeholders[] = ":". $esc_name;
			$esc_values[ ":". $esc_name ] = $v;
		}
		
		// create query
		$query = "INSERT INTO `". $this->esc_name( $table ) ."` (". implode( ",", $esc_fields_quoted ) .") VALUES ( ". implode( ",", $esc_placeholders ) ." )";
		
		// on duplicate key update
		if( !empty( $options['on duplicate key update'] ) && is_array( $options['on duplicate key update'] ) )
		{
			$query .= " ON DUPLICATE KEY UPDATE ";

			foreach( $options['on duplicate key update'] as $k => $v )
			{
				$esc_name = $this->esc_name( $k );
				
				$esc_placeholder = ":". $esc_name;
				for( $i=1; array_search( $esc_placeholder, $esc_placeholders ) !== false; $i++ )
				{
					$esc_placeholder = ":". $esc_name . $i;				
				}
				$esc_placeholders[] = $esc_placeholder;
				$esc_values[ $esc_placeholder ] = $v;
				
				$query .= "`". $esc_name ."` = ". $esc_placeholder .",";
			}
			
			$query = rtrim( $query, ", " );
		}

		// prepare and execute
		try {
			$this->close_cursor();
			$this->st = $this->db->prepare( $query );

			foreach( $esc_values as $k=>$v )
			{
				$this->st->bindValue( $k, $v );
			}
			
			$this->st->execute();

			return $this->st;

		} catch(PDOException $e) {
			// die if error occurs
			trigger_error($e->errorInfo[2], E_USER_ERROR);
			return false;

		}
	}
	
	/*
	* replace a row
	*/
	public function replace( $table, $fields, $args = [] )
	{
		if( empty( $args['where'] ) ) $args[ 'where' ] = [];

		// escape where
		$where = $this->create_where_clause( $args['where'] ); // returns an array with "query" and "values"

		// escape fields
		$esc_fields = [];
		$esc_fields_quoted = [];
		$esc_placeholders = [];
		$esc_values = [];
		
		foreach($fields as $k=>$v)
		{
			$esc_name = $this->esc_name( $k );
			$esc_fields[] = $esc_name;
			$esc_fields_quoted[] = "`". $esc_name ."`";
			$esc_placeholders[] = ":". $esc_name;
			$esc_values[] = $v;
		}
		
		// create query
		$query = "REPLACE INTO `". $this->esc_name( $table ) ."` (". implode( ",", $esc_fields_quoted ) .") VALUES ( ". implode( ",", $esc_placeholders ) ." )";

		if( !empty( $where['query'] ) )
			$query .= " WHERE ". $where['query'] ." ";
		
		if( isset( $args['limit'] ) ) $query .= " LIMIT ". intval( $args['limit'] ) ." ";

		
		// prepare and execute
		try
		{
			$this->close_cursor();
			$this->st = $this->db->prepare( $query );
			$this->st->execute( $esc_values );
			return $this->st;

		} catch(PDOException $e) {
			// die if error occurs
			trigger_error($e->errorInfo[2], E_USER_ERROR);
			return false;

		}
	}
	
	/*
	* update
	*/
	public function update( $table, $fields, $args = [] )
	{
		if( empty( $args['where'] ) ) $args[ 'where' ] = [];

		// escape where
		$where = $this->create_where_clause( $args['where'] ); // returns an array with "query" and "values"

		// escape fields
		$esc_fields = [];
		$esc_fields_quoted = [];
		$esc_placeholders = [];
		$esc_values = [];
		
		foreach($fields as $k=>$v)
		{
			$esc_name = $this->esc_name( $k );
			$esc_fields[] = $esc_name;
			$esc_placeholder = $esc_name;
			for( $i=""; array_key_exists( ":". $esc_placeholder . $i, $where[ 'values' ] ); $i++ ) {}
			$esc_placeholders[] = ":". $esc_placeholder . $i;
			$esc_values[ ":". $esc_placeholder . $i ] = $v;
		}

		
		// create query
		$query = "UPDATE `". $this->esc_name( $table ) ."` SET ";
		foreach( $esc_fields as $k => $v )
		{
			$query .= "`". $v ."`=". $esc_placeholders[ $k ] ." ";
		}
		
		if( !empty( $where['query'] ) )
			$query .= " WHERE ". $where['query'] ." ";
		
		if( isset( $args['limit'] ) ) $query .= " LIMIT ". intval( $args['limit'] ) ." ";

		// prepare and execute
		try
		{
			$this->close_cursor();
			$this->st = $this->db->prepare( $query );

			foreach( $where['values'] as $param => $value )
			{
				$this->st->bindValue($param, $value);
			}
			
			foreach( $esc_values as $param => $value )
			{
				$this->st->bindValue($param, $value);
			}
			
			$this->st->execute();
		
			return $this->st;

		} catch(PDOException $e) {
			// die if error occurs
			trigger_error($e->errorInfo[2], E_USER_ERROR);
			return false;

		}
	}
	
	/*
	* delete
	*/
	public function delete( $table, $args )
	{
		if( empty( $args['where'] ) ) $args[ 'where' ] = [];
		
		// escape where
		$where = $this->create_where_clause( $args['where'] ); // returns an array with "query" and "values"
		
		// create query
		$query = "DELETE FROM `". $this->esc_name( $table ) ."` ";
		
		if( !empty( $where['query'] ) )
			$query .= " WHERE ". $where['query'] ." ";
		
		if( isset( $args['limit'] ) ) $query .= " LIMIT ". intval( $args['limit'] ) ." ";

		// prepare and execute
		try
		{
			$this->close_cursor();
			$this->st = $this->db->prepare( $query );

			foreach( $where['values'] as $param => $value )
			{
				$this->st->bindValue($param, $value);
			}
			
			$this->st->execute();
		
			return $this->st;

		} catch(PDOException $e) {
			// die if error occurs
			trigger_error($e->errorInfo[2], E_USER_ERROR);
			return false;

		}
	}
	
	/*
	* execute query with optional replacement fields [ :placeholder => value ]
	*/
	function query( $query, $fields=[] )
	{
		try
		{
			$this->close_cursor();
			$this->st = $this->db->prepare( $query );
			
			foreach( $fields as $k => $v )
			{
				$this->st->bindValue( $k, $v );
			}
			
			$this->st->execute();

			return $this->st;
		
		} catch(PDOException $e) {
			// die if error occurs
			trigger_error($e->errorInfo[2], E_USER_ERROR);
			return false;

		}
	}
}