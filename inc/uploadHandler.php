<?
require_once('./tplshortcuts.lib.php');
kInitBettino('../');


/* check if user is logged */
if(!kMemberIsLogged()) die("false");

/* check if is an ajax upload */
$headers=apache_request_headers();
$filename=(isset($headers['X-Filename'])?$headers['X-Filename']:false);
$dir=(isset($headers['X-Dir'])?$headers['X-Dir']:false);
if($filename&&$dir) {

	/* check if is a valid folder (if it's valid, if it exists and if user can write inside) */
	$dir=trim($dir," ./");
	$dir=str_replace("../","",$dir);
	
	if(!kPrivateDirExists($dir)) die("false");
	if(!kPrivateDirIsWritable($dir)) die("false");

	/* check filename validity */
	// "disable" php, exe, js, html, swf
	$filename=trim($filename," ./");
	$filename=str_replace("/","",$filename);

	$ext=array(
		"text/html"=>true,
		"application/javascript"=>true,
		"application/x-msdownload"=>true,
		"text/php"=>true,
		"text/x-php"=>true,
		"application/php"=>true,
		"application/x-php"=>true,
		"application/x-httpd-php"=>true,
		"application/x-httpd-php-source"=>true,
		"application/x-shockwave-flash"=>true
		);
	if(isset($ext[$_SERVER['CONTENT_TYPE']])) $filename.='-renamed';

	if(file_put_contents($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir.'/'.$filename,file_get_contents('php://input'))) echo $filename;
	else echo "false";
	}
else echo "false";
?>