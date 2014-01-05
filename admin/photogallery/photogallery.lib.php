<?php
/* (c) Kalamun.org - GNU/GPL 3 */

class kaPhotogallery {
	protected $kaComments,$kaImgallery;
	
	function kaPhotogallery() {
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/comments.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/imgallery.lib.php');
		$this->kaComments=new kaComments();
		$this->kaImgallery=new kaImgallery();
		}
	
	function add($titolo,$testo,$dir,$template="",$traduzioni="",$ll=false) {
		if($ll==false) $ll=$_SESSION['ll'];
		
		if(get_magic_quotes_gpc()) {
			$titolo=mysql_real_escape_string($titolo);
			$testo=mysql_real_escape_string($testo);
			$dir=mysql_real_escape_string($dir);
			}
		
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".$ll."' ORDER BY ordine DESC LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$ordine=$row['ordine']+1;
		
		$query="INSERT INTO ".TABLE_PHOTOGALLERY." (titolo,testo,dir,template,layout,traduzioni,ordine,ll,data) VALUES('".$titolo."','".$testo."','".$dir."','','','','".$ordine."','".$ll."',NOW())";
		if(!mysql_query($query)) return false;
		else return mysql_insert_id();
		}

	function getById($idphg) {
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE idphg='".mysql_real_escape_string($idphg)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		
		$output=$row;
		$output['traduzioni']=array();
		foreach(explode("|",$row['traduzioni']) as $t) {
			$ll=substr($t,0,2);
			$id=intval(substr($t,3));
			if($ll!=""&&$id!=0) $output['traduzioni'][$ll]=$id;
			}

		$output['commentiOnline']=$this->kaComments->count(TABLE_PHOTOGALLERY,$row['idphg'],"public='s'");
		$output['commentiTot']=$this->kaComments->count(TABLE_PHOTOGALLERY,$row['idphg']);
		$output['imgallery']=$this->kaImgallery->getList(TABLE_PHOTOGALLERY,$row['idphg']);

		return $output;
		}

	function getTitleById($idphg) {
		$query="SELECT `idphg`,`titolo`,`dir` FROM `".TABLE_PHOTOGALLERY."` WHERE `idphg`='".mysql_real_escape_string($idphg)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row;
		}

	function getList($conditions="",$ll=false) {
		require_once(ADMINRELDIR.'/inc/config.lib.php');
		$kaImpostazioni=new kaImpostazioni();
		
		if($ll==false) $ll=$_SESSION['ll'];

		$output=array();
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ";
		if($conditions!="") $query.="(".$conditions.") AND ";
		$query.=" ll='".$ll."' ORDER BY ".$kaImpostazioni->getVar('photogallery-order',1);
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$row;
			$output[$id]['commentiOnline']=$this->kaComments->count(TABLE_PHOTOGALLERY,$row['idphg'],"public='s'");
			$output[$id]['commentiTot']=$this->kaComments->count(TABLE_PHOTOGALLERY,$row['idphg']);
			$output[$id]['imgallery']=$this->kaImgallery->getList(TABLE_PHOTOGALLERY,$row['idphg']);
			}
		return $output;
		}

	public function getQuickList($vars) {
		if(!isset($vars['start'])) $vars['start']=0;
		if(!isset($vars['limit'])) $vars['limit']=999;
		$output=array();
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE `idphg`>0 ";
		if(isset($vars['match'])) $query.=" AND (`titolo` LIKE '%".mysql_real_escape_string($vars['match'])."%' OR `dir` LIKE '%".mysql_real_escape_string($vars['match'])."%')";
		if(isset($vars['ll'])) $query.=" AND `ll`='".mysql_real_escape_string($vars['ll'])."' ";
		if(isset($vars['exclude_ll'])) $query.=" AND `ll`<>'".mysql_real_escape_string($vars['exclude_ll'])."' ";
		$query.=" ORDER BY `titolo` LIMIT ".$vars['start'].",".$vars['limit'];
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	
	function update($idphg,$titolo,$testo,$dir,$template="",$layout="",$ll=false) {
		if($ll==false) $ll=$_SESSION['ll'];
		
		$titolo=mysql_real_escape_string($titolo);
		$testo=mysql_real_escape_string($testo);
		$dir=mysql_real_escape_string($dir);
		
		$query="UPDATE `".TABLE_PHOTOGALLERY."` SET `titolo`='".$titolo."',`testo`='".$testo."',`dir`='".$dir."',`template`='".$template."',`layout`='".$layout."',`ll`='".$ll."' WHERE `idphg`=".$idphg." LIMIT 1";
		if(!mysql_query($query)) return false;
		else return $idphg;
		}
	
	function delById($idphg,$delImg=false) {
		if($delImg==true) {
			require_once(ADMINRELDIR.'inc/images.lib.php');
			$kaImages=new kaImages();
			
			$immagini=$kaImages->getList(TABLE_PHOTOGALLERY,$idphg);
			foreach($immagini as $img) {
				if(!$kaImages->delete($img['idimg'])) return false;
				}
			}

		$query="DELETE FROM ".TABLE_PHOTOGALLERY." WHERE idphg=".$idphg." LIMIT 1";
		if(!mysql_query($query)) return false;
		else return $idphg;
		}
	
	function sort($idphg) {
		for($i=0;isset($idphg[$i]);$i++) {
			if(is_numeric($idphg[$i])) {
				$query="UPDATE ".TABLE_PHOTOGALLERY." SET ordine='".($i+1)."' WHERE idphg=".$idphg[$i]." AND ll='".$_SESSION['ll']."' LIMIT 1";
				if(!mysql_query($query)) return false;
				}
			}
		return true;
		}
	
	public function setTranslations($idphg,$translations) {
		$query="UPDATE ".TABLE_PHOTOGALLERY." SET `traduzioni`='".mysql_real_escape_string($translations)."' WHERE `idphg`='".mysql_real_escape_string($idphg)."' LIMIT 1";
		if(mysql_query($query)) return true;
		else return false;
		}
	public function removePageFromTranslations($idphg) {
		$query="UPDATE ".TABLE_PHOTOGALLERY." SET `traduzioni`=REPLACE(`traduzioni`,'=".mysql_real_escape_string($idphg)."|','=|') WHERE `traduzioni` LIKE '%=".mysql_real_escape_string($idphg)."%|'";
		if(mysql_query($query)) return true;
		else return false;
		}

	}

?>