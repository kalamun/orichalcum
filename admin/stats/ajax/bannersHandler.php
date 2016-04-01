<?php
/*
returns informations about the banners, such as the views per day etc etc
"action" is a mandatory input variable
*/

require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

include_once("../stats.lib.php");
$kaStats=new kaStats();

if(!isset($_GET['action'])) die('missing action');

if($_GET['action']=='monthDetails')
{
	//
	if(!isset($_GET['idbanner'])) die('missing idbanner');
	if(!isset($_GET['m'])) die('missing month');
	if(!isset($_GET['y'])) die('missing year');
	
	$currentTimestamp = mktime(1,0,0, $_GET['m'], 15, $_GET['y']);
	$numberOfDays = date("t", $currentTimestamp);

	echo '{';
	echo '"month":"'.$_GET['y'].'-'.sprintf("%02d",$_GET['m']).'",';
	echo '"views":[';
	
	// count how many views
	for($i=1; $i<$numberOfDays; $i++)
	{
		if($i>1) echo ',';
		echo intval( $kaStats->getSummaryCount("banner", "view", $_GET['idbanner'].': '.$_GET['y'].'-'.sprintf("%02d",$_GET['m']).'-'.sprintf("%02d",$i)) );
	}
	echo '],';

	echo '"clicks":[';
	// count how many clicks
	for($i=1; $i<$numberOfDays; $i++)
	{
		if($i>1) echo ',';
		echo intval( $kaStats->getSummaryCount("banner", "click", $_GET['idbanner'].': '.$_GET['y'].'-'.sprintf("%02d",$_GET['m']).'-'.sprintf("%02d",$i)) );
	}
	echo ']';

	echo '}';
}