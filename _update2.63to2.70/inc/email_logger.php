<?php 

require_once('./tplshortcuts.lib.php');
kInitBettino('../');

/* print an transparent gif of 1 x 1 px */
$file=$_SERVER["DOCUMENT_ROOT"].BASEDIR.'img/transparent.gif';
header('Content-type: image/gif');
header('Content-Disposition: inline; filename="'.basename($file).'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: '.filesize($file));


/* LOG WHEN AN E-MAIL IS READED */

if(!isset($_GET['uid'])) return false;
$GLOBALS['__emails']->setAsRead($_GET['uid']);


/* print the fucking gif */
echo file_get_contents($file);
