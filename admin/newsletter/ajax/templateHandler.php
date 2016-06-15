<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

if(!isset($_SESSION['exists'])) die();
if(!isset($_GET['template'])) die('0');

require_once('../newsletter.lib.php');
$kaNewsletter = new kaNewsletter;

foreach($kaNewsletter->getTextBlocksFromTemplate($_GET['template']) as $block)
{
	if($block=='') $block = '-default-';
	echo $block."\n";
}

