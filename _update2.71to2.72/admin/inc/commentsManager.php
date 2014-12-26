<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('./main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

if(!isset($_SESSION['iduser'])) die('You don\'t have permissions to use this page');
if(!isset($_GET['t'])) die();
if(!isset($_GET['id'])) die();


/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="")
{
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	ksql_query($query);
}

require_once('./log.lib.php');
$kaLog=new kaLog();

$kaTranslate=new kaAdminTranslate();
$kaComments=new kaComments();

define("PAGE_NAME","Comments:Comments management");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?= $kaTranslate->translate(PAGE_NAME); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/kzeneditor.css?<?= SW_VERSION; ?>" type="text/css" />

<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript">
	var ADMINDIR='<?= addslashes(ADMINDIR); ?>';
	var BASEDIR='<?= addslashes(BASEDIR); ?>';
</script>

</head>
<body>

<?php /* ADD COMMENTS */
if(isset($_POST['comment_add']))
{
	$results=$kaComments->add($_POST['comment_author'],$_POST['comment_email'],$_POST['comment_text'],$_GET['t'],$_GET['id']);
	if($results==false) $kaLog->add("ERR",'Comments: Error while adding a new comment to the id '.$_GET['id'].' and table '.$_GET['t']);
	else $kaLog->add("ADD",'Comments: added a comment (id '.$results.') to the id <em>'.$_GET['id'].' and table '.$_GET['t']);

/* SWAP VISIBILITY */
} elseif(isset($_GET['comment_approve'])) {
	$results=$kaComments->approve($_GET['comment_approve']);
	if($results==false) $kaLog->add("ERR",'Comments: Error while approving a comment (id '.$_GET['comment_approve'].')');
	else $kaLog->add("UPD",'Comments: approved / hid a comment (id '.$_GET['comment_approve'].')');

} elseif(isset($_GET['comment_delete'])) {
	$results=$kaComments->delete($_GET['comment_delete']);
	if($results==false) $kaLog->add("ERR",'Comments: Error while deleting a comment (id '.$_GET['comment_delete'].')');
	else $kaLog->add("DEL",'Comments: removed a comment (id '.$_GET['comment_delete'].')');

}
?>

<div id="iPopUpHeader">
	<h1><?= $kaTranslate->translate('Comments:Comments management'); ?></h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
</div>

<div class="padding"><?php 
	$i=0;
	foreach($kaComments->getList($_GET['t'],$_GET['id']) as $c) { ?>
		<div id="comment<?= $c['idcomm']; ?>" class="comment <?= ($c['public']=='n'?'disapproved':'approved'); ?>">
			<small><?= preg_replace("/(\d{4})-(\d{2})-(\d{2}).*/","$3-$2-$1",$c['data']); ?> - <?= $c['autore']; ?></small><br />
			<?= $c['testo']; ?><br />
			<small class="actions">
				<a href="?t=<?= urlencode($_GET['t']); ?>&id=<?= urlencode($_GET['id']); ?>&comment_approve=<?= $c['idcomm']; ?>"><?= ($c['public']=='n' ? $kaTranslate->translate('Comments:Approve') : $kaTranslate->translate('Comments:Hide')); ?></a> |
				<a href="?t=<?= urlencode($_GET['t']); ?>&id=<?= urlencode($_GET['id']); ?>&comment_delete=<?= $c['idcomm']; ?>" class="warning" onclick="return confirm('<?= $kaTranslate->translate('UI:Are you sure?'); ?>');"><?= $kaTranslate->translate('Comments:Delete'); ?></a>
			</small>
		</div>
		<?php 
		$i++;
		}

	if($i==0)
	{ ?>
		<?= $kaTranslate->translate('Comments:No comments'); ?><br>
	<?php  } ?>
	
	<br>
	<h2><?= $kaTranslate->translate('Comments:Add a comment'); ?></h2>
	<form action="" method="post">
		<?= b3_create_input("comment_author","text",$kaTranslate->translate('Comments:Author')." ",b3_lmthize($_SESSION['name'],"input"),"200px",250); ?><br>
		<?= b3_create_input("comment_email","text",$kaTranslate->translate('Comments:E-mail')." ",b3_lmthize($_SESSION['email'],"input"),"200px",250); ?><br>
		<?= b3_create_textarea("comment_text",$kaTranslate->translate('Comments:Comment')."<br />","","100%","100px");?><br>
		<div class="submit"><input type="submit" name="comment_add" value="<?= $kaTranslate->translate('Comments:Add Comment'); ?>" class="button"></div>
	</form>
	
</div>

<script type="text/javascript">
	var txts=new kInitZenEditor;
	txts.init('<?= addslashes(ADMINDIR); ?>');
</script>


</body>
</html>