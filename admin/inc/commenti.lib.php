<?php 
/* (c) Kalamun.org - GPL v3 */

class kCommenti {
	
	public function kCommenti() {
		}

	public function getList($tabella,$id) {
		$output=array();
		if($to==0) $to=9999;
		if($lang==false) $lang=$this->ll;
		$query="SELECT * FROM ".TABLE_COMMENTI." WHERE tabella='".addslashes($tabella)."' AND id='".intval($id)."' ORDER BY data DESC";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			}
		return $output;
		}
	
	public function count($tabella,$id,$conditions="",$from=0,$to=0) {
		if($to==0) $to=9999;
		$query="SELECT count(*) AS tot FROM ".TABLE_COMMENTI." WHERE tabella='".$tabella."' AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" LIMIT ".$from.",".$to;
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
		}

	}
