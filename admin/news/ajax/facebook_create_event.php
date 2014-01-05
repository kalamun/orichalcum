<?
session_start();

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}

require_once('../../inc/log.lib.php');
$kaLog=new kaLog();

require_once('../../inc/config.lib.php');
$kaConfig=new kaImpostazioni();

require_once('../news.lib.php');
$kaNews=new kaNews();

define("PAGE_NAME","Facebook");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<style type="text/css">
	@import "<?php echo ADMINDIR; ?>css/screen.css";
	@import "<?php echo ADMINDIR; ?>css/main.lib.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>

<div id="iPopUpHeader">
	<h1>Crea un evento su facebook</h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">

<?

// Require the SDK file from wherever you've located it
require_once('../../inc/facebook/facebook.php');

$app_id=trim($kaConfig->getVar('facebook-config',1));
$app_secret=trim($kaConfig->getVar('facebook-config',2));
$page_id=trim($kaConfig->getVar('facebook-page',1));
$my_url=SITE_URL.ADMINDIR."news/ajax/facebook_create_event.php";
$code=isset($_REQUEST["code"])?$_REQUEST["code"]:'';

if(isset($_POST['insert'])) {
	$_SESSION['facebook_tmp']=array();
	$_SESSION['facebook_tmp']['idnews']=$_GET['id'];
	$_SESSION['facebook_tmp']['name']=$_POST['name'];
	$_SESSION['facebook_tmp']['start_time']=$_POST['start_time'];
	$_SESSION['facebook_tmp']['end_time']=$_POST['end_time'];
	$_SESSION['facebook_tmp']['location']=$_POST['location'];
	$_SESSION['facebook_tmp']['street']=$_POST['street'];
	$_SESSION['facebook_tmp']['city']=$_POST['city'];
	$_SESSION['facebook_tmp']['state']=$_POST['state'];
	$_SESSION['facebook_tmp']['country']=$_POST['country'];
	$_SESSION['facebook_tmp']['phone']=$_POST['phone'];
	$_SESSION['facebook_tmp']['email']=$_POST['email'];
	$_SESSION['facebook_tmp']['description']=$_POST['description'];
	}

/* if the user doesn't gave the right permissions to the app, ask for */
if(empty($code)) {
	$_SESSION['state']=md5(uniqid(rand(),true)); //CSRF protection
	$dialog_url="https://www.facebook.com/dialog/oauth?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&scope=read_friendlists,publish_stream,user_events,user_status,create_event,manage_pages&state=".$_SESSION['state'];
	?>
	<h2>Non hai ancora il permesso di pubblicare su questa pagina facebook</h2>
	<p>Per ottenere il permesso di farlo, clicca sul tasto qui sotto e concedi il permesso</p>
	<p><a href="<?= $dialog_url; ?>" target="_blank" class="button">Richiedi il permesso di pubblicazione</a></p>
	<?
	die();
	}

/* get an access token */
if($_REQUEST['state']==$_SESSION['state']) {
	$token_url="https://graph.facebook.com/oauth/access_token?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&client_secret=".$app_secret."&code=".$code;
	$response=file_get_contents($token_url);
	$params=null;
	parse_str($response,$params);

	$graph_url="https://graph.facebook.com/me?access_token=".$params['access_token'];
	$user=json_decode(file_get_contents($graph_url));
	$facebook=new Facebook(array(
		'appId'=>$app_id,
		'secret'=>$app_secret,
		'cookie'=>true,
		'fileUpload'=>true,
		));
	$facebook->setAccessToken($params['access_token']);
	$uid=$facebook->getUser();
	//$me=$facebook->api('/me');

	$accounts=$facebook->api("/me/accounts");
	foreach($accounts["data"] as $page) {
		if($page["id"]==$page_id) {
			$page_access_token=$page["access_token"];
			$page_name=$page["name"];
			break;
			}
		}
	
	$start_time=strftime("%Y-%m-%dT%H:%M:%S%z",mktime(substr($_SESSION['facebook_tmp']['start_time'],11,2),substr($_SESSION['facebook_tmp']['start_time'],14,2),substr($_SESSION['facebook_tmp']['start_time'],17,2),substr($_SESSION['facebook_tmp']['start_time'],3,2),substr($_SESSION['facebook_tmp']['start_time'],0,2),substr($_SESSION['facebook_tmp']['start_time'],6,4)));
	$end_time=strftime("%Y-%m-%dT%H:%M:%S%z",mktime(substr($_SESSION['facebook_tmp']['end_time'],11,2),substr($_SESSION['facebook_tmp']['end_time'],14,2),substr($_SESSION['facebook_tmp']['end_time'],17,2),substr($_SESSION['facebook_tmp']['end_time'],3,2),substr($_SESSION['facebook_tmp']['end_time'],0,2),substr($_SESSION['facebook_tmp']['end_time'],6,4)));
	$news=$kaNews->get($_SESSION['facebook_tmp']['idnews']);

	//create event
	$fb_event_array=array(
		'name'=>$_SESSION['facebook_tmp']['name'],
		'start_time'=>$start_time,
		'end_time'=>$end_time,
		'location'=>$_SESSION['facebook_tmp']['location'],
		'venue'=>array(
			'street'=>$_SESSION['facebook_tmp']['street'],
			'city'=>$_SESSION['facebook_tmp']['city'],
			'zip_code'=>'48010',
			'state'=>$_SESSION['facebook_tmp']['state'],
			'country'=>$_SESSION['facebook_tmp']['country'],
			),
		'phone'=>$_SESSION['facebook_tmp']['phone'],
		'email'=>$_SESSION['facebook_tmp']['email'],
		'description'=>$_SESSION['facebook_tmp']['description'],
		'privacy_type'=>"OPEN",
		);

	if(isset($news['imgallery'][0]['url'])&&$news['imgallery'][0]['url']!="") {
		$size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.$news['imgallery'][0]['url']);
		if($size[0]<200) {
			echo "Impossible to insert the cover image: the pic is too small!<br />";
			}
		else {
			$facebook->setFileUploadSupport(true);
			$fb_event_array['picture']='@'.$_SERVER['DOCUMENT_ROOT'].BASEDIR.$news['imgallery'][0]['url'];
			}
		}

	if($page_id!=""&&isset($page_access_token)) {
		$fb_event_array['owner']=array("id"=>$page_id,"name"=>$page_name);
		$fb_event_array['page_id']=$page_id;
		$fb_event_array['access_token']=$page_access_token;
		}
	else {
		$page_id='me';
		}

	$eventInfo=$facebook->api("/".$page_id."/events","POST",$fb_event_array);

	//invite friends/likers
//	$facebook->api($eventInfo['id']."/invited?users=1129283431,100002323610887","POST");

/*	$fb_feed_array=array(
		'message'=>'',
		'link'=>'https://www.facebook.com/events/'.$eventInfo['id'],
		);
	if(isset($news['imgallery'][0]['url'])&&$news['imgallery'][0]['url']!="") {
		$size=getimagesize($_SERVER['DOCUMENT_ROOT'].BASEDIR.$news['imgallery'][0]['url']);
		if($size[0]<200) {
			echo "Impossible to insert the cover image: the pic is too small!<br />";
			}
		else {
			$facebook->setFileUploadSupport(true);
			$fb_feed_array['picture']='@'.$_SERVER['DOCUMENT_ROOT'].BASEDIR.$news['imgallery'][0]['url'];
			}
		}
	if($page_id!="me") {
		$fb_event_array['page_id']=$page_id;
		$fb_event_array['access_token']=$page_access_token;
		}
	$postInfo=$facebook->api("/".$page_id."/feed","POST",$fb_feed_array);*/
	?>
	<h2>Evento creato</h2>
	<p>Puoi visualizzarlo qui:<br />
	<a href="https://www.facebook.com/events/<?= $eventInfo['id']; ?>" target="_blank">https://www.facebook.com/events/<?= $eventInfo['id']; ?></a>
	</p>
	<?
	unset($_SESSION['facebook_tmp']);
	}
else {
	echo "The state does not match. You may be a victim of CSRF.";
	}

?>

</div>

</body>
</html>
