<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:General settings");
include_once("../inc/head.inc.php");

if(isset($_POST['update'])) {
	$log="";
	if(isset($_POST['seo']['robots'])) $_POST['seo']['robots']=implode(",",$_POST['seo']['robots']);
	else $_POST['seo']['robots']="";
	foreach($_POST['seo'] as $ka=>$v) {
		$kaImpostazioni->setParam('seo_'.$ka,b3_htmlize($v,true,""),"");
		}

	$kaImpostazioni->setParam('sitename',b3_htmlize($_POST['sitename'][1],false,""),b3_htmlize($_POST['sitename'][2],false,""),$_SESSION['ll']);
	$kaImpostazioni->setParam('footer',b3_htmlize($_POST['footer'][1],false),b3_htmlize($_POST['footer'][2],false,""),$_SESSION['ll']);
	$kaImpostazioni->setParam('timezone',$_POST['timezone'][1],$_POST['timezone'][2],$_SESSION['ll']);
	$kaImpostazioni->setParam('captcha',$_POST['captcha']['public'],$_POST['captcha']['private'],"*");

	echo '<div id="MsgSuccess">'.$kaTranslate->translate('Setup:Successfully saved').'</div>';
	$kaLog->add("UPD",'Setup: Changed some general settings');
	}

$sitename=$kaImpostazioni->getParam('sitename',$_SESSION['ll']);
$footer=$kaImpostazioni->getParam('footer',$_SESSION['ll']);
$timezone=$kaImpostazioni->getParam('timezone',$_SESSION['ll']);
$captcha=$kaImpostazioni->getParam('captcha',$_SESSION['ll']);
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<div class="topset">
	<form action="?" method="post">
		<table>
		<colgroup>
			<col width="180"></col>
			<col></col>
			</colgroup>
		
		<tr><td><h2><?= $kaTranslate->translate('Setup:Site name'); ?></h2></td>
		<td>
			<?= b3_create_input("sitename[1]","text",$kaTranslate->translate('Setup:Site name')." ",b3_lmthize($sitename['value1'],"input"),"400px",250); ?><br /><br />
			<?= b3_create_input("sitename[2]","text",$kaTranslate->translate('Setup:Payoff')." ",b3_lmthize($sitename['value2'],"input"),"400px",250); ?><br /><br />
			<br /><br />
		</td></tr>

		<tr><td><h2><?= $kaTranslate->translate('Setup:Footer'); ?></h2></td>
		<td>
			<?= b3_create_textarea("footer[1]",$kaTranslate->translate('Setup:Text')." ",b3_lmthize($footer['value1'],"textarea"),"100%","300px",RICH_EDITOR); ?>
			<br />
			<?= b3_create_input("footer[2]","text",$kaTranslate->translate('Setup:Copyright')." ",b3_lmthize($footer['value2'],"input"),"500px",250); ?><br />
			<br /><br /><br />
		</td></tr>
		
		<tr><td><h2><?= $kaTranslate->translate('Setup:Timezone'); ?></h2></td>
		<td>
			<label for="timezone1"><?= $kaTranslate->translate('Setup:Timezone'); ?></label>
			<? foreach(file('timezones.inc.php') as $line) {
				if(substr($line,15,strlen($timezone['value1']))==$timezone['value1']) $line=str_replace('<option','<option selected="selected"',$line);
				echo $line;
				} ?>
			<small><strong><?= $kaTranslate->translate('Setup:%s date','PHP'); ?></strong>: <?= date("d-m-Y H:i:s"); ?> &nbsp;&nbsp;
			<strong><?= $kaTranslate->translate('Setup:%s date','MySQL'); ?></strong>: <? $row=mysql_fetch_array(mysql_query("SELECT NOW() AS t")); echo $row['t']; ?></small><br />
			<br />
			<?
			echo b3_create_input("timezone[2]","text",$kaTranslate->translate('Setup:Date format')." ",b3_lmthize($timezone['value2'],"input"),"","","",true).' '; ?>
			<small>%d=<?= $kaTranslate->translate('Setup:day'); ?>, %m=<?= $kaTranslate->translate('Setup:month'); ?>, %Y=<?= $kaTranslate->translate('Setup:year'); ?>, %H=<?= $kaTranslate->translate('Setup:hour'); ?>, %i=<?= $kaTranslate->translate('Setup:minutes'); ?>, %s=<?= $kaTranslate->translate('Setup:seconds'); ?>, <a href="http://php.net/manual/en/function.strftime.php"><?= $kaTranslate->translate('Setup:others'); ?>...</a></small><br />
			<br /><br /><br />
		</td></tr>

		<tr><td><h2><?= $kaTranslate->translate('Setup:Search Engine Optimization'); ?></h2></td>
		<td>
			<table>
			<tr><td><label for="seodescription"><?= $kaTranslate->translate('SEO:Description'); ?></label></td><td><?= b3_create_input("seo[description]","text",'',b3_lmthize($kaImpostazioni->getVar('seo_description',1),"input"),"400px",250); ?></td></tr>
			<tr><td><label for="seokeywords"><?= $kaTranslate->translate('SEO:Keywords'); ?></label></td><td><?= b3_create_input("seo[keywords]","text",'',b3_lmthize($kaImpostazioni->getVar('seo_keywords',1),"input"),"400px",250); ?></td></tr>
			<tr><td><label for="seochangefreq"><?= $kaTranslate->translate('SEO:Change frequency'); ?></label></td><td><?= b3_create_select("seo[changefreq]",'',array($kaTranslate->translate('SEO:Always'),$kaTranslate->translate('SEO:Hourly'),$kaTranslate->translate('SEO:Daily'),$kaTranslate->translate('SEO:Weekly'),$kaTranslate->translate('SEO:Monthly'),$kaTranslate->translate('SEO:Yearly'),$kaTranslate->translate('SEO:Never')),array("always","hourly","daily","weekly","monthly","yearly","never"),b3_lmthize($kaImpostazioni->getVar('seo_changefreq',1),"input"),"",250); ?></td></tr>
			<tr><td><label for="seopriority"><?= $kaTranslate->translate('SEO:Priority'); ?></label></td><td><?= b3_create_input("seo[priority]","text",'',b3_lmthize($kaImpostazioni->getVar('seo_priority',1),"input"),"50px",3); ?> <span class="small"><?= $kaTranslate->translate('SEO:A decimal included from 0 to 1'); ?></span></td></tr>
			<tr><td><label for="seorobots"><?= $kaTranslate->translate('SEO:Robots'); ?></label></td><td>
				<?= b3_create_input("seo[robots][]","checkbox",$kaTranslate->translate('SEO:No index'),'noindex','','',(strpos($kaImpostazioni->getVar('seo_robots',1),"noindex")!==false?'checked':'')); ?><br />
				<?= b3_create_input("seo[robots][]","checkbox",$kaTranslate->translate('SEO:No follow'),'nofollow','','',(strpos($kaImpostazioni->getVar('seo_robots',1),"nofollow")!==false?'checked':'')); ?><br />
				<?= b3_create_input("seo[robots][]","checkbox",$kaTranslate->translate('SEO:No archive'),'noarchive','','',(strpos($kaImpostazioni->getVar('seo_robots',1),"noarchive")!==false?'checked':'')); ?><br />
				</td></tr>
			</table>
			<br /><br />
		</td></tr>
		
		<tr><td><h2><?= $kaTranslate->translate('Setup:CAPTCHA'); ?></h2></td>
			<td>
			<a href="http://www.recaptcha.net" target="_blank" class="smallbutton"><?= $kaTranslate->translate('Setup:create a new account on reCaptcha'); ?></a>
			<table>
			<tr><td><label for="captchapublic"><?= $kaTranslate->translate('Setup:Public key'); ?></label></td><td><?= b3_create_input("captcha[public]","text",'',b3_lmthize($kaImpostazioni->getVar('captcha',1,"*"),"input"),"400px",250); ?></td></tr>
			<tr><td><label for="captchaprivate"><?= $kaTranslate->translate('Setup:Private key'); ?></label></td><td><?= b3_create_input("captcha[private]","text",'',b3_lmthize($kaImpostazioni->getVar('captcha',2,"*"),"input"),"400px",250); ?></td></tr>
			</table>
			<small><?= $kaTranslate->translate('Setup:On conversions, set the moderation method to CAPTCHA then use {CAPTCHA} inside forms to use it.'); ?></small>
		</td></tr>
		
		</table>

		<br /><br />
		<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"></div>
	</form>
	</div>

<?
include_once("../inc/foot.inc.php");
?>
