<?php 
require_once('../main.lib.php');
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("x-frame-options"=>"", "check-permissions"=>false) );
//error_reporting(0);

/* an action required */
if(isset($_POST['action']))
{
	/* return image list */
	if($_POST['action']=='getImageList')
	{
		require_once('../images.lib.php');
		$kaImages=new kaImages();
		if(!isset($_POST['start'])) $_POST['start']=0;
		if(!isset($_POST['limit'])) $_POST['limit']=30;
		if(empty($_POST['orderby'])) $_POST['orderby']='`creation_date` DESC, `idimg` DESC';
		if(!isset($_POST['fileType'])) $_POST['fileType']="image";
		
		//if sorted by filename remove the limit because the natural sorting is made by PHP so it needs the entrire list
		if(strpos($_POST['orderby'],'filename')!==false)
		{
			$tmp=array("start"=>$_POST['start'], "limit"=>$_POST['limit']);
			$_POST['start']=0;
			$_POST['limit']=false;
		}
		
		$vars=array();
		$vars['start'] = $_POST['start'];
		if(!empty($_POST['limit'])) $vars['limit'] = $_POST['limit'];
		$vars['orderby'] = $_POST['orderby'];
		
		if(!isset($_POST['conditions'])) $_POST['conditions']='';
		if(isset($_POST['search'])&&$_POST['search']!="") $_POST['conditions'].=" AND (`filename` LIKE '%".ksql_real_escape_string($_POST['search'])."%' OR `thumbnail` LIKE '%".ksql_real_escape_string($_POST['search'])."%' OR `hotlink` LIKE '%".ksql_real_escape_string($_POST['search'])."%' OR `alt` LIKE '%".ksql_real_escape_string($_POST['search'])."%' OR `idimg`='".intval($_POST['search'])."')";
		if(substr($_POST['conditions'],0,5)==' AND ') $_POST['conditions']=substr($_POST['conditions'],5);
		
		$vars['conditions'] = $_POST['conditions'];
		
		if($_POST['fileType']=="image") $vars['filetype']=1;
		elseif($_POST['fileType']=="media") $vars['filetype']=2;
		elseif($_POST['fileType']=="documents") $vars['filetype']=3;


		$output=array();
		$images=array();
		
		foreach($kaImages->getList($vars) as $img)
		{
			$images[$img['idimg']]=$img;
			$output[$img['idimg']]=$img['filename'];
		}
		//if sorted by filename sort by natural order and apply offset and limit
		if(strpos($_POST['orderby'],'filename')!==false)
		{
			$tmpoutput=$output;
			$output=array();
			natsort($tmpoutput); //order by natural sort
			$i=0;
			foreach($tmpoutput as $idimg=>$filename)
			{
				if($i < $tmp['start']) { $i++; continue; }
				$output[$idimg]=$filename;
				if($i >= $tmp['start']+$tmp['limit']) break;
				$i++;
			}
		}
		
		foreach($output as $idimg=>$filename)
		{
			$img=$images[$idimg];
			echo $img['idimg']."\t".BASEDIR.dirname($img['url'])."/\t".$img['filename']."\t".$img['thumbnail']."\t".$img['width'].'x'.$img['height']."px\t".$img['alt']."\n";
		}
		
		die();
	}
	
	elseif($_POST['action']=='saveCaption')
	{
		require_once('../images.lib.php');
		$kaImages=new kaImages();
		if($kaImages->updateAlt($_POST['id'],$_POST['caption'])==true) echo $_POST['id'];
		else echo "false";
		die();
	}

	/* else someone is copying via internet */
	elseif($_POST['action']=='startInternetUpload')
	{
		// must be passed "url" and "copy" parameters. copy is a boolean var: if true than copy the file to the local server, else create an hotlink
		if(empty($_POST['url'])) return false;
		if(empty($_POST['copy'])) $_POST['copy'] = true;
		
		$filename = basename($_POST['url']);

		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/images.lib.php');
		$kaImages = new kaImages();
		$filetype = $kaImages->getFileType($filename);
		if($filetype==9) //dangerous files
		{
			$filename .= '-renamed';
			$filetype = 3;
		}

		$tmpfilename = $_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMP.'tmp'.time().$filename;
		
		// get file size (and verify the existance of the file)
		$ch = curl_init(substr($_POST['url'], 0, -strlen($filename)).rawurlencode($filename));
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data === false) {
			echo 'cURL failed';
			return false;
		}

		$contentLength = 0;
		$status = 'unknown';
		if (preg_match('/^HTTP\/1\.[01] (\d\d\d)/', $data, $matches)) {
		  $status = (int)$matches[1];
		}
		if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
		  $contentLength = (int)$matches[1];
		}
		if($contentLength == 0) return false;

		$remote = fopen(substr($_POST['url'], 0, -strlen($filename)).urlencode($filename), 'rb');
		$local = fopen($tmpfilename, 'wb');

		$read_bytes = 0;
		while(!feof($remote))
		{
			$buffer = fread($remote, 2048*4);
			fwrite($local, $buffer);

			$read_bytes += 2048*4;
			$progress = min(100, 100 * $read_bytes / $contentLength);

			//save progress to session
			if(!isset($_SESSION['fileUploadsProgress'])) $_SESSION['fileUploadsProgress'] = array();
			file_put_contents(dirname($tmpfilename).'/progress_'.basename($filename).'.txt',$progress); // I wrote the progress into a txt file because the php parser is busy on the upload procedure so it doesn't respond until the end of the upload
		}
		
		fclose($remote);
		fclose($local);

		$_SESSION['fileUploadsProgress'][$_POST['url']] = 'end';

		$filetypes = array(
			1 => "image",
			2 => "media",
			3 => "document",
			);
		
		// insert into db
		$idimg = $kaImages->upload($tmpfilename,$filename);
		echo $filetypes[$filetype].'|'.$idimg."|".$filename;

		/* clean tmp directory */
		$kaImages->cleanTmpDir();
	}

}


/* else someone is uploading... */
else {

	$filetypes = array(
		1 => "image",
		2 => "media",
		3 => "document",
		);

	/* check if it's an ajax upload */
	$headers=apache_request_headers();
	$filename=(isset($headers['X-Filename'])?$headers['X-Filename']:false);
	if($filename) {

		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/images.lib.php');
		$kaImages = new kaImages();
		$filetype = $kaImages->getFileType($filename);
		if($filetype==9) //dangerous files
		{
			$filename .= '-renamed';
			$filetype = 3;
		}
		
		/* move into the right directory and save in db */
		$tmpfilename = $_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMP.'tmp'.time().$filename;

		if(file_put_contents($tmpfilename, file_get_contents('php://input')))
		{
			// insert into db
			$idimg = $kaImages->upload($tmpfilename,$filename);
			echo $filetypes[$filetype].'|'.$idimg."|".$filename;

			/* clean tmp directory */
			$kaImages->cleanTmpDir();
		}
		else die("false");
		
	}
	else echo "false";
}
