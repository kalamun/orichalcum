<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['collection'])) $_GET['collection']="";

define("PAGE_NAME","Modifica una voce nel men&ugrave; del sito");
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_POST['save'])) {
	$log="";

	if(empty($_POST['photogallery'])) $_POST['photogallery']=",";
	
	$query="UPDATE ".TABLE_MENU." SET `label`='".b3_htmlize($_POST['label'],true,"")."', `url`='".mysql_real_escape_string($_POST['url'])."', `photogallery`='".mysql_real_escape_string($_POST['photogallery'])."' WHERE idmenu=".intval($_GET['idmenu']);
	if(!mysql_query($query)) $log="Errore durante il salvataggio nel Database";
	
	if($log=="") echo '<div id="MsgSuccess">Men&ugrave; salvato con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}

/***/

?><h1><?php  echo PAGE_NAME; ?></h1>
	<br />
	
	<script type="text/javascript">
		/* funzioni per ajax */
		var timer=null;
		var markURLfield=function(success) {
			var target=document.getElementById('url')
			//cancello i caratteri non ammessi
			if(success=="false") {
				if(target.getAttribute('oldstyle')==null) target.setAttribute('oldstyle',target.style.border)
				target.style.border="1px solid #f00";
				}
			else target.style.border=target.getAttribute('oldstyle');
			}
		function checkURL(field) {
			var target=document.getElementById('url')
			//cancello i caratteri non ammessi
			target.value=target.value.replace(/ /g,'%20');
			if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
			t=setTimeout("b3_ajaxSend('post','ajax/checkUrl.php','url="+escape(field.value)+"',markURLfield);",500);
			}
		</script>
	<?php 
	if(isset($_GET['idmenu'])) {
		?>
		<form action="?collection=<?= urlencode($_GET['collection']); ?>&idmenu=<?= $_GET['idmenu']; ?>" method="post" enctype="multipart/form-data">
		<?php 
		$query="SELECT * FROM `".TABLE_MENU."` WHERE `idmenu`=".intval($_GET['idmenu']);
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		?>
		<div class="title"><?= b3_create_input("label","text","Titolo: ",b3_lmthize($row['label'],"input"),"300px",64); ?></div><br />
		<?= b3_create_input("url","text","URL: ",b3_lmthize($row['url'],"input"),"400px",250,'onkeyup="checkURL(this)"'); ?> <span class="help">assoluto o riferito alla <em>root</em></span><br />
		<br /><br>

		<div class="box <?= trim($row['photogallery'],",")=="" ? "closed" : "opened"; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('UI:Photogallery'); ?></h2>
			<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?submitlabel=<?= urlencode($kaTranslate->translate('UI:Add selected images to the list')); ?>&onsubmit=kAddImagesToPhotogallery','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('UI:Add images'); ?></a>
			<div id="photogallery"></div>
			<script type="text/javascript">
				kLoadPhotogallery('<?= $row['photogallery']; ?>');
			</script>
		</div>

		<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Meta-dati</h2>
		<div id="divMetadata"></div>
		<script type="text/javascript">kaMetadataReload('<?= TABLE_MENU; ?>',<?= $row['idmenu']; ?>);</script>
		<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_MENU; ?>&id=<?= $row['idmenu']; ?>','600px','400px')" class="smallbutton">Nuovo meta-dato</a>
		</div>


		<br />
		<br />
		<div class="submit" id="submit">
			<input type="button" class="button" value="&lt; Indietro" onclick="document.location='index.php?collection=<?= urlencode($_GET['collection']); ?>'" />
			<input type="submit" name="save" class="button" value="Salva le modifiche" />
			</div>
		</form>
		<?php 
		}
	else echo 'Nessuna voce selezionata';

	
include_once("../inc/foot.inc.php");
