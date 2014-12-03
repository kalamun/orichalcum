<?php 
/* (c) Kalamun.org - GPL v3 */

class kaComments {
	
	public function __construct() {
		}
	
	public function add($author,$email,$text,$table,$id)
	{
		$query="INSERT INTO `".TABLE_COMMENTI."` (`ip`,`data`,`tabella`,`id`,`autore`,`email`,`testo`,`public`) VALUES('',NOW(),'".ksql_real_escape_string($table)."','".ksql_real_escape_string($id)."','".ksql_real_escape_string($author)."','".ksql_real_escape_string($email)."','".b3_htmlize($text,true)."','s')";
		if(ksql_query($query)) return ksql_insert_id();
		else return false;
	}

	public function getList($tabella,$id)
	{
		$output=array();
		$query="SELECT * FROM `".TABLE_COMMENTI."` WHERE `tabella`='".ksql_real_escape_string($tabella)."' AND `id`='".ksql_real_escape_string($id)."' ORDER BY `data`";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++)
		{
			$output[$i]=$row;
		}
		return $output;
	}
	
	public function count($tabella,$id,$conditions="",$from=0,$to=0)
	{
		if($to==0) $to=9999;
		$query="SELECT count(*) AS `tot` FROM `".TABLE_COMMENTI."` WHERE `tabella`='".ksql_real_escape_string($tabella)."' AND `id`='".ksql_real_escape_string($id)."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" LIMIT ".ksql_real_escape_string($from).",".ksql_real_escape_string($to);
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
	}

	public function approve($idcomm)
	{
		$query="SELECT `public` FROM `".TABLE_COMMENTI."` WHERE `idcomm`='".intval($idcomm)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		
		$status=($row['public']=='s' ? 'n' : 's');
		$query="UPDATE `".TABLE_COMMENTI."` SET `public`='".$status."' WHERE `idcomm`='".intval($idcomm)."' LIMIT 1";
		return ksql_query($query);
	}

	public function delete($idcomm)
	{
		$query="DELETE FROM `".TABLE_COMMENTI."` WHERE `idcomm`='".intval($idcomm)."' LIMIT 1";
		return ksql_query($query);
	}

}
