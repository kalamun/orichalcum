<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Welcomepage:Set a text for the welcome page");
include_once("../inc/head.inc.php");


/* SUBMIT ACTIONS */
if(isset($_POST['update'])) {
	$log="";

	$value1=b3_htmlize($_POST['title'],false,"");
	$value2=(trim(strip_tags($_POST['text'],"<a><img><embed><object>"))!="" ? b3_htmlize($_POST['text'],false) : '');
	$kaImpostazioni->setParam('siteboard',$value1,$value2,"*");

	if($log=="") echo '<div id="MsgSuccess">Configurazione salvata con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}
/**/

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br>
<div class="topset">
	<form action="?" method="post">
		<?
		$siteboard=array();
		$siteboard['title']=$kaImpostazioni->getVar('siteboard',1,"*");
		$siteboard['text']=$kaImpostazioni->getVar('siteboard',2,"*");
		?>
		
		<div class="title">
			<?= b3_create_input("title","text",$kaTranslate->translate("Welcomepage:Welcome title").'<br>',$siteboard['title'],"400px"); ?>
		</div>
		
		<br>
		<?= b3_create_textarea("text",$kaTranslate->translate("Welcomepage:Welcome text"),$siteboard['text'],"100%","500px"); ?>
		
		<br>
		<div class="submit">
			<input type="submit" name="update" value="<?= $kaTranslate->translate("UI:Save"); ?>" class="button">
		</div>
	</form>
</div>

<?	
include_once("../inc/foot.inc.php");
?>
