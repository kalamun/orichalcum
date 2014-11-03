<?php /* (c) Kalamun.org - GNU/GPL 3 */

class kImages {
	protected $img,$thumb;

	function __construct() {
		}

	function getList($tabella=false,$id=false,$orderby=false,$conditions='')
	{
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
		$output=array();
		if(empty($orderby)) $orderby='idimg';

		$lang= isset($_GET['lang']) ? strtoupper($_GET['lang']) : $_SESSION['ll'];

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`>0 ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" ORDER BY ".$orderby." ";

		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++)
		{
			$output[$i]=$row;
			if($row['filename']!=""||$output[$i]['hotlink']=="") {
				$row['filename']=str_replace(" ","%20",$row['filename']);
				$row['filename']=str_replace("#","%23",$row['filename']);
				$row['filename']=str_replace("&","%26",$row['filename']);
				$row['filename']=str_replace("/","%2F",$row['filename']);
				$row['filename']=str_replace("?","%3F",$row['filename']);
				$row['filename']=str_replace("@","%40",$row['filename']);
				$output[$i]['url']=SITE_URL.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'];
				$output[$i]['hotlink']=false;
				if($output[$i]['filename']!=""&&file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output[$i]['filename'])) $size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output[$i]['filename']);
				else $size=array(0,0,0,"");
				}
			else {
				$output[$i]['filename']=basename($row['hotlink']);
				$output[$i]['url']=$row['hotlink'];
				$output[$i]['hotlink']=true;
				if($output[$i]['filename']!=""&&file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output[$i]['filename'])) $size=getimagesize($row['hotlink']);
				else $size=array(0,0,0,"");
				}
			$output[$i]['width']=$size[0];
			$output[$i]['height']=$size[1];
			$row['thumbnail']=str_replace(" ","%20",$row['thumbnail']);
			$row['thumbnail']=str_replace("#","%23",$row['thumbnail']);
			$row['thumbnail']=str_replace("&","%26",$row['thumbnail']);
			$row['thumbnail']=str_replace("/","%2F",$row['thumbnail']);
			$row['thumbnail']=str_replace("?","%3F",$row['thumbnail']);
			$row['thumbnail']=str_replace("@","%40",$row['thumbnail']);
			$output[$i]['thumb']['url']=SITE_URL.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'];
			if($output[$i]['thumbnail']!=""&&file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output[$i]['thumbnail'])) $size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output[$i]['thumbnail']);
			else $size=array(0,0,0,"");
			$output[$i]['thumb']['width']=$size[0];
			$output[$i]['thumb']['height']=$size[1];

			$output[$i]['alts']=json_decode($output[$i]['alt'],true);
			if($output[$i]['alts']!=false)
			{
				if(!isset($output[$i]['alts'][$lang])) $output[$i]['alts'][$lang]="";
				$output[$i]['alt']=$output[$i]['alts'][$lang];
			}
			$output[$i]['caption']=$output[$i]['alt'];
			$output[$i]['alt']=strip_tags(trim(str_replace("\n","",$output[$i]['alt'])));
		}
		
		return $output;
	}

	function getImage($idimg)
	{
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_IMG." WHERE `idimg`=".intval($idimg)." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if(!isset($row['idimg'])) return false;

		$output=$row;
		if($row['filename']!=""||$output['hotlink']=="") {
			$row['filename']=str_replace(" ","%20",$row['filename']);
			$row['filename']=str_replace("#","%23",$row['filename']);
			$row['filename']=str_replace("&","%26",$row['filename']);
			$row['filename']=str_replace("/","%2F",$row['filename']);
			$row['filename']=str_replace("?","%3F",$row['filename']);
			$row['filename']=str_replace("@","%40",$row['filename']);
			$output['url']=SITE_URL.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'];
			$output['hotlink']=false;
			if($output['filename']!=""&&file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output['filename'])) $size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output['filename']);
			else $size=array(0,0,0,"");
			}
		else {
			$output['filename']=basename($row['hotlink']);
			$output['url']=$row['hotlink'];
			$output['hotlink']=true;
			if($output['filename']!=""&&file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output['filename'])) $size=getimagesize($row['hotlink']);
			else $size=array(0,0,0,"");
			}
		$output['width']=$size[0];
		$output['height']=$size[1];
		$row['thumbnail']=str_replace(" ","%20",$row['thumbnail']);
		$row['thumbnail']=str_replace("#","%23",$row['thumbnail']);
		$row['thumbnail']=str_replace("&","%26",$row['thumbnail']);
		$row['thumbnail']=str_replace("/","%2F",$row['thumbnail']);
		$row['thumbnail']=str_replace("?","%3F",$row['thumbnail']);
		$row['thumbnail']=str_replace("@","%40",$row['thumbnail']);
		$output['thumb']['url']=SITE_URL.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'];
		if($output['thumbnail']!=""&&file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output['thumbnail'])) $size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output['thumbnail']);
		else $size=array(0,0,0,"");
		$output['thumb']['width']=$size[0];
		$output['thumb']['height']=$size[1];
	
		$output['alts']=json_decode($output['alt'],true);
		if($output['alts']!=false)
		{
			if(!isset($output['alts'][LANG])) $output['alts'][LANG]="";
			$output['alt']=$output['alts'][LANG];
		}
		$output['caption']=$output['alt'];
		$output['alt']=strip_tags(trim(str_replace("\n","",$output['alt'])));

		//retrieve filesize
		$output['filesize']=0;
		if($output['hotlink']==false)
		{
			if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output['filename'])) $output['filesize']=filesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$row['idimg'].'/'.$output['filename']);
		} else {
			if(substr($output['url'],0,4)=='http') {
				$x=array_change_key_case(get_headers($output['url'],1),CASE_LOWER);
				if(strcasecmp($x[0],'HTTP/1.1 200 OK')!=0) $output['filesize']=$x['content-length'][1];
				else $output['filesize']=$x['content-length'];
			}
		}
				
		return $output;
	}		
		
}


class kImgallery {
	
	function kImgallery() {
		}
	
	function getList($tabella=false,$id=false,$orderby='ordine',$conditions='') {
		$output=array();

		$query="SELECT * FROM ".TABLE_IMGALLERY." WHERE idimga>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";

		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$GLOBALS['__images']->getImage($row['idimg']);
			$output[$i]['idimga']=$row['idimga'];
			$output[$i]['tabella']=$row['tabella'];
			$output[$i]['id']=$row['id'];
			}
		
		return $output;
		}
	
	function getImage($idimga) {
		$output=array();

		$query="SELECT * FROM ".TABLE_IMGALLERY." WHERE idimga=".$idimga." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$GLOBALS['__images']->getImage($row['idimg']);
		$output['idimga']=$row['idimga'];
		$output['tabella']=$row['tabella'];
		$output['id']=$row['id'];
		
		return $output;
		}
	}
