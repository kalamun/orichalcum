<?php

class kaDocgallery {
	protected $kaDocuments;
	
	function kaDocgallery() {
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/connect.inc.php');
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/documents.lib.php');
		$this->kaDocuments=new kaDocuments();
		}
	
	function getList($tabella=false,$id=false,$orderby='ordine',$conditions='') {
		$output=array();

		$query="SELECT * FROM ".TABLE_DOCGALLERY." WHERE iddocg>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$this->kaDocuments->getDocument($row['iddoc']);
			$output[$i]['iddocg']=$row['iddocg'];
			$output[$i]['tabella']=$row['tabella'];
			$output[$i]['id']=$row['id'];
			}

		return $output;
		}
	
	function getDocument($iddocg) {
		$output=array();

		$query="SELECT * FROM ".TABLE_DOCGALLERY." WHERE iddocg=".$iddocg." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$this->kaDocuments->getDocument($row['iddoc']);
		$output['iddocg']=$row['iddocg'];
		$output['tabella']=$row['tabella'];
		$output['id']=$row['id'];
		
		return $output;
		}

	function add($tabella,$id,$iddoc,$start=1,$max=999) {
		$query="SELECT ordine FROM ".TABLE_DOCGALLERY." WHERE tabella='".$tabella."' AND id='".$id."' AND ordine>=".$start." AND ordine<".($start+$max)." ORDER BY ordine DESC LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['ordine']=="") $ordine=$start;
		else $ordine=$row['ordine']+1;
		if($start>0&&$max>0&&$ordine>$start+$max) return false;
		if($ordine<$start) $ordine=$start;

		$query="INSERT INTO ".TABLE_DOCGALLERY." (tabella,id,ordine,iddoc) VALUES('".$tabella."','".$id."','".$ordine."','".$iddoc."')";
		if(mysql_query($query)) return true;
		else return false;
		}
	
	function del($iddocg,$start=1,$max=999) {
		$query="SELECT ordine,tabella,id FROM ".TABLE_DOCGALLERY." WHERE iddocg='".$iddocg."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$ordine=$row['ordine'];
		if($start>0&&$max>0&&$ordine>$start+$max) return false;
		if($ordine<$start) $ordine=$start;
		$tabella=$row['tabella'];
		$id=$row['id'];

		$query="DELETE FROM ".TABLE_DOCGALLERY." WHERE iddocg=".$iddocg." LIMIT 1";
		if(!mysql_query($query)) return false;
		
		$query="UPDATE ".TABLE_DOCGALLERY." SET ordine=ordine-1 WHERE tabella='".$tabella."' AND id='".$id."' AND ordine>".$ordine." AND ordine>=".$start." AND ordine<".($start+$max);
		mysql_query($query);
		
		return true;
		}

	function sort($tabella,$id,$order,$start=1,$max=999) {
		if(!is_array($order)) {
			$order=array();
			$query="SELECT iddocg FROM ".TABLE_DOCGALLERY." WHERE tabella='".$tabella."' AND id='".$id."' AND ordine>=".$start." AND ordine<".($start+$max)." ORDER BY ordine";
			$results=mysql_query($query);
			while($results=mysql_fetch_array($results)) {
				$order[]=$row['iddocg'];
				}
			}
		for($i=0;isset($order[$i]);$i++) {
			if($i>$max) break;
			$query="UPDATE ".TABLE_DOCGALLERY." SET ordine=".($i+$start)." WHERE iddocg=".$order[$i]." LIMIT 1";
			if(!mysql_query($query)) return false;
			}
		return true;
		}

	}

?>