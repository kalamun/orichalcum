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

		$lang = defined(LANG) ? LANG : (isset($_SESSION['ll']) ? $_SESSION['ll'] : DEFAULT_LANG);

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`>0 ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" ORDER BY ".$orderby." ";
		$results=ksql_query($query);
		
		for($i=0; $row=ksql_fetch_array($results); $i++)
		{
			$output[] = $this->row2output($row);
		}
		
		return $output;
	}

	function getImage($idimg)
	{
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_IMG." WHERE `idimg`=".intval($idimg)." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		if(!isset($row['idimg'])) return false;

		$output = $this->row2output($row);

		return $output;
	}

	public function row2output($row)
	{
		$output = $row;

		// directory where the file is stored
		if($row['filetype'] == 1) $dir = DIR_IMG;
		elseif($row['filetype'] == 2) $dir = DIR_MEDIA;
		elseif($row['filetype'] == 3) $dir = DIR_DOCS;
		
		$output['metadata'] = unserialize($output['metadata']);
		if(!isset($output['metadata']['width'])) $output['metadata']['width'] = 0;
		if(!isset($output['metadata']['height'])) $output['metadata']['height'] = 0;
		
		$output['mimetype'] = $this->getMimeType($output['filename']);
		
		if(!empty($row['filename']) || empty($output['hotlink']))
		{
			// local file
			$output['hotlink']=false;

			$row['filename']=str_replace(" ","%20",$row['filename']);
			$row['filename']=str_replace("#","%23",$row['filename']);
			$row['filename']=str_replace("&","%26",$row['filename']);
			$row['filename']=str_replace("/","%2F",$row['filename']);
			$row['filename']=str_replace("?","%3F",$row['filename']);
			$row['filename']=str_replace("@","%40",$row['filename']);
			
			if( $output['filetype'] == 1 )
			{
				// if mobile version is active and mobile alternative exists, get it
				if(kGetVar('img_mobile',1,'*')=="y" && file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.$row['idimg'].'/m_'.$output['filename']) && kIsMobile()) $output['filename']='m_'.$output['filename'];
				// else get the normal file
				elseif(!empty($output['filename']) && file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.$row['idimg'].'/'.$output['filename']) ) $size = getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.$row['idimg'].'/'.$output['filename']);
			}
			
			if(empty($size)) $size=array($output['metadata']['width'], $output['metadata']['height'], 0 , "");
			
			$output['url']=SITE_URL.BASEDIR.$dir.$row['idimg'].'/'.$output['filename'];

		} else {
			// hotlink
			$output['filename']=basename($row['hotlink']);
			$output['url']=$row['hotlink'];
			$output['hotlink']=true;
			
			if($output['filetype']==1 && $output['filename']!="") $size = getimagesize($row['hotlink']);
			if(empty($size)) $size=array($output['metadata']['width'], $output['metadata']['height'], 0 , "");
		}

		$output['width']=$size[0];
		$output['height']=$size[1];

		// thumbnail (it is always an image)
		$row['thumbnail']=str_replace(" ","%20",$row['thumbnail']);
		$row['thumbnail']=str_replace("#","%23",$row['thumbnail']);
		$row['thumbnail']=str_replace("&","%26",$row['thumbnail']);
		$row['thumbnail']=str_replace("/","%2F",$row['thumbnail']);
		$row['thumbnail']=str_replace("?","%3F",$row['thumbnail']);
		$row['thumbnail']=str_replace("@","%40",$row['thumbnail']);

		$output['thumb']['url']=SITE_URL.BASEDIR.$dir.$row['idimg'].'/'.$row['thumbnail'];
		if( $output['thumbnail']!="" && file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.$row['idimg'].'/'.$output['thumbnail']) ) $size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.$row['idimg'].'/'.$output['thumbnail']);
		if(empty($size)) $size=array($output['metadata']['width'], $output['metadata']['height'], 0 , "");

		$output['thumb']['width']=$size[0];
		$output['thumb']['height']=$size[1];
	
		// alternative text based on current language
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
			if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.$row['idimg'].'/'.$output['filename'])) $output['filesize']=filesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.$row['idimg'].'/'.$output['filename']);
		} else {
			if(substr($output['url'],0,4)=='http') {
				$x=array_change_key_case(get_headers($output['url'],1),CASE_LOWER);
				if(strcasecmp($x[0],'HTTP/1.1 200 OK')!=0) $output['filesize'] = $x['content-length'][1];
				else $output['filesize'] = $x['content-length'];
			}
		}
		
		return $output;
	}

	/* get mime type based on file name */
	function getMimeType($filename)
	{
		$filename=trim(basename($filename)," ./");
		$filename=str_replace("/","",$filename);
		$fileextension=substr($filename,strrpos($filename,".")+1);
		
		$mime=array(
			"jpg"=>"image/jpeg",
			"jpeg"=>"image/jpeg",
			"png"=>"image/png",
			"gif"=>"image/gif",
			"mov"=>"video/quicktime",
			"mpg"=>"video/mpeg",
			"avi"=>"video/avi",
			"wmv"=>"video/msvideo",
			"flv"=>"video/x-flv",
			"f4v"=>"video/x-f4v",
			"mp4"=>"video/mp4",
			"ogv"=>"video/ogg",
			"mp3"=>"audio/mpeg",
			"ogg"=>"audio/ogg",
			"midi"=>"audio/x-midi",
			"swf"=>"application/x-shockwave-flash",
			"php"=>"text/php",
			"doc"=>"application/msword",
			"docx"=>"application/vnd.openxmlformats",
			"ppt"=>"application/vnd.ms-powerpoint",
			"pptx"=>"application/vnd.openxmlformats",
			"xls"=>"application/vnd.ms-excel",
			"xlsx"=>"application/vnd.openxmlformats",
			"html"=>"text/html",
			"svg"=>"image/svg+xml",
			"txt"=>"text/plain",
			"zip"=>"application/zip",
			"xml"=>"application/xml",
			"pdf"=>"application/pdf"
			);
		if(!empty($mime[$fileextension])) return $mime[$fileextension];
		return false;
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

		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
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
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$output=$GLOBALS['__images']->getImage($row['idimg']);
		$output['idimga']=$row['idimga'];
		$output['tabella']=$row['tabella'];
		$output['id']=$row['id'];
		
		return $output;
		}
	}
