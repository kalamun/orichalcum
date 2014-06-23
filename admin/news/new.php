<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","News:Write a new News");
include_once("../inc/head.inc.php");
include_once("./news.lib.php");
$kaNews=new kaNews();
$pageLayout=$kaImpostazioni->getVar('admin-news-layout',1,"*");


/*
if the current language is different from the page language, means that the user have clicked on the flag and are requesting the translation of this page.
- if the page has a translation in the requested language, edit the translation
- if the page hasn't a translated version, create a new translate page
*/
if(isset($_GET['translate'])) {
	$page=$kaNews->get($_GET['translate']);
	if($_SESSION['ll']==$page['ll']) $page['traduzioni'][$_SESSION['ll']]=$page['idnews'];
	if(isset($page['traduzioni'][$_SESSION['ll']])&&$page['traduzioni'][$_SESSION['ll']]!="") {
		$url="edit.php?idnews=".$page['traduzioni'][$_SESSION['ll']];
		?>
		<div class="MsgNeutral">
			<h2><?= $kaTranslate->translate('News:Searching for translation'); ?></h2>
			<a href="<?= $url; ?>"><?= $kaTranslate->translate('News:if nothing happens, click here'); ?></a>
			<meta http-equiv="refresh" content="0;URL='<?= $url; ?>'">
			</div>
		<?
		die();
		}
	}



/* AZIONI */
if(isset($_POST['insert'])) {
	$log="";
	$categorie=",";
	if(isset($_POST['idcat'])) {
		foreach($_POST['idcat'] as $idcat) {
			$categorie.=$idcat.',';
			}
		}
	if(trim($categorie,",")=="") {
		require_once('../inc/categorie.lib.php');
		$kaCategorie=new kaCategorie();
		foreach($kaCategorie->getList(TABLE_NEWS) as $cat) {
			$categorie=','.$cat['idcat'].',';
			break;
			}
		}

	if(isset($_POST['visible_day'])&&isset($_POST['visible_hour'])) $visible_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['visible_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['visible_hour']);
	else $visible_date=date("Y-m-d H:i");

	if(isset($_POST['expiration_day'])&&isset($_POST['expiration_hour'])) $expiration_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['expiration_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['expiration_hour']);
	else $expiration_date=date("Y-m-d H:i");

	if(isset($_POST['starting_day'])&&isset($_POST['starting_hour'])) $starting_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['starting_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['starting_hour']);
	else $starting_date=$expiration_date;

	$values=array();
	$values['title']=b3_htmlize($_POST['titolo'],false,"");
	$values['categories']=$categorie;
	$values['creation_date']=date("Y-m-d H:i");
	$values['public_date']=$visible_date;
	$values['starting_date']=$starting_date;
	$values['expiration_date']=$expiration_date;
	$values['dir']=$_POST['dir'];
	$values['home']='s';
	if(isset($_POST['copyfrom'])) $values['copyfrom']=intval($_POST['copyfrom']);
	$id=$kaNews->add($values);

	if($id==false) $log="Problemi durante l'inserimento nel database";
	else {

		//if the news is a translated version of another news
		if(isset($_POST['translation_id'])&&$_POST['translation_id']!="") {
			$news=$kaNews->get($_POST['translation_id']);
			// first of all, clear translations from previous+current pages
			foreach($news['traduzioni'] as $k=>$v) {
				if($v!="") $kaNews->removePageFromTranslations($v);
				}
			// translation has this format: |LL=idpag|LL=idpag|...
			$news['traduzioni'][$_SESSION['ll']]=$id;
			$translations="|";
			foreach($news['traduzioni'] as $k=>$v) {
				$translations.=$k."=".$v."|";
				}
			// then set the new translations in the current pages
			foreach($news['traduzioni'] as $k=>$v) {
				if($v!="") {
					$kaNews->setTranslations($v,$translations);
					}
				}
			}
		}


	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nella creazione della news <em>'.b3_htmlize($_POST['dir'],true,"").'</em>');
		}
	else {
		$kaLog->add("INS",'Creata la news: <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$kaImpostazioni->getVar('dir_news',1).'/tmp/'.$_POST['dir'].'">'.$_POST['titolo'].'</a> (<em>ID: '.$id.'</em>)');
		echo '<div id="MsgSuccess">Notizia inserita con successo.<br />Attendi...</div>';
		echo '<meta http-equiv="refresh" content="0; url=edit.php?idnews='.$id.'">';
		include(ADMINRELDIR.'inc/foot.inc.php');
		die();
		}
	}
/* FINE AZIONI */


if(isset($_GET['copyfrom'])) $copyfrom=$kaNews->get($_GET['copyfrom']);

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<script type="text/javascript" src="./js/edit.js"></script>
<script type="text/javascript">
	function checkForm() {
		if(document.getElementById("titolo").value.length==0) { alert("Devi specificare un titolo"); return false; }
		else if(document.getElementById("testo").value.length==0) { alert("Devi specificare un testo"); return false; }
		else { return true; }
	}
	</script>


<form action="?" method="post" onsubmit="return checkForm();">

	<div class="topset">

		<script type="text/javascript">
		var timer=null;
		var markURLfield=function(success) {
			if(success=="true") {
				var d=new Date();
				document.getElementById('dirYetExists').style.display="inline";
				document.getElementById('dir').value=d.getUTCFullYear()+'-'+(d.getUTCMonth()+1)+'-'+d.getUTCDate()+'-'+document.getElementById('dir').value;
				checkURL(document.getElementById('dir'));
				}
			else document.getElementById('dirYetExists').style.display="none";
			}
		function checkURL(urlField) {
			var target=document.getElementById('dir')
			//cancello i caratteri non ammessi
			if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
			ajaxTimer=setTimeout(function() {
				var aj=new kAjax();
				aj.onSuccess(markURLfield);
				aj.send('post','ajax/checkUrl.php','url='+escape(urlField.value));
				},1000);
			}
		function title2url() {
			var titleField=document.getElementById('titolo');
			var urlField=document.getElementById('dir');
			if(!urlField.getAttribute("manualmode")&&titleField.value!="") {
				urlField.value=titleField.value.toLowerCase().replace(/[^\w\/\.\-\u00C0-\uD7FF\u2C00-\uD7FF]+/g,"-")+'.html';
				checkURL(urlField);
				}
			}
		function titleBlur() {
			var titleField=document.getElementById('titolo');
			var urlField=document.getElementById('dir');
			checkURL(urlField);
			}
		function selectMenuRef(f) {
			if(f.checked) {
				k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
				}
			}
		function dirKeyUp(e) {
			urlField=this;
			if(e.keyCode!=37&&e.keyCode!=38&&e.keyCode!=39&&e.keyCode!=40&&e.keyCode!=9&&urlField.value!=""&&urlField.value!=".html") urlField.setAttribute("manualmode","true");
			checkURL(urlField);
			}
		function selectElement(id,where) {
			document.getElementById('addtomenu').value=id+','+where;
			}
		</script>

	<? if(strpos($pageLayout,",title,")!==false) { ?>
		<div class="title"><?= b3_create_input("titolo","text",$kaTranslate->translate('News:Title').":<br />",(isset($copyfrom['titolo'])?$copyfrom['titolo']:''),"95%",250,'autocomplete="off" onkeyup="title2url()" onblur="titleBlur()"'); ?></div>
		<? } ?>
	<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('News:Page URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/[categoria]/',(isset($copyfrom['dir'])?$copyfrom['dir'].'-'.date("Ymd"):''),"400px",64,''); ?> <span id="dirYetExists" style="display:none;"><?= $kaTranslate->translate('News:This URL already exists!'); ?>!</span></div><br />
	<script type="text/javascript">
		document.getElementById('dir').onkeyup=dirKeyUp;
		</script>
	<br />

	<br />
	<table class="options">
	<?

	//public date
	if(strpos($pageLayout,",public,")!==false) { ?>
		<tr>
		<th><?= $kaTranslate->translate('News:Visible from date'); ?></th>
		<td><?
			if(isset($copyfrom['pubblica'])) {
				$visible_day=preg_replace('/(\d{4})[^\d](\d{1,2})[^\d](\d{1,2}) (\d{1,2})[^\d](\d{1,2})[^\d](\d{1,2})/','$3-$2-$1',$copyfrom['pubblica']);
				$visible_hour=preg_replace('/(\d{4})[^\d](\d{1,2})[^\d](\d{1,2}) (\d{1,2})[^\d](\d{1,2})[^\d](\d{1,2})/','$4:$5',$copyfrom['pubblica']);
				}
			elseif(isset($_GET['visible_day'])&&isset($_GET['visible_hour'])) {
				$visible_day=preg_replace('/(\d{4})[^\d](\d{1,2})[^\d](\d{1,2})/','$3-$2-$1',$_GET['visible_day']);
				$visible_hour=preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2',$_GET['visible_hour']);
				}
			else {
				$visible_day=date('d-m-Y');
				$visible_hour=date('H:i');
				}
			echo b3_create_input("visible_day","text","",$visible_day,"80px",250).' ';
			echo b3_create_input("visible_hour","text",$kaTranslate->translate('News:and from time')." ",$visible_hour,"40px",250);
			?>
			</td>
		</tr>
		<? }

	//starting date
	if(strpos($pageLayout,",startingdate,")!==false) { ?>
		<tr>
		<th><?= $kaTranslate->translate('News:Starting date'); ?></th>
		<td><?
			if(isset($copyfrom['starting_date'])) {
				$starting_day=preg_replace('/(\d{4})[^\d](\d{1,2})[^\d](\d{1,2}) (\d{1,2})[^\d](\d{1,2})[^\d](\d{1,2})/','$3-$2-$1',$copyfrom['starting_date']);
				$starting_hour=preg_replace('/(\d{4})[^\d](\d{1,2})[^\d](\d{1,2}) (\d{1,2})[^\d](\d{1,2})[^\d](\d{1,2})/','$4:$5',$copyfrom['starting_date']);
				}
			else {
				$starting_day=date('d-m-Y',time()+604800);
				$starting_hour=date('H:i');
				}
			echo b3_create_input("starting_day","text","",$starting_day,"80px",250).' ';
			echo b3_create_input("starting_hour","text",$kaTranslate->translate('News:and time')." ",$starting_hour,"40px",250);
			?>
			</td>
		</tr>
		<? }

	//expiration date
	if(strpos($pageLayout,",expiration,")!==false) { ?>
		<tr>
		<th><?= $kaTranslate->translate('News:Expiration date'); ?></th>
		<td><?
			if(isset($copyfrom['pubblica'])) {
				$expiration_day=preg_replace('/(\d{4})[^\d](\d{1,2})[^\d](\d{1,2}) (\d{1,2})[^\d](\d{1,2})[^\d](\d{1,2})/','$3-$2-$1',$copyfrom['scadenza']);
				$expiration_hour=preg_replace('/(\d{4})[^\d](\d{1,2})[^\d](\d{1,2}) (\d{1,2})[^\d](\d{1,2})[^\d](\d{1,2})/','$4:$5',$copyfrom['scadenza']);
				}
			else {
				$expiration_day=date('d-m-Y',time()+604800);
				$expiration_hour=date('H:i');
				}
			echo b3_create_input("expiration_day","text","",$expiration_day,"80px",250).' ';
			echo b3_create_input("expiration_hour","text",$kaTranslate->translate('News:and time')." ",$expiration_hour,"40px",250);
			?>
			</td>
		</tr>
		<? }

	//categories
	if(strpos($pageLayout,",categories,")!==false) { ?>
		<tr>
		<th><?= $kaTranslate->translate('News:Categories'); ?></th>
		<td>
			<div id="categorie">Loading...</div>
			<script type="text/javascript" src="./ajax/categorie.js"></script>
			<script type="text/javascript">k_reloadCat(<?= (isset($copyfrom['idnews'])?$copyfrom['idnews']:"0"); ?>);</script>
			</td>
		</tr>
		<? } ?>

	<?
	//copy contents from another page?
	if(isset($copyfrom['idnews'])) { ?>
		<tr>
		<th></th>
		<td><?= b3_create_input("copyfrom","checkbox",$kaTranslate->translate('News:Copy contents from the news')." <em>".$copyfrom['titolo']."</em>",$copyfrom['idnews'],"","","checked"); ?></td>
		</tr>
		<? } ?>

	<?
	//translations
	if(strpos($pageLayout,",translate,")!==false) {
		if(isset($_GET['translate'])) $page_l=$kaNews->get($_GET['translate']);
		else $page_l=array("titolo"=>"","idnews"=>"");
		?>
		<tr>
			<th><?= $kaTranslate->translate('News:Translations'); ?></th>
			<td>
			<table><tr>
				<td><?= $kaTranslate->translate('News:This page is the translated version of'); ?></td>
				<td>
					<div class="suggestionsContainer">
						<?= b3_create_input("translation","text","",$page_l['titolo'],"200px",250,'autocomplete="off"'); ?>
						<?= b3_create_input("translation_id","hidden","",$page_l['idnews']); ?>
						<script type="text/javascript">translationHandler=new kAutocomplete();translationHandler.init('-<?= $_SESSION['ll']; ?>');</script>
						</div>
					</td>
				<td>
					<small><?= $kaTranslate->translate('UI:optional'); ?></small>
					</td>
				</tr></table>
			</td>
		</tr>
		<? } ?>

	</table>
	<br /><br />

	<div style="clear:both;"></div>
	</div>

	<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('News:Create News'); ?> &gt;" class="button" /></div>
</form>
<?
include_once("../inc/foot.inc.php");
?>
