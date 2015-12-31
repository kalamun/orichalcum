<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once('config.inc.php');


/* MYSQL to PDO */
function ksql_connect($host,$dbname,$user,$password)
{
	try
	{
		$GLOBALS['__db']['pdo']=new PDO('mysql:host='.$host.';dbname='.$dbname , $user, $password);
		$GLOBALS['__db']['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$GLOBALS['__db']['pdo']->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	} catch(PDOException $e) {
		// die if error occurs
		if(isset($_SESSION['iduser'])) trigger_error($e->errorInfo[2], E_USER_ERROR);
		else trigger_error('We are sorry, a database error occurred.', E_USER_ERROR);
		return false;
	}
	return $GLOBALS['__db']['pdo'];
}

function ksql_real_escape_string($string)
{
	$string=$GLOBALS['__db']['pdo']->quote($string);
	$string=substr($string,1,-1);
	return $string;
}

function ksql_query($query)
{

	try
	{
		$microseconds=microtime();
		
		$results=$GLOBALS['__db']['pdo']->prepare($query);
		$results->closeCursor();
		$results->execute();
		
		// log query and execution duration into /mysql_log.txt
		if(defined("DB_LOG") && DB_LOG==true)
		{
			$microseconds-=microtime();
			$log="\n".date("Y-m-d H:i:s")." ------------------------------\n".number_format($microseconds, 6)."   ".$query."\n";
			file_put_contents($_SERVER['DOCUMENT_ROOT'].BASEDIR.'mysql_log.txt', $log, FILE_APPEND);
		}
	
	} catch(PDOException $e) {
		// die if error occurs
		if(isset($_SESSION['iduser'])) trigger_error($e->errorInfo[2], E_USER_ERROR);
		else trigger_error('We are sorry, a database error occurred.', E_USER_ERROR);
		return false;
	}
	

	return $results;
}

function ksql_fetch_array($results)
{
	return $results->fetch(PDO::FETCH_ASSOC);
}

function ksql_insert_id()
{
	return $GLOBALS['__db']['pdo']->lastInsertId();
}

function ksql_num_rows($results)
{
	return $results->rowCount();
}


function ksql_close()
{
	if(isset($GLOBALS['__db']['pdo'])) unset($GLOBALS['__db']['pdo']);
}


/* CONNECT TO DB */
$GLOBALS['__db']['id']=ksql_connect($GLOBALS['__db']['host'],$GLOBALS['__db']['name'],$GLOBALS['__db']['user'],$GLOBALS['__db']['password']);
ksql_query("SET NAMES 'UTF8'"); 

