<?
/* (c) Kalamun.org - GPL v3 */

class kaComments {
	
	public function kaCommenti() {
		}

	public function getList($tabella,$id) {
		$output=array();
		$query="SELECT * FROM `".TABLE_COMMENTI."` WHERE `tabella`='".mysql_real_escape_string($tabella)."' AND `id`='".mysql_real_escape_string($id)."' ORDER BY `data` DESC";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			}
		return $output;
		}
	
	public function count($tabella,$id,$conditions="",$from=0,$to=0) {
		if($to==0) $to=9999;
		$query="SELECT count(*) AS `tot` FROM `".TABLE_COMMENTI."` WHERE `tabella`='".mysql_real_escape_string($tabella)."' AND `id`='".mysql_real_escape_string($id)."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" LIMIT ".mysql_real_escape_string($from).",".mysql_real_escape_string($to);
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
		}

	}
?>