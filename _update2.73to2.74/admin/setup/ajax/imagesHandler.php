<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

include('../../inc/connect.inc.php');
include('../../inc/images.lib.php');
$kaImages=new kaImages();

if(isset( $_GET['reprocess'] ))
{

	$images = $kaImages->getList('`idimg`', '', intval($_GET['reprocess']), 5);
	
	foreach($images as $img)
	{
		$ffile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . $img['url'];
		$ofile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . ltrim(DIR_IMG,"./") . $img['idimg'] .'/-originalsize';
		//$mfile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . ltrim(DIR_IMG,"./") . $img['idimg'] .'/m_'. $img['filename'];
		//$tfile = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . ltrim(DIR_IMG,"./") . $img['idimg'] .'/'. $img['thumbnail'];
		
		// if main image doesn't exists, skip
		if(!file_exists($ffile)) continue;
		
/*		// check for the original size image
		if(!file_exists($ofile))
		{
			// if they doesn't exist, create it from main image
			copy($ffile, $ofile);
		}*/
		
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

