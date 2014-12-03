<?php 
class kaImgallery {
	protected $kaImages;
	
	function kaImgallery() {
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/connect.inc.php');
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/images.lib.php');
		$this->kaImages=new kaImages();
		}
	
	function getList($tabella=false,$id=false,$orderby='ordine',$conditions='') {
		$output=array();

		$query="SELECT * FROM ".TABLE_IMGALLERY." WHERE idimga>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";

		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$this->kaImages->getImage($row['idimg']);
			$output[$i]['idimga']=$row['idimga'];
			$output[$i]['tabella']=$row['tabella'];
			$output[$i]['id']=$row['id'];
			}
		
		return $output;
		}
	
	function getImage($idimga) {
		$output=array();

		$query="SELECT * FROM ".TABLE_IMGALLERY." WHERE idimga=".$idimga." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$output=$this->kaImages->getImage($row['idimg']);
		$output['idimga']=$row['idimga'];
		$output['tabella']=$row['tabella'];
		$output['id']=$row['id'];
		
		return $output;
		}

	function add($tabella,$id,$idimg,$start=1,$max=999) {
		$query="SELECT ordine FROM ".TABLE_IMGALLERY." WHERE tabella='".$tabella."' AND id='".$id."' AND ordine>=".$start." AND ordine<".($start+$max)." ORDER BY ordine DESC LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		if($row['ordine']=="") $ordine=$start;
		else $ordine=$row['ordine']+1;
		if($start>0&&$max>0&&$ordine>$start+$max) return false;
		if($ordine<$start) $ordine=$start;

		$query="INSERT INTO ".TABLE_IMGALLERY." (tabella,id,ordine,idimg) VALUES('".$tabella."','".$id."','".$ordine."','".$idimg."')";
		if(ksql_query($query)) return true;
		else return false;
		}
	
	function del($idimga,$start=1,$max=999) {
		$query="SELECT ordine,tabella,id FROM ".TABLE_IMGALLERY." WHERE idimga='".$idimga."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$ordine=$row['ordine'];
		if($start>0&&$max>0&&$ordine>$start+$max) return false;
		if($ordine<$start) $ordine=$start;
		$tabella=$row['tabella'];
		$id=$row['id'];

		$query="DELETE FROM ".TABLE_IMGALLERY." WHERE idimga=".$idimga." LIMIT 1";
		if(!ksql_query($query)) return false;
		
		$query="UPDATE ".TABLE_IMGALLERY." SET ordine=ordine-1 WHERE tabella='".$tabella."' AND id='".$id."' AND ordine>".$ordine." AND ordine>=".$start." AND ordine<".($start+$max);
		ksql_query($query);
		
		return true;
		}

	function sort($tabella,$id,$order,$start=1,$max=999) {
		if(!is_array($order)) {
			$order=array();
			$query="SELECT idimga FROM ".TABLE_IMGALLERY." WHERE tabella='".$tabella."' AND id='".$id."' AND ordine>=".$start." AND ordine<".($start+$max)." ORDER BY ordine";
			$results=ksql_query($query);
			while($results=ksql_fetch_array($results)) {
				$order[]=$row['idimga'];
				}
			}
		for($i=0;isset($order[$i]);$i++) {
			if($i>$max) break;
			$query="UPDATE ".TABLE_IMGALLERY." SET ordine=".($i+$start)." WHERE idimga=".$order[$i]." LIMIT 1";
			if(!ksql_query($query)) return false;
			}
		return true;
		}

	}

