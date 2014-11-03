<?php /* (c) Kalamun.org - GNU/GPL 3 */

// gestione del caricamento e del ridimensionamento delle immagini

class kDocuments {
	protected $img,$thumb;

	function __construct() {
		}
	
	function getList($tabella=false,$id=false,$orderby='ordine',$conditions='') {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_DOCS." WHERE iddoc>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";

		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			if($row['filename']!=""||$row['hotlink']=="") {
				if(trim($output[$i]['alt'])=="") $output[$i]['alt']=$output[$i]['filename'];
				$row['filename']=str_replace(" ","%20",$row['filename']);
				$row['filename']=str_replace("#","%23",$row['filename']);
				$row['filename']=str_replace("&","%26",$row['filename']);
				$row['filename']=str_replace("/","%2F",$row['filename']);
				$row['filename']=str_replace("?","%3F",$row['filename']);
				$row['filename']=str_replace("@","%40",$row['filename']);
				$output[$i]['url']=SITE_URL.BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename'];
				$output[$i]['hotlink']=false;
				}
			else {
				if(trim($output[$i]['alt'])=="") $output[$i]['alt']=$output[$i]['filename'];
				$output[$i]['filename']=basename($row['hotlink']);
				$output[$i]['url']=$row['hotlink'];
				$output[$i]['hotlink']=true;
				}

			$output[$i]['alt']=str_replace("\r","",str_replace("\n","",strip_tags($output[$i]['alt'])));
			$output[$i]['caption']=$row['alt'];
			$output[$i]['extension']=strtolower(substr($row['filename'],strrpos($row['filename'],".")+1));
			
			//if image, get dimensions
			$output[$i]['width']=0;
			$output[$i]['height']=0;
			if($output[$i]['hotlink']==false&&($output[$i]['extension']=="jpg"||$output[$i]['extension']=="jpeg"||$output[$i]['extension']=="png"||$output[$i]['extension']=="gif")) {
				$size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename']);
				$output[$i]['width']=$size[0];
				$output[$i]['height']=$size[1];
				}
			}
		return $output;
		}

	function getDocument($iddoc) {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_DOCS." WHERE iddoc=".$iddoc." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$row;
		if($row['filename']!=""||$row['hotlink']=="") {
			if(trim($output['alt'])=="") $output['alt']=$output['filename'];
			$tmpfilename=$row['filename'];
			$tmpfilename=str_replace(" ","%20",$tmpfilename);
			$tmpfilename=str_replace("#","%23",$tmpfilename);
			$tmpfilename=str_replace("&","%26",$tmpfilename);
			$tmpfilename=str_replace("/","%2F",$tmpfilename);
			$tmpfilename=str_replace("?","%3F",$tmpfilename);
			$tmpfilename=str_replace("@","%40",$tmpfilename);
			$output['url']=SITE_URL.BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename'];
			$output['hotlink']=false;
			}
		else {
			if(trim($output['alt'])=="") $output['alt']=$output['filename'];
			$output['filename']=basename($row['hotlink']);
			$output['url']=$row['hotlink'];
			$output['hotlink']=true;
			}
		$output['alt']=str_replace("\r","",str_replace("\n","",strip_tags($output['alt'])));
		$output['caption']=$row['alt'];
		$output['extension']=strtolower(substr($row['filename'],strrpos($row['filename'],".")+1));
		
		//if image, get dimensions
		$output['width']=0;
		$output['height']=0;
		if($output['hotlink']==false&&($output['extension']=="jpg"||$output['extension']=="jpeg"||$output['extension']=="png"||$output['extension']=="gif")) {
			$size=getimagesize(BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename']);
			$output['width']=$size[0];
			$output['height']=$size[1];
			}

		//retrieve filesize
		$output['filesize']=0;
		if($output['hotlink']==false) {
			if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename'])) $output['filesize']=filesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename']);
			}
		else {
			if(substr($output['url'],0,4)=='http') {
				$x=array_change_key_case(get_headers($output['url'],1),CASE_LOWER);
				if(strcasecmp($x[0],'HTTP/1.1 200 OK')!=0) $output['filesize']=$x['content-length'][1];
				else $output['filesize']=$x['content-length'];
				}
			}
		
		return $output;
		}		

	}


class kDocgallery {
	protected $kDocuments;
	
	function kDocgallery() {
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.'admin/inc/connect.inc.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.'inc/documents.lib.php');
		$this->kDocuments=new kDocuments();
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
			$output[$i]=$this->kDocuments->getDocument($row['iddoc']);
			$output[$i]['iddocg']=$row['iddocg'];
			//$output[$i]['ordineg']=$row['ordineg'];
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
		$output=$this->kDocuments->getDocument($row['iddoc']);
		$output['iddocg']=$row['iddocg'];
		$output['ordineg']=$row['ordineg'];
		$output['tabella']=$row['tabella'];
		$output['id']=$row['id'];
		
		return $output;
		}

	}

