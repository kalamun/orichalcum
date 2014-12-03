<?php /* (c) Kalamun.org - GNU/GPL 3 */

class kMedia {
	protected $img,$thumb;

	function __construct() {
		}

	function getList($tabella=false,$id=false,$orderby='ordine',$conditions='') {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_MEDIA." WHERE idmedia>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";

		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['alt']=strip_tags(trim(str_replace("\n","",$output[$i]['alt'])));
			$output[$i]['caption']=$row['alt'];
			if($row['filename']!=""&&$output[$i]['hotlink']=="") {
				$row['filename']=str_replace(" ","%20",$row['filename']);
				$row['filename']=str_replace("#","%23",$row['filename']);
				$row['filename']=str_replace("&","%26",$row['filename']);
				$row['filename']=str_replace("/","%2F",$row['filename']);
				$row['filename']=str_replace("?","%3F",$row['filename']);
				$row['filename']=str_replace("@","%40",$row['filename']);
				$output[$i]['url']=SITE_URL.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['filename'];
				$output[$i]['hotlink']=false;
				$size=getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['filename']);
				}
			else {
				$output[$i]['filename']=basename($row['hotlink']);
				$output[$i]['url']=$row['hotlink'];
				$output[$i]['hotlink']=true;
				$size=getimagesize($row['hotlink']);
				}
			$output[$i]['width']=$size[0];
			$output[$i]['height']=$size[1];
			$row['thumbnail']=str_replace(" ","%20",$row['thumbnail']);
			$row['thumbnail']=str_replace("#","%23",$row['thumbnail']);
			$row['thumbnail']=str_replace("&","%26",$row['thumbnail']);
			$row['thumbnail']=str_replace("/","%2F",$row['thumbnail']);
			$row['thumbnail']=str_replace("?","%3F",$row['thumbnail']);
			$row['thumbnail']=str_replace("@","%40",$row['thumbnail']);
			$output[$i]['thumb']['url']=SITE_URL.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail'];
			if($output[$i]['thumbnail']!="") $size=getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail']);
			else $size=array(0,0,0,"");
			$output[$i]['thumb']['width']=$size[0];
			$output[$i]['thumb']['height']=$size[1];
			}
		
		return $output;
		}

	function getMimeType($file) {
		if(!file_exists($file)||is_dir($file)) return false;
		$mime="";
		if(function_exists("fifo_open")) {
			$finfo=finfo_open(FILEINFO_MIME);
			$mime=finfo_file($finfo,$file);
			finfo_close($finfo);
			}
		if($mime!=""&&function_exists("mime_content_type")) $mime=mime_content_type($file);
		if($mime=="") {
			$ext=substr($file,strrpos($file,'.')+1);
			switch(strtolower($ext)) {
				case "js":return "application/x-javascript";
				case "json":return "application/json";
				case "jpg":case "jpeg":case "jpe":return "image/jpg";
				case "png":case "gif":case "bmp":case "tiff":return "image/".strtolower($ext);
				case "css":return "text/css";
				case "xml":return "application/xml";
				case "doc":case "docx":return "application/msword";
				case "xls":case "xlt":case "xlm":case "xld":case "xla":case "xlc":case "xlw":case "xll":return "application/vnd.ms-excel";
				case "ppt":case "pps":return "application/vnd.ms-powerpoint";
				case "rtf":return "application/rtf";
				case "pdf":return "application/pdf";
				case "html":case "htm":case "php":return "text/html";
				case "txt":return "text/plain";
				case "mpeg":case "mpg":case "mpe":return "video/mpeg";
				case "mp3":return "audio/mpeg3";
				case "wav":return "audio/wav";
				case "aiff":case "aif":return "audio/aiff";
				case "avi":return "video/msvideo";
				case "wmv":return "video/x-ms-wmv";
				case "mov":return "video/quicktime";
				case "zip":return "application/zip";
				case "tar":return "application/x-tar";
				case "swf":return "application/x-shockwave-flash";
				case "flv":return "video/x-flv";
				case "f4v":return "video/f4v";
				case "odt":return "application/vnd.oasis.opendocument.text";
				case "ods":return "application/vnd.oasis.opendocument.spreadsheet";
				default:return "application/octet-stream";
				}
			}
		return $mime;
		}

	function getMedia($idmedia) {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_MEDIA." WHERE idmedia=".$idmedia." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$output=$row;
		$output['alt']=strip_tags(trim(str_replace("\n","",$output['alt'])));
		$output['caption']=$row['alt'];
		if($row['htmlcode']!="") {
			$output['url']=false;
			$output['hotlink']=false;
			$size=array(0,0,0,"");
			$output['mimetype']=$this->getMimeType($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['filename']);
			}
		elseif($row['filename']!=""&&$output['hotlink']=="") {
			$row['filename']=str_replace(" ","%20",$row['filename']);
			$row['filename']=str_replace("#","%23",$row['filename']);
			$row['filename']=str_replace("&","%26",$row['filename']);
			$row['filename']=str_replace("/","%2F",$row['filename']);
			$row['filename']=str_replace("?","%3F",$row['filename']);
			$row['filename']=str_replace("@","%40",$row['filename']);
			$output['url']=SITE_URL.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['filename'];
			$output['hotlink']=false;
			$output['htmlcode']=false;
			$output['mimetype']=$this->getMimeType($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['filename']);
			}
		else {
			$output['filename']=basename($row['hotlink']);
			$output['url']=$row['hotlink'];
			$output['hotlink']=true;
			$output['htmlcode']=false;
			$output['mimetype']=$this->getMimeType($row['hotlink']);
			}
		if(!isset($output['width'])) $output['width']=$size[0];
		if(!isset($output['height'])) $output['height']=$size[1];
		$row['thumbnail']=str_replace(" ","%20",$row['thumbnail']);
		$row['thumbnail']=str_replace("#","%23",$row['thumbnail']);
		$row['thumbnail']=str_replace("&","%26",$row['thumbnail']);
		$row['thumbnail']=str_replace("/","%2F",$row['thumbnail']);
		$row['thumbnail']=str_replace("?","%3F",$row['thumbnail']);
		$row['thumbnail']=str_replace("@","%40",$row['thumbnail']);
		$output['thumb']['url']=SITE_URL.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail'];
		if($output['thumbnail']!="") $size=getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail']);
		else $size=array(0,0,0,"");
		$output['thumb']['width']=$size[0];
		$output['thumb']['height']=$size[1];

		//retrieve filesize
		$output['filesize']=0;
		if($output['hotlink']==false) {
			$output['filesize']=filesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$output['filename']);
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
