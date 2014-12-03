<?php /* (c) Kalamun.org - GNU/GPL 3 */


/* functions to increase compatiblity on different configurations */
if(!function_exists('apache_request_headers'))
{
	function apache_request_headers()
	{
		$headers = array();
		foreach($_SERVER as $key => $value)
		{
			if(substr($key, 0, 5) == 'HTTP_') $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
		}
		return $headers;
	}
}


function kInit() {
	if(!defined("BASERELDIR")||!defined("ADMINDIR")) {
		$reldir="";
		$dirname=dirname($_SERVER['PHP_SELF']);
		$chars=count_chars(substr($dirname,strlen(BASEDIR)),1);
		if(!isset($chars[47])) $chars[47]=0;
		for($i=1;$i<=$chars[47];$i++) { $reldir.='../'; }
		if(!defined("BASERELDIR")) define("BASERELDIR",$reldir);
		if(!defined("ADMINDIR")) define("ADMINDIR",BASEDIR."admin/");
		}
	
	function kTxtLog($log) {
		if(defined("ksql_LOG")&&ksql_LOG==true) {
			$log="\n                      ".date("Y-m-d H:i:s")." ------------------------------\n".number_format($GLOBALS['microseconds'],6)."   ".$log."\n";
			file_put_contents($_SERVER['DOCUMENT_ROOT'].BASEDIR.'log.txt',$log,FILE_APPEND);
			}
		}
	}
kInit();

/* text utilities */
class kText
{

	/* parse requested tag and extract their position in string and their attributes */
	function tagParser($string,$tagref)
	{
		$tags=array();
		
		
		$offset=0;
		while(strpos($string,"<".$tagref." ",$offset)!==false)
		{
			$tag=array();
			$tag['start']=strpos($string,"<".$tagref." ",$offset);
			$tag['name']=$tagref;
			$tag['attributes']=array();
			$tag['innerHTML']="";
			$offset=$tag['start']+strlen($tagref)+2;
			$end=false;

			// parse every attribute till the end of tag
			while(strlen($string) > $offset)
			{
				// skip spaces
				for(; trim($string[$offset])==""; $offset++) {}
				// check the end of the tag
				if(strlen($string)-1 < $offset || $string[$offset]==">" || $string[$offset]=="/") break;
				
				$tagname="";
				// parse letters till "="
				while($string[$offset]!="=" && strlen($string) > $offset)
				{
					$tagname.=$string[$offset];
					$offset++;
				}
				
				// =
				$offset++;

				// skip spaces
				for(; trim($string[$offset])==""  && strlen($string)>$offset; $offset++) {}
				
				// check boundaries
				$bound="";
				if($string[$offset]=="'" || $string[$offset]=='"')
				{
					$bound=$string[$offset];
					$offset++;
				}
				
				// find the value, considering escape chars and the end of the tag
				$value="";
				while (
					strlen($string) > $offset &&
					(
						trim($string[$offset])!=$bound ||
						$string[$offset-1]=="\\"
					)
				)
				{
					// when no boundary is defined and there are spaces or >, skip
					if($bound=="" && (trim($string[$offset])=="" || $string[$offset]==">"))
					{
						$offset++;
						continue;
					}

					$value.=$string[$offset];
					$offset++;
				}
				
				$offset++;
				$tag['attributes'][trim($tagname)]=$value;
				
			}

			if(strlen($string) > $offset)
			{
				// skip latest spaces and tag's closure chars
				for(; trim($string[$offset])==""; $offset++) {}
				for(; $string[$offset]=="/" || $string[$offset]==">"; $offset++) {}
			}

			// look forward for close tag, if no other tags of the same type was opened before
			if(strlen($string)>$offset && strpos($string,"</".$tagref.">",$offset)!==false)
			{
				$closeoffset=strpos($string,"</".$tagref.">",$offset);
				if(strpos($string,"<".$tagref." ",$offset)===false || strpos($string,"<".$tagref." ",$offset)>$closeoffset)
				{
					// there are a valid close tag
					$tag['innerHTML']=substr($string,$offset,$closeoffset-$offset);
					$offset=$closeoffset+strlen("</".$tagref.">");
				}
			}
			
			$tag['end']=$offset;
			$tag['source']=substr($string,$tag['start'],$tag['end']-$tag['start']);

			$tags[]=$tag;

			if(strlen($string)<$offset) break;
		}
		
		return $tags;
	}

	
	/* format html text conforming the template re-definitions of tags */
	function formatText($string) {
		global $__template;

		//<a>
		$code=array();
		$tpl=$__template->getSubTemplate('a');
		if($tpl!="") {
			$offset=0;
			while(strpos($string,"<a ",$offset)!==false) {
				$id=count($code);
				$code[$id]['start']=strpos($string,"<a ",$offset);
				$code[$id]['end']=strpos($string,"</a>",$code[$id]['start'])+4;
				$tmp=substr($string,$code[$id]['start'],$code[$id]['end']-$code[$id]['start']);
				if(strpos($tmp," ")!==false) {
					$attr_offset=0;
					while(strpos($tmp," ",$attr_offset)!==false) {
						$attr_start=strpos($tmp," ",$attr_offset);
						$attr_stop=strpos($tmp,"=",$attr_start);
						$attribute=trim(substr($tmp,$attr_start,$attr_stop-$attr_start));
						$value_start=$attr_stop+1;
						if(substr($tmp,$value_start,1)=='"') $value_start++;
						$value_stop=strpos($tmp,'"',$value_start);
						$value=substr($tmp,$value_start,$value_stop-$value_start);
						while(substr($tmp,$value_start-1,2)=='\"') {
							$value_stop=strpos($tmp,"\"",$value_stop+1);
							}
						$code[$id]['contents'][$attribute]=$value;
						$code[$id]['contents']['contents']=preg_replace('/^.*?>(.*?)<\/a>.*/',"$1",$tmp);
						$attr_offset+=$value_stop+1;
						if($attr_offset>strlen($tmp)) $attr_offset=strlen($tmp);
						}
					}
				$offset=$code[$id]['end'];
				}
			for($i=count($code)-1;$i>=0;$i--) {
				$__template->contents=$code[$i]['contents'];
				$tpl=$__template->getSubTemplate('a');
				$string=substr_replace($string,$tpl,$code[$i]['start'],$code[$i]['end']-$code[$i]['start']);
				}
			}

		//<code>
		$code=array();
		$tpl=$__template->getSubTemplate('code');
		if($tpl!="") {
			$offset=0;
			while(strpos($string,"<code>",$offset)!==false) {
				$id=count($code);
				$code[$id]['start']=strpos($string,"<code>",$offset);
				$code[$id]['end']=strpos($string,"</code>",$code[$id]['start']);
				$code[$id]['contents']=substr($string,$code[$id]['start']+6,$code[$id]['end']-$code[$id]['start']-6);
				$offset=$code[$id]['end'];
				}
			for($i=count($code)-1;$i>=0;$i--) {
				$__template->contents=$code[$i]['contents'];
				$tpl=$__template->getSubTemplate('code');
				$string=substr_replace($string,$tpl,$code[$i]['start'],$code[$i]['end']-$code[$i]['start']);
				}
			}

		return $string;
		}

	function embedImg($string)
	{
		global $__template;
		$kImage=new kImages;
		$images=$this->tagParser($string,"img");

		$embeddedimages=array();
		for($i=count($images)-1;$i>=0;$i--)
		{
			$idimg=0;
			$attributes=$images[$i]['attributes'];
			if(isset($attributes['id']))
			{
				if(substr($attributes['id'],0,3)=="img") $idimg=intval(substr($attributes['id'],3));
				elseif(substr($attributes['id'],0,5)=="thumb") $idimg=intval(substr($attributes['id'],5));
			}

			if($idimg>0)
			{
				$__template->contents=array("name"=>$images[$i]['name'], "attributes"=>$images[$i]['attributes'], "innerHTML"=>$images[$i]['innerHTML']);
				$__template->imgDB=$kImage->getImage($idimg);
				if(isset($attributes['width'])) $__template->imgDB['width']=$attributes['width'];
				if(isset($attributes['height'])) $__template->imgDB['height']=$attributes['height'];
				if(isset($attributes['width'])) $__template->imgDB['thumb']['width']=$attributes['width'];
				if(isset($attributes['height'])) $__template->imgDB['thumb']['height']=$attributes['height'];
				if(isset($attributes['class'])) $__template->imgDB['class']=$attributes['class'];
				if(isset($attributes['alt'])) $__template->imgDB['alt']=$attributes['alt'];
				if(isset($attributes['caption'])) $__template->imgDB['caption']=$attributes['alt'];

				if(isset($attributes['style']))
				{
					// get width from CSS, prioritary respect the attribute width
					$attributes['style'].=";";
					if(preg_match("/width: ?([^;]+);/i",$attributes['style'],$match))
					{
						$__template->imgDB['width']=$match[1];
						$__template->imgDB['thumb']['width']=$match[1];
					}
					
					// get height from CSS, prioritary respect the attribute height
					$attributes['style'].=";";
					if(preg_match("/height: ?([^;]+);/i",$attributes['style'],$match))
					{
						$__template->imgDB['height']=$match[1];
						$__template->imgDB['thumb']['height']=$match[1];
					}
				}
				$embeddedimages[]=$__template->imgDB;
				
				// load image or thumbnail template
				if(substr($attributes['id'],0,3)=="img") $tpl=$__template->getSubTemplate('image');
				else $tpl=$__template->getSubTemplate('thumbnail');
				
				// replace the old image with the new one
				$string=substr_replace($string,$tpl,$images[$i]['start'],$images[$i]['end']-$images[$i]['start']);
			}
		}

		return array($string,$embeddedimages);
	}


	function embedDocs($string)
	{
		global $__template;
		$kDocuments=new kDocuments;
		$documents=$this->tagParser($string,"a");

		$embeddeddocs=array();
		for($i=count($documents)-1;$i>=0;$i--)
		{
			$iddoc=0;
			$attributes=$documents[$i]['attributes'];
			if(isset($attributes['id']))
			{
				if(substr($attributes['id'],0,3)=="doc") $iddoc=intval(substr($attributes['id'],3));
			}

			if($iddoc>0)
			{
				$__template->contents=array("name"=>$documents[$i]['name'], "attributes"=>$documents[$i]['attributes'], "innerHTML"=>$documents[$i]['innerHTML']);
				$__template->docDB=$kDocuments->getDocument($iddoc);
				if(!empty($documents[$i]['innerHTML'])) $__template->docDB['caption']=$documents[$i]['innerHTML'];
				$embeddeddocs[]=$__template->docDB;
				$tpl=$__template->getSubTemplate('document');
				$string=substr_replace($string,$tpl,$documents[$i]['start'],$documents[$i]['end']-$documents[$i]['start']);
			}
		}

		return array($string,$embeddeddocs);
	}

	function embedMedia($string) {
		global $__template;
		require_once('media.lib.php');
		$kMedia=new kMedia;
		$media=array();

		//detect delle immagini
		$offset=0;
		while(strpos($string,"<img ",$offset)!==false) {
			$id=count($media);
			$media[$id]['start']=strpos($string,"<img ",$offset);
			$media[$id]['end']=strpos($string,">",$media[$id]['start'])+1;
			$media[$id]['html']=substr($string,$media[$id]['start'],$media[$id]['end']-$media[$id]['start']);
			if(preg_match("/^.*id=[\"']?media(\d+)[\"'].*$/",$media[$id]['html'])) {
				$media[$id]['idmedia']=preg_replace("/^.*id=[\"']?media(\d+)[\"'].*$/","$1",$media[$id]['html']);
				}
			strpos($media[$id]['html'],' width=')!==false?$media[$id]['width']=preg_replace("/^.*width=[\"']?(\d+)[\"'].*$/","$1",$media[$id]['html']):$media[$id]['width']="";
			strpos($media[$id]['html'],' height=')!==false?$media[$id]['height']=preg_replace("/^.*height=[\"']?(\d+)[\"'].*$/","$1",$media[$id]['html']):$media[$id]['height']="";
			$offset=$media[$id]['end'];
			}

		$embeddedmedia=array();
		for($i=count($media)-1;$i>=0;$i--) {
			if(isset($media[$i]['idmedia'])) {
				$__template->mediaDB=$kMedia->getMedia($media[$i]['idmedia']);
				if($media[$i]['width']!="") $__template->mediaDB['width']=$media[$i]['width'];
				if($media[$i]['height']!="") $__template->mediaDB['height']=$media[$i]['height'];
				$embeddedmedia[]=$__template->mediaDB;
				$tpl=$__template->getSubTemplate('media');
				$string=substr_replace($string,$tpl,$media[$i]['start'],$media[$i]['end']-$media[$i]['start']);
				}
			}

		return array($string,$embeddedmedia);
		}

	}


/* statistiche */
function kStatistiche() {
	/* sposto la roba vecchia in archivio */
	$q="INSERT INTO `".TABLE_STATS_ARCHIVE."` (`ip`,`date`,`url`,`referer`,`system`,`contacts`,`ll`) SELECT `ip`,`date`,`url`,`referer`,`system`,`contacts`,`ll` FROM ".TABLE_STATISTICHE." WHERE `date`<'".date("Y-m-d H:i",time()-3600)."'";
	/**/ $GLOBALS['microseconds']=microtime();
	if(ksql_query($q)) {
		/**/ kTxtLog($q);
		$q="DELETE FROM `".TABLE_STATISTICHE."` WHERE `date`<'".date("Y-m-d H:i",time()-3600)."'";
		ksql_query($q);
		/**/ kTxtLog($q);
		}

	/* cattura indirizzo */
	$url=trim(BASEDIR,"/ ").'/';
	if(isset($_GET['lang'])) $url.=$_GET['lang'].'/';
	if(isset($GLOBALS['__dir__'])) $url.=$GLOBALS['__dir__'].'/';
	if(isset($GLOBALS['__subdir__'])) $url.=$GLOBALS['__subdir__'].'/';
	if(isset($GLOBALS['__subsubdir__'])) $url.=$GLOBALS['__subsubdir__'].'/';
	$url=trim($url,"/ ").'?';
	foreach($_GET as $k=>$v) {
		if($k!='lang'&&$k!='__dir__'&&$k!='__subdir__'&&$k!='__subsubdir__') $url.=$k.'='.urlencode($v).'&';
		}
	$url=trim($url,"&?");

	isset($_SERVER['HTTP_REFERER'])?$referer=addslashes($_SERVER['HTTP_REFERER']):$referer="";
	$system=$_SERVER['HTTP_USER_AGENT'];
	$ip=$_SERVER['REMOTE_ADDR'];

	/* filters: record only knowed browsers */
	if(strpos($system,"MSIE")!==false
		||strpos($system,"Firefox")!==false
		||strpos($system,"Chrome")!==false
		||strpos($system,"Safari")!==false
		||strpos($system,"Opera")!==false
		) {

		/* verifico se e' una nuova visita */
		$q="SELECT count(*) AS tot FROM ".TABLE_STATISTICHE." WHERE ip='".$ip."' AND system='".ksql_real_escape_string($system)."'";
		$p=ksql_query($q);
		$r=ksql_fetch_array($p);
		if($r['tot']==0) {
			//nuova visita
			$q="INSERT INTO ".TABLE_STATISTICHE." (`ip`,`date`,`url`,`referer`,`system`,`contacts`,`ll`) VALUES('".$ip."','".date("Y-m-d H:i",time())."','".ksql_real_escape_string($url)."','".ksql_real_escape_string($referer)."','".ksql_real_escape_string($system)."',1,'')";
			ksql_query($q);
			}
		else {
			//nuovo contatto
			$q="UPDATE ".TABLE_STATISTICHE." SET `date`='".date("Y-m-d H:i",time())."',`url`=CONCAT(url,'"."\n".ksql_real_escape_string($url)."'),`contacts`=`contacts`+1 WHERE `ip`='".$ip."' AND system='".ksql_real_escape_string($system)."'";
			$results=ksql_query($q);
			}
		}
	}


