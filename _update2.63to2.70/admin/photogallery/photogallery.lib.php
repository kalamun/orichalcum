<?php /* (c) Kalamun.org - GNU/GPL 3 */

class kaPhotogallery {
	protected $kaComments,$kaImgallery;
	
	function kaPhotogallery()
	{
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/comments.lib.php');
		$this->kaComments=new kaComments();
	}
	
	function add($vars)
	{
		if(empty($vars['ll'])) $vars['ll']=$_SESSION['ll'];
		if(!isset($vars['title'])) $vars['title']="";
		if(!isset($vars['dir'])) $vars['dir']=preg_replace("/[^\w]*/","-",strtolower($vars['titolo'])).'.html';
		if(!isset($vars['categories'])) $vars['categories']=",";
		if(!isset($vars['translations'])) $vars['translations']=",";
		
		//check if dir already exists and if it exists add a random numberic prefix
		$query="SELECT `idphg` FROM `".TABLE_PHOTOGALLERY."` WHERE `ll`='".$vars['ll']."' AND `dir`='".mysql_real_escape_string($vars['dir'])."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row!=false) $vars['dir']=rand(10000,99999).$vars['dir'];

		// get the order value
		$query="SELECT * FROM `".TABLE_PHOTOGALLERY."` WHERE ll='".$vars['ll']."' ORDER BY `ordine` DESC LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$order=$row['ordine']+1;
		
		$query="INSERT INTO `".TABLE_PHOTOGALLERY."` (
				`titolo`,
				`testo`,
				`dir`,
				`featuredimage`,
				`photogallery`,
				`categories`,
				`template`,
				`layout`,
				`traduzioni`,
				`ordine`,
				`ll`,
				`data`,
				`modified`
			) VALUES(
				'".b3_htmlize($vars['title'],true,"")."',
				'".b3_htmlize("",true)."',
				'".mysql_real_escape_string($vars['dir'])."',
				0,
				',',
				'".mysql_real_escape_string($vars['categories'])."',
				'',
				'',
				'".mysql_real_escape_string($vars['translations'])."',
				'".intval($order)."',
				'".mysql_real_escape_string($vars['ll'])."',
				NOW(),
				NOW()
				)";

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
	
	function update($idphg,$vars) {
		if(empty($vars['ll'])) $vars['ll']=$_SESSION['ll'];
		
		// check if dir already exists
		if(isset($vars['dir']))
		{
			$query="SELECT `idphg` FROM `".TABLE_PHOTOGALLERY."` WHERE `ll`='".$vars['ll']."' AND `dir`='".mysql_real_escape_string($vars['dir'])."' AND `idphg`<>'".intval($idphg)."' LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			if($row!=false) $vars['dir']=rand(10000,99999).$vars['dir'];
		}
		
	
		$query="UPDATE `".TABLE_PHOTOGALLERY."` SET ";
		if(isset($vars['title'])) $query.="`titolo`='".mysql_real_escape_string($vars['title'])."', ";
		if(isset($vars['text'])) $query.="`testo`='".mysql_real_escape_string($vars['text'])."', ";
		if(isset($vars['dir'])) $query.="`dir`='".mysql_real_escape_string($vars['dir'])."', ";
		if(isset($vars['template'])) $query.="`template`='".mysql_real_escape_string($vars['template'])."', ";
		if(isset($vars['layout'])) $query.="`layout`='".mysql_real_escape_string($vars['layout'])."', ";
		if(isset($vars['featuredimage'])) $query.="`featuredimage`='".mysql_real_escape_string($vars['featuredimage'])."', ";
		if(isset($vars['photogallery'])) $query.="`photogallery`='".mysql_real_escape_string($vars['photogallery'])."', ";
		if(isset($vars['categories'])) $query.="`categories`='".mysql_real_escape_string($vars['categories'])."', ";
		$query.="`ll`='".mysql_real_escape_string($vars['ll'])."', `modified`=NOW() WHERE `idphg`=".intval($idphg)." LIMIT 1";
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

