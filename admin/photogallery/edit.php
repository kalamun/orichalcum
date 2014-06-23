<?php
/* (c) Kalamun.org - GNU/GPL 3 - àèìòù */
define("PAGE_NAME","Modifica Galleria Fotografica");
define("PAGE_LEVEL",2);
include_once("../inc/head.inc.php");
include_once("./photogallery.lib.php");
include_once("../inc/metadata.lib.php");
$kaPhotogallery=new kaPhotogallery();

$pageLayout=$kaImpostazioni->getVar('admin-photogallery-layout',1,"*");
$kaMetadata=new kaMetadata;

if(!isset($_GET['idphg'])) {

	/* AZIONI */
	if(isset($_GET['addtomenu'])) {
		$log="";

		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE idphg='".$_GET['usePage']."' AND ll='".$_SESSION['ll']."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$titolo=$row['titolo'];
			$dir=$kaImpostazioni->getVar('dir_photogallery',1).'/'.$row['dir'];
			$id=$row['idphg'];
			$addtomenu=explode(",",$_GET['addtomenu']);
			if($addtomenu[1]=="after") {
				$query="SELECT ordine,ref,collection FROM ".TABLE_MENU." WHERE idmenu=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' LIMIT 1";
				$results=mysql_query($query);
				$row=mysql_fetch_array($results);
				$ordine=$row['ordine']+1;
				$ref=$row['ref'];
				$query="UPDATE ".TABLE_MENU." SET ordine=ordine+1 WHERE ref='".$ref."' AND ordine>='".$ordine."' AND ll='".$_SESSION['ll']."'";
				mysql_query($query);
				}
			elseif($addtomenu[1]=="inside") {
				$query="SELECT ordine,ref,collection FROM ".TABLE_MENU." WHERE ref=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' ORDER BY ordine DESC LIMIT 1";
				$results=mysql_query($query);
				$row=mysql_fetch_array($results);
				$ordine=$row['ordine']+1;
				$ref=$addtomenu[0];
				}
			elseif($addtomenu[1]=="before") {
				$query="SELECT ordine,ref,collection FROM ".TABLE_MENU." WHERE idmenu=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' LIMIT 1";
				$results=mysql_query($query);
				$row=mysql_fetch_array($results);
				$ordine=$row['ordine'];
				$ref=$row['ref'];
				$query="UPDATE ".TABLE_MENU." SET ordine=ordine+1 WHERE ref='".$ref."' AND ordine>='".$ordine."' AND ll='".$_SESSION['ll']."'";
				mysql_query($query);
				}
			$query="INSERT INTO ".TABLE_MENU." (label,url,ref,ordine,ll,collection) VALUES('".addslashes($titolo)."','".addslashes($dir)."','".$ref."','".$ordine."','".$_SESSION['ll']."','".mysql_real_escape_string($row['collection'])."')";
			if(!mysql_query($query)) $log="Problemi durante l'inserimento nel men&ugrave;";

			if($log!="") {
				echo '<div id="MsgAlert">'.$log.'</div>';
				$kaLog->add("ERR",'Errore durante l\'inserimento nel men&ugrave; della pagina <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$dir.'">'.$titolo.'</a> <em>(ID: '.$id.')</em>');
				}
			else {
				echo '<div id="MsgSuccess">Pagina inserita nel men&ugrave;</div>';
				$kaLog->add("INS",'Inserita nel men&ugrave; la pagina: <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$dir.'">'.$titolo.'</a> <em>(ID: '.$id.')</em>');
				}
			}		
		}
	/**/


	if($kaImpostazioni->getVar('photogallery-order',1)=="ordine"&&isset($_POST['idphg'])&&count($_POST['idphg']>0)) {
		$log="";
		if(!$kaPhotogallery->sort($_POST['idphg'])) $log="Errore durante il salvataggio dell'ordinamento delle gallerie";

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Errore nell\'ordinamento delle gallerie fotografiche');
			}
		else {
			$kaLog->add("UPD",'Modificato l\'ordine delle gallerie fotografiche');
			}
		}
		?>
	
	<h1><?php echo PAGE_NAME; ?></h1>
	<br />
		
	<div class="subset">
		<fieldset class="box"><legend>Cerca</legend>
		<input type="text" name="search" id="searchQ" style="width:180px;" value="<? if(isset($_GET['search'])) echo str_replace('"','&quot;',$_GET['search']); ?>" />
		<script type="text/javascript">
			function submitSearch() {
				var q=document.getElementById('searchQ').value;
				window.location="?search="+escape(q);
				}
			function searchKeyUp(e) {
			   var KeyID=(window.event)?event.keyCode:e.keyCode;
			   if(KeyID==13) submitSearch(); //invio
			   }
			document.getElementById('searchQ').onkeyup=searchKeyUp;
			
			function selectMenuRef(usePage) {
				document.getElementById('usePage').value=usePage;
				k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
				}
			function selectElement(id,where) {
				var usePage=document.getElementById('usePage').value;
				var get="";
				if(String(window.location).indexOf("search=")>-1) {
					get=String(window.location);
					get=get.replace(/.*search=/,"");
					get="search="+get.replace(/^[[^\d]*].*/,"");
					}
				var url=String(window.location).replace(/\?.*/,"");
				window.location=url+'?usePage='+usePage+'&addtomenu='+id+','+where+'&'+get;
				}
			</script>

		<? if($kaImpostazioni->getVar('photogallery-order',1)=="ordine") { ?>
		<script type="text/javascript" src="<? echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
		<script type="text/javascript">
		kDragAndDrop=new kDrago();
		kDragAndDrop.dragClass("DragZone");
		kDragAndDrop.dropClass("DragZone");
		kDragAndDrop.containerTag('TR');
		kDragAndDrop.onDrag(function (drag,target) {
			var container=drag.parentNode.childNodes;
			if(target.className!='DragZone'&&target!=drag) {
				if((parseInt(target.getAttribute("ddTop"))+target.offsetHeight/2)>kWindow.mousePos.y) target.parentNode.insertBefore(drag,target);
				else target.parentNode.insertBefore(drag,target.nextSibling);
				}
			kDragAndDrop.savePosition();
			});
		kDragAndDrop.onDrop(function (drag,target) {
			b3_openMessage('Salvataggio in corso',false);
			document.getElementById('orderby').submit();
			});
			</script>
			<? } ?>

		</div>
		
	<div class="topset">
		<input type="hidden" id="usePage" />
		<form action="" method="post" id="orderby">
		<div class="DragZone">
			<table class="tabella">
			<tr><th>Galleria</th><th>Indirizzo</th>
			<?= ($kaImpostazioni->getVar('photogallery-commenti',1)=='s'?'<th>'.$kaTranslate->translate('Photogalleries:Comments').'</th>':''); ?>
			<? if($kaImpostazioni->getVar('photogallery-order',1)=="ordine") echo '<th>Ordine</th>'; ?>
			</tr><?php
			$conditions="";
			if(isset($_GET['search'])) {
				$conditions.="titolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
				$conditions.="testo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
				$conditions.="dir LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
				}
			
			$list=$kaPhotogallery->getList($conditions);
			foreach($list as $ka=>$g) {
				echo '<tr>';
				echo '<td><h2><a href="?idphg='.$g['idphg'].'">'.$g['titolo'].'</a></h2>';
					echo '<small class="actions"><a href="?idphg='.$g['idphg'].'">'.$kaTranslate->translate('UI:Edit').'</a> | <a href="javascript:selectMenuRef('.$g['idphg'].');">'.$kaTranslate->translate('Photogalleries:Add to menu').'</a> | <a href="'.SITE_URL.'/'.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_photogallery',1)."/".$g['dir'].'">'.$kaTranslate->translate('Photogalleries:Visit').'</a></small>';
					echo '</td>';
				echo '<td class="percorso"><a href="?idphg='.$g['idphg'].'">'.$g['dir'].'</a></td>';
				echo ($kaImpostazioni->getVar('photogallery-commenti',1)=='s'?'<td class="percorso"><strong>'.$g['commentiOnline'].'</strong> / '.$g['commentiTot'].'</td>':'');
				if($kaImpostazioni->getVar('photogallery-order',1)=="ordine") echo '<td class="sposta"><input type="hidden" name="idphg[]" value="'.$g['idphg'].'" /><img src="'.ADMINRELDIR.'img/drag_v.gif" width="18" height="18" alt="Sposta" /> Sposta</td>';
				echo '</tr>';
				}
			?></table>
			</div>
			</form>
		</div>
	<?php }

else {
	$row=$kaPhotogallery->getById($_GET['idphg']);
	?>

	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<script type="text/javascript" src="js/edit.js"></script>
	
	<?

	/* AZIONI */
	if(isset($_POST['update'])) {
		$log="";
		$photogallery=$kaPhotogallery->getById($_GET['idphg']);


		/* update translation table in all involved pages (past and current) */
		if(isset($_POST['translation_id'])) {
			// translation has this format: |LL=idpag|LL=idpag|...
			$translations="";
			$_POST['translation_id'][$_SESSION['ll']]=$_GET['idphg'];
			foreach($_POST['translation_id'] as $k=>$v) {
				if($v!="") {
					$translations.=$k.'='.$v.'|';
					$kaPhotogallery->removePageFromTranslations($v);
					}
				}
			// first of all, clear translations from previous+current pages
			foreach($row['traduzioni'] as $k=>$v) {
				if($v!="") $kaPhotogallery->removePageFromTranslations($v);
				}
			// then set the new translations in the current pages
			foreach($_POST['translation_id'] as $k=>$v) {
				if($v!="") {
					$kaPhotogallery->setTranslations($v,$translations);
					}
				}
			}
		
		if(!isset($_POST['layout'])) $_POST['layout']="";

		//modifico o inserisco il record
		if(!$kaPhotogallery->update($_GET['idphg'],b3_htmlize($_POST['titolo'],false,""),b3_htmlize($_POST['testo'],false),$_POST['dir'],$_POST['template'],$_POST['layout'])) $log="Errore durante il salvataggio";
		else {
			$id=$_GET['idphg'];
			if(strpos($pageLayout,",seo,")!==false) {
				if(isset($_POST['seo_robots'])) $_POST['seo_robots']=implode(",",$_POST['seo_robots']);
				else $_POST['seo_robots']="";
				foreach($_POST as $ka=>$v) {
					if(substr($ka,0,4)=="seo_") $kaMetadata->set(TABLE_PHOTOGALLERY,$id,$ka,$v);
					}
				}
			}

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Photogallery:Errors occured while updating <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$kaImpostazioni->getVar('dir_photogallery',1)."/".$dir.'">'.b3_htmlize($_POST['titolo'],true,"").'</a> <em>(ID: '.$_GET['id'].')</em>');
			}
		else {
			/* SUCCESS! */
			// update menu with permalink (if it's different)
			if($_POST['dir']!=$photogallery['dir']||b3_htmlize($_POST['titolo'],true,"")!=$photogallery['titolo']) {
				require_once('../menu/menu.lib.php');
				$kaMenu=new kaMenu();
				foreach($kaMenu->getCollections() as $c) {
					$kaMenu->setCollection($c);
					foreach($kaMenu->getMenuElementsByUrl(array("url"=>$photogallery['dir'])) as $m) {
						//change the menu label only if the old page title is equal to the label of the menu element (so probably it wasn't manually changed)
						$m['label']==$photogallery['titolo']&&b3_htmlize($_POST['titolo'],true,"")!=$photogallery['titolo']?$newtitle=$_POST['titolo']:$newtitle=false;
						$kaMenu->updateDirAndLabel($m['idmenu'],$_POST['dir'],$newtitle);
						}
					}
				}

			echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
			$kaLog->add("UPD",'Photogallery: Changed <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$kaImpostazioni->getVar('dir_photogallery',1)."/".$_POST['dir'].'">'.$_POST['titolo'].'</a> <em>(ID: '.$id.')</em>');
			}
		}
	/* FINE AZIONI */

	?>
	<br />
	<?php
	$row=$kaPhotogallery->getById($_GET['idphg']);

	/*mi porto dietro eventuali variabili get*/
	$get="?";
	foreach($_GET as $ka=>$v) { $get.=$ka.'='.urlencode($v).'&'; }
	rtrim($get,"&");
	echo '<form action="'.$get.'" method="post" enctype="multipart/form-data">';
	?>

	<div class="subset">
		<? if($kaImpostazioni->getVar('photogallery-commenti',1)=='s') { ?>
			<script style="text/javascript" src="<?= ADMINRELDIR; ?>js/comments.js"></script>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Photogallery:Comments'); ?></legend>
				<?= $kaTranslate->translate('Photogalleries:This gallery has %d comments, %d of which still to moderate',$row['commentiTot'],($row['commentiTot']-$row['commentiOnline'])); ?>.<br />
				<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/commentsManager.php','t=<?= TABLE_PHOTOGALLERY; ?>&id=<?= $row['idphg']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Photogalleries:Comment management'); ?></a>
				</fieldset><br />
			<? } ?>

		</div>
	
	<div class="topset">
		<? if(strpos($pageLayout,",title,")!==false) {
			echo '<div class="title">'.b3_create_input("titolo","text","Titolo<br />",b3_lmthize($row['titolo'],"input"),"70%",64).'</div>';
			} ?>
		<div class="URLBox"><?= b3_create_input("dir","text","Indirizzo della pagina: ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_photogallery',1).'/',b3_lmthize($row['dir'],"input"),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span></div>
		<script type="text/javascript">
			var target=document.getElementById('dir');
			target.setAttribute("oldvalue",target.value);
			</script>
		<br />

		<? if(strpos($pageLayout,",text,")!==false) {
		echo b3_create_textarea("testo","Testo<br />",b3_lmthize($row['testo'],"textarea"),"99%","100px",RICH_EDITOR,false,TABLE_PHOTOGALLERY,$row['idphg']).'<br />';
			} ?>

		<br />

		<? if(strpos($pageLayout,",photogallery,")!==false) { ?>
			<div class="box">
			<iframe src="<?php echo ADMINDIR; ?>inc/imgallery.inc.php?refid=imgallery&mediatable=<?php echo TABLE_PHOTOGALLERY; ?>&mediaid=<?php echo $row['idphg']; ?>" class="imgframe" id="imgallery"></iframe>
			</div>
			<? } ?>
		
			<? if(strpos($pageLayout,",metadata,")!==false) {
				?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Photogalleries:Meta-data'); ?></h2>
				<div id="divMetadata"></div>
				<script type="text/javascript">kaMetadataReload('<?= TABLE_PHOTOGALLERY; ?>',<?= $row['idphg']; ?>);</script>
				<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_PHOTOGALLERY; ?>&id=<?= $row['idphg']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Photogalleries:Add Meta-data'); ?></a>
				</div><?
				} ?>

			<? if(strpos($pageLayout,",template,")!==false||strpos($pageLayout,",layout,")!==false) { ?>
				<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Photogalleries:Template'); ?></h2>
				<? if(strpos($pageLayout,",template,")!==false) { ?>
					<?
					$option=array("");
					$value=array("-default-");
					foreach($kaImpostazioni->getTemplateList() as $file) {
						$option[]=$file;
						$value[]=str_replace("_"," ",$file);
						}
					echo b3_create_select("template",$kaTranslate->translate('Photogalleries:Template')." ",$value,$option,$row['template']);
					} ?>

				<? if(strpos($pageLayout,",layout,")!==false) { ?>
					<?
					$option=array("");
					$value=array("-default-");
					foreach($kaImpostazioni->getLayoutList() as $file) {
						$option[]=$file;
						$file=str_replace("_"," ",$file);
						$file=str_replace(".php"," ",$file);
						$file=str_replace(".html"," ",$file);
						$value[]=$file;
						}
					echo b3_create_select("layout",$kaTranslate->translate('Photogalleries:Layout')." ",$value,$option,$row['layout']);
					} ?>
					</div>
				<? } ?>

			<? if(strpos($pageLayout,",traduzioni,")!==false) { ?>
				<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Traduzioni</h2>
					<table><?
						$translation=array();
						$translation_id=array();
						$query_l="SELECT * FROM ".TABLE_LINGUE." WHERE ll<>'".$row['ll']."' ORDER BY `lingua`";
						$results_l=mysql_query($query_l);
						while($page_l=mysql_fetch_array($results_l)) {
							if(!isset($row['traduzioni'][$page_l['ll']])||$row['traduzioni'][$page_l['ll']]=="") {
								$translation[$page_l['ll']]="";
								$translation_id[$page_l['ll']]="";
								}
							else {
								$tmp=$kaPhotogallery->getTitleById($row['traduzioni'][$page_l['ll']]);
								$translation[$page_l['ll']]=$tmp['titolo'];
								$translation_id[$page_l['ll']]=$tmp['idphg'];
								}
							?>
							<tr>
							<td><label for="translation['<?= $page_l['ll']; ?>']"><strong><?= $page_l['lingua']; ?></strong></label></td>
							<td><div class="suggestionsContainer">
								<?= b3_create_input("translation[".$page_l['ll']."]","text","",$translation[$page_l['ll']],"200px",250,'autocomplete="off"'); ?>
								<?= b3_create_input("translation_id[".$page_l['ll']."]","hidden","",$translation_id[$page_l['ll']]); ?>
								<img src="<?= ADMINDIR; ?>img/close.png" alt="clear" width="12" height="12" id="translation_clear<?= $page_l['ll']; ?>" class="suggestionsClear" />
								<script type="text/javascript">translation<?= $page_l['ll']; ?>Handler=new kAutocomplete();translation<?= $page_l['ll']; ?>Handler.init('<?= $page_l['ll']; ?>');</script>
								</div></td>
							</tr>
							<? } ?>
						</table>
					</div>
				<? } ?>
				
			<? if(strpos($pageLayout,",seo,")!==false) {
				?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">SEO (<em>Search Engine Optimization</em>)</h2>
				<table>
					<tr>
						<td><label for="seo_changefreq">Frequenza delle modifiche alla pagina</label></td>
						<td><select name="seo_changefreq" id="seo_changefreq">
							<?
							foreach(array(""=>"","always"=>"Sempre","hourly"=>"Ogni ora","daily"=>"Ogni giorno","weekly"=>"Ogni settimana","monthly"=>"Ogni mese","yearly"=>"Ogni anno","never"=>"Mai") as $ka=>$v) {
								$md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_changefreq');
								?><option value="<?= $ka; ?>" <?= ($md['value']==$ka?'selected':''); ?>><?= $v; ?></option><?
								} ?>
							</select>&nbsp;&nbsp;&nbsp;&nbsp;
							</td>
						<td><label for="seo_title">Titolo della pagina</label></td>
						<td><input type="text" name="seo_title" id="seo_title" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_title'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
						</tr>
					<tr>
						<td><label for="seo_priority">Priorit&agrave;</label></td>
						<td><input type="text" name="seo_priority" id="seo_priority" style="width:50px;" value="<? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_priority'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
						<td><label for="seo_description">Descrizione</label></td>
						<td><input type="text" name="seo_description" id="seo_description" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_description'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
						</tr>
					<tr>
						<td colspan="2">
						<td><label for="seo_keywords">Parole chiave</label></td>
						<td><input type="text" name="seo_keywords" id="seo_keywords" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_keywords'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
						</tr>
					<tr>
						<td colspan="2">
							<input type="checkbox" name="seo_robots[]" id="seo_robots_noindex" value="noindex" <? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_robots'); if(strpos($md['value'],"noindex")!==false) { echo 'checked'; }; ?> /> <label for="seo_robots_noindex">Non indicizzare</label>,
							<input type="checkbox" name="seo_robots[]" id="seo_robots_nofollow" value="nofollow" <? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_robots'); if(strpos($md['value'],"nofollow")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_nofollow">Non seguire</label>,
							<input type="checkbox" name="seo_robots[]" id="seo_robots_noarchive" value="noarchive" <? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_robots'); if(strpos($md['value'],"noarchive")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_noarchive">Non archiviare</label>
							</td>
						<td><label for="seo_canonical">Canonical URL</label></td>
						<td><input type="text" name="seo_canonical" id="seo_canonical" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_PHOTOGALLERY,$row['idphg'],'seo_canonical'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
						</tr>
					</table>
				</div><?
				} ?>


		<script type="text/javascript">
			var timer=null;
			var markURLfield=function(success) {
				if(success=="true") document.getElementById('dirYetExists').style.display="inline";
				else document.getElementById('dirYetExists').style.display="none";
				}
			function checkURL(field) {
				var target=document.getElementById('dir');
				if(target.value==target.getAttribute("oldvalue")) markURLfield('false');
				else {
					target.value=target.value.replace(/[^\w^\/]+/g,"-");
					if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
					t=setTimeout("b3_ajaxSend('post','ajax/checkUrl.php','url="+escape(field.value)+"',markURLfield);",500);
					}
				}
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
		<div class="submit"><input type="submit" name="update" class="button" value="Salva le modifiche" /></div>
		</div>
	</form>

<? }

include_once("../inc/foot.inc.php");
?>
