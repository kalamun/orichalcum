<?
/* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['collection'])) $_GET['collection']="";

define("PAGE_NAME","Modifica una voce nel men&ugrave; del sito");
include_once("../inc/head.inc.php");
include_once("../inc/images.lib.php");

$kaImages=new kaImages();

/* AZIONI */
if(isset($_POST['save'])) {
	$log="";

	$query="UPDATE ".TABLE_MENU." SET label='".b3_htmlize($_POST['label'],true,"")."',url='".b3_htmlize($_POST['url'],true,"")."' WHERE idmenu=".$_GET['idmenu'];
	if(!mysql_query($query)) $log="Errore durante il salvataggio nel Database";
	
	if(isset($_FILES['img'])&&$_FILES['img']['tmp_name']!="") {
		$img=$kaImages->getList(TABLE_MENU,$_GET['idmenu']);
		if(count($img)==0) $kaImages->upload($_FILES['img']['tmp_name'],$_FILES['img']['name'],TABLE_MENU,$_GET['idmenu'],$_POST['label'],false);
		else $kaImages->updateImage($img[0]['idimg'],$_FILES['img']['tmp_name'],$_FILES['img']['name'],$_POST['label'],false);
		}
	if($log=="") echo '<div id="MsgSuccess">Men&ugrave; salvato con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}

if(isset($_GET['delImage'])&&isset($_GET['idmenu'])) {
	$img=$kaImages->getList(TABLE_MENU,$_GET['idmenu']);
	$kaImages->delete($img[0]['idimg']);
	}

/***/

?><h1><? echo PAGE_NAME; ?></h1>
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
	<?
	if(isset($_GET['idmenu'])) {
		?>
		<form action="?collection=<?= urlencode($_GET['collection']); ?>&idmenu=<?= $_GET['idmenu']; ?>" method="post" enctype="multipart/form-data">
		<?
		$query="SELECT * FROM ".TABLE_MENU." WHERE idmenu=".$_GET['idmenu'];
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		echo b3_create_input("label","text","Titolo: ",b3_lmthize($row['label'],"input"),"300px",64).'<br /><br />';
		echo b3_create_input("url","text","URL: ",b3_lmthize($row['url'],"input"),"400px",250,'onkeyup="checkURL(this)"').' <span class="help">assoluto o riferito alla <em>root</em></span><br /><br />';
		?>
		
		<fieldset class="box">
		<?
		$img=$kaImages->getList(TABLE_MENU,$_GET['idmenu']);
		if(count($img)>0) { ?>
			<img src="<?= BASEDIR.DIR_IMG.$img[0]['idimg'].'/'.$img[0]['filename']; ?>" alt="<?= str_replace('"','&quot;',$img[0]['alt']); ?>" /><br />
			<a href="?delImage&collection=<?= urlencode($_GET['collection']); ?>&idmenu=<?= $_GET['idmenu']; ?>" class="smallbutton" onclick="return confirm('Sei sicuro di voler cancellare questa immagine?');"><?= $kaTranslate->translate('UI:Delete'); ?></a><br />
			<? }
		echo b3_create_input("img","file","Scegli immagine: ","").'<br />';
		?>
		</fieldset><br />

		<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Meta-dati</h2>
		<div id="divMetadata"></div>
		<script type="text/javascript">kaMetadataReload('<?= TABLE_MENU; ?>',<?= $row['idmenu']; ?>);</script>
		<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_MENU; ?>&id=<?= $row['idmenu']; ?>','600px','400px')" class="smallbutton">Nuovo meta-dato</a>
		</div>
		<script type="text/javascript">
			function showActions(td) {
				for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
					td.getElementsByTagName('DIV')[i].style.visibility='visible';
					}
				}
			function hideActions(td) {
				for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
					td.getElementsByTagName('DIV')[i].style.visibility='hidden';
					}
				}
			</script>

		<br />
		<br />
		<div class="submit" id="submit">
			<input type="button" class="button" value="&lt; Indietro" onclick="document.location='index.php?collection=<?= urlencode($_GET['collection']); ?>'" />
			<input type="submit" name="save" class="button" value="Salva le modifiche" />
			</div>
		</form>
		<?
		}
	else echo 'Nessuna voce selezionata';

	
include_once("../inc/foot.inc.php");
?>
