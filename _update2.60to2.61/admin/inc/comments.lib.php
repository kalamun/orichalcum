<?
/* (c) Kalamun.org - GPL v3 */

class kaComments {
	
	public function __construct() {
		}
	
	public function add($author,$email,$text,$table,$id)
	{
		$query="INSERT INTO `".TABLE_COMMENTI."` (`ip`,`data`,`tabella`,`id`,`autore`,`email`,`testo`,`public`) VALUES('',NOW(),'".mysql_real_escape_string($table)."','".mysql_real_escape_string($id)."','".mysql_real_escape_string($author)."','".mysql_real_escape_string($email)."','".b3_htmlize($text,true)."','s')";
		if(mysql_query($query)) return mysql_insert_id();
		else return false;
	}

	public function getList($tabella,$id)
	{
		$output=array();
		$query="SELECT * FROM `".TABLE_COMMENTI."` WHERE `tabella`='".mysql_real_escape_string($tabella)."' AND `id`='".mysql_real_escape_string($id)."' ORDER BY `data`";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++)
		{
			$output[$i]=$row;
		}
		return $output;
	}
	
	public function count($tabella,$id,$conditions="",$from=0,$to=0)
	{
		if($to==0) $to=9999;
		$query="SELECT count(*) AS `tot` FROM `".TABLE_COMMENTI."` WHERE `tabella`='".mysql_real_escape_string($tabella)."' AND `id`='".mysql_real_escape_string($id)."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" LIMIT ".mysql_real_escape_string($from).",".mysql_real_escape_string($to);
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
	}

	public function approve($idcomm)
	{
		$query="SELECT `public` FROM `".TABLE_COMMENTI."` WHERE `idcomm`='".intval($idcomm)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		
		$status=($row['public']=='s' ? 'n' : 's');
		$query="UPDATE `".TABLE_COMMENTI."` SET `public`='".$status."' WHERE `idcomm`='".intval($idcomm)."' LIMIT 1";
		return mysql_query($query);
	}

	public function delete($idcomm)
	{
		$query="DELETE FROM `".TABLE_COMMENTI."` WHERE `idcomm`='".intval($idcomm)."' LIMIT 1";
		return mysql_query($query);
	}

}
?>