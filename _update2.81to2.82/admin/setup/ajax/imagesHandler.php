<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

include('../../inc/connect.inc.php');
include('../../inc/images.lib.php');
$kaImages=new kaImages();

if(isset( $_GET['reprocess'] ))
{

	$images = $kaImages->getList(array( "filetype"=>1, "orderby"=>'`idimg`', "offset"=>intval($_GET['reprocess']), "limit"=>5 ));
	
	foreach($images as $img)
	{
		$ffile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . $img['url'];
		$ofile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . ltrim(DIR_IMG,"./") . $img['idimg'] .'/-originalsize';

		if(!file_exists($ffile)) continue;
		
		// update
		$file=$ofile;
		if(!file_exists($file)) $file=$ffile;
		$kaImages->updateImage($img['idimg'],$file,basename($ffile),true);
		
		// update all thumbnails but custom ones
		if($img['thumbnail']=='t_'.$img['filename']) $kaImages->setThumb($img['idimg']);
	}
	
	if(!empty($images)) echo "true";
	else echo "false";
}

