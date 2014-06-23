<?
require_once('../main.lib.php');
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("x-frame-options"=>"", "check-permissions"=>false) );
error_reporting(E_ALL);

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
		if(!isset($_POST['conditions'])) $_POST['conditions']='';
		if(isset($_POST['search'])&&$_POST['search']!="") $_POST['conditions'].=" AND (`filename` LIKE '%".mysql_real_escape_string($_POST['search'])."%' OR `thumbnail` LIKE '%".mysql_real_escape_string($_POST['search'])."%' OR `hotlink` LIKE '%".mysql_real_escape_string($_POST['search'])."%' OR `alt` LIKE '%".mysql_real_escape_string($_POST['search'])."%' OR `idimg`='".intval($_POST['search'])."')";
		if(substr($_POST['conditions'],0,5)==' AND ') $_POST['conditions']=substr($_POST['conditions'],5);
		if(!isset($_POST['orderby'])||$_POST['orderby']=="") $_POST['orderby']='`creation_date` DESC';

		foreach($kaImages->getList($_POST['orderby'],$_POST['conditions'],$_POST['start'],$_POST['limit']) as $img)
		{
			echo $img['idimg']."\t".BASEDIR.DIR_IMG.$img['idimg']."/\t".$img['filename']."\t".$img['thumbnail']."\t".$img['width'].'x'.$img['height']."px\t".$img['alt']."\n";
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
}


/* else someone is uploading... */
else
{

	/* check if is an ajax upload */
	$headers=apache_request_headers();
	$filename=(isset($headers['X-Filename'])?$headers['X-Filename']:false);
	if($filename) {

		/* check filename validity */
		// "disable" php, exe, js, html
		$filename=trim($filename," ./");
		$filename=str_replace("/","",$filename);
		$fileextension=substr($filename,strrpos($filename,".")+1);
		
		/* detect file type */
		$isImage=false;
		$isMedia=false;
		$isDocument=false;
		
		// images
		$ext=array(
			"png"=>true,
			"jpg"=>true,
			"jpeg"=>true,
			"gif"=>true
			);
		$mime=array(
			"image/jpeg"=>true,
			"image/pjpeg"=>true,
			"image/png"=>true,
			"image/gif"=>true
			);
		if(isset($ext[$fileextension])||isset($mime[$_SERVER['CONTENT_TYPE']])) $isImage=true;
		
		// medias
		$ext=array(
			"mov"=>true,
			"mpg"=>true,
			"mp3"=>true,
			"mp4"=>true,
			"webm"=>true,
			"ogv"=>true,
			"ogg"=>true,
			"oga"=>true,
			"avi"=>true,
			"wmv"=>true,
			"flv"=>true,
			"f4v"=>true,
			"swf"=>true
			);
		$mime=array(
			"video/quicktime"=>true,
			"video/mpeg"=>true,
			"video/x-mpeg"=>true,
			"video/avi"=>true,
			"video/msvideo"=>true,
			"video/x-flv"=>true,
			"video/x-f4v"=>true,
			"video/mp4"=>true,
			"video/x-ms-wmv"=>true,
			"video/ogg"=>true,
			"audio/mpeg"=>true,
			"audio/x-mpeg"=>true,
			"audio/mpeg3"=>true,
			"audio/x-mpeg3"=>true,
			"audio/ogg"=>true,
			"application/x-shockwave-flash"=>true
			);
		if(isset($ext[$fileextension])||isset($mime[$_SERVER['CONTENT_TYPE']])) $isMedia=true;
		
		// documents
		if($isImage==false && $isMedia==false) 
		{
			$isDocument=true;

			// rename file in case of file types not allowed
			$ext=array(
				"php"=>true,
				"php3"=>true,
				"exe"=>true,
				"msi"=>true
				);
			$mime=array(
				"text/php"=>true,
				"text/x-php"=>true,
				"application/php"=>true,
				"application/x-php"=>true,
				"application/x-httpd-php"=>true,
				"application/x-httpd-php-source"=>true,
				"application/x-msdownload"=>true
				);
			if(isset($ext[$fileextension])||isset($mime[$_SERVER['CONTENT_TYPE']])) $filename.='-renamed';
		}

		/* move into the right directory and save in db */
		echo 'is uploading: ';
		$tmpfilename=$_SERVER['DOCUMENT_ROOT'].BASEDIR.'arch/tmp'.time().$filename;
		echo $tmpfilename;
		if(file_put_contents($tmpfilename,file_get_contents('php://input')))
		{
			echo 'is uploaded';
			// well uploaded
			if($isImage==true)
			{
				// insert into db
				require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/images.lib.php');
				$kaImages=new kaImages();
				$idimg=$kaImages->upload($tmpfilename,$filename);
				echo 'image|'.$idimg."|".$filename;
			}

		}
		else die("false");
		
		
	}
	else echo "false";
}
?>