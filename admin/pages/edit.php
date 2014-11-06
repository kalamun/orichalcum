<?php /* (c) Kalamun.org - GNU/GPL 3 - àèìòù */
define("PAGE_NAME","Pages:Edit a page");
include_once("../inc/head.inc.php");
include_once("./pages.lib.php");
include_once("../inc/metadata.lib.php");
include_once("../menu/menu.lib.php");

$kaPages=new kaPages;
$kaMetadata=new kaMetadata;
$pageLayout=$kaImpostazioni->getVar('admin-page-layout',1,"*");

?><script type="text/javascript" src="./js/edit.js" charset="UTF-8"></script><?php 


/**************************************/
/* if no page is specified, show list */
/**************************************/
if(!isset($_GET['idpag'])) {

	/* ACTIONS */	

	//add to menu -> $_GET['addtomenu'] contains the idmenu and, comma separated, "after" or "before"
	if(isset($_GET['addtomenu'])) {
		$log="";

		require_once("../menu/menu.lib.php");
		$kaMenu=new kaMenu();

		$query="SELECT `idpag`,`titolo`,`dir` FROM ".TABLE_PAGINE." WHERE idpag='".$_GET['usePage']."' AND ll='".$_SESSION['ll']."' LIMIT 1";
		$results=mysql_query($query);
		if($page=mysql_fetch_array($results))
		{
			$vars['title']=$page['titolo'];
			$vars['dir']=$page['dir'];
			$vars['idpag']=$page['idpag'];
			$addtomenu=explode(",",$_GET['addtomenu']);
			$vars['idmenu']=$addtomenu[0];
			$vars['where']=$addtomenu[1];
			$log=$kaMenu->addElement($vars);

			if($log==false) {
				echo '<div id="MsgAlert">'.$kaTranslate->translate('Pages:An error occurred while inserting page into menu').'</div>';
				$kaLog->add("ERR",'Pages: Error while inserting in the menu the page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$vars['dir'].'">'.$vars['title'].'</a> <em>(ID: '.$vars['idpag'].')</em>');
				}
			else {
				echo '<div id="MsgSuccess">'.$kaTranslate->translate('Pages:Page successfully added to menu').'</div>';
				$kaLog->add("INS",'Pages: Page was inserted in the menu: <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$vars['dir'].'">'.$vars['title'].'</a> <em>(ID: '.$vars['idpag'].')</em>');
				}
			}		
		}

	//switch published/draft status
	elseif(isset($_GET['draft'])) {
		$draft=$kaPages->switchDraft($_GET['draft']);
		if($draft=='n') $log=$kaTranslate->translate('Pages:Page published! Now the whole world can visit it, good luck');
		else $log=$kaTranslate->translate('Pages:Now the page is a draft');
		echo '<div id="MsgSuccess">'.$log.'</div>';
		$kaLog->add("UPD",'Pages: the page was set as draft <em>(ID: '.$_GET['draft'].')</em>');
		}

	/* END ACTIONS */


	
	?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />

	<div class="subset">
		<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Search'); ?></legend>
		<input type="text" name="search" id="searchQ" style="width:180px;" value="<?php  if(isset($_GET['search'])) echo str_replace('"','&quot;',$_GET['search']); ?>" />
		<script type="text/javascript">
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
			document.getElementById('searchQ').onkeyup=searchKeyUp;
			</script>
		</div>
		
	<div class="topset">
		<input type="hidden" id="usePage" />
		<table class="tabella">
		
		<tr>
			<th><?= $kaTranslate->translate('Pages:Title'); ?></th>
			<th><?= $kaTranslate->translate('Pages:Page URL'); ?></th>
			</tr>

		<?php 
		$conditions="";
		if(isset($_GET['search'])) {
			$conditions.="(";
			$conditions.="titolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="sottotitolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="dir LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
			$conditions.=") AND ";
			}
		$conditions.="ll='".$_SESSION['ll']."'";
		$query="SELECT idpag,titolo,dir,riservata,allowconversions FROM ".TABLE_PAGINE." WHERE ".$conditions." ORDER BY titolo";
		$results=mysql_query($query);
		while($page=mysql_fetch_array($results)) { ?>
			<tr>
			<td>
				<h2><a href="?idpag=<?= $page['idpag']; ?>">
					<?= $page['titolo']; ?>
					<?php  if($page['riservata']=='s') echo '<small class="alert">'.$kaTranslate->translate('Pages:DRAFT').'</small>'; ?>
					<?php  if($page['allowconversions']=='1') echo '<small class="alert conversions">'.$kaTranslate->translate('Pages:CONVERSIONS').'</small>'; ?>
					</a></h2>
				<small class="actions">
					<a href="?idpag=<?= $page['idpag']; ?>"><?= $kaTranslate->translate('Pages:Edit'); ?></a> |
					<a href="?draft=<?= $page['idpag']; ?>"><?= $page['riservata']=='s'?$kaTranslate->translate('Pages:Set as public'):$kaTranslate->translate('Pages:Set as draft'); ?></a> |
					<a href="new.php?copyfrom=<?= $page['idpag']; ?>"><?= $kaTranslate->translate('Pages:Create a copy'); ?></a> |
					<a href="javascript:selectMenuRef(<?= $page['idpag']; ?>);"><?= $kaTranslate->translate('Pages:Add to menu'); ?></a> |
					<a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$page['dir']; ?>" target="_blank"><?= $kaTranslate->translate('Pages:Visit'); ?></a>
					</small>
				</td>
			<td class="percorso"><a href="?idpag=<?= $page['idpag']; ?>"><?= $page['dir']; ?></a></td>
			</tr>
			<?php  } ?>
		</table>
		</div>
	<?php  }


/*********************************************/
/* if a page is selected, show the edit form */
/*********************************************/
else {
	$_GET['idpag']=intval($_GET['idpag']);
	$page=$kaPages->get($_GET['idpag']);

	?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php 

	/*
	if the current language is different from the page language, means that the user have clicked on the flag and are requesting the translation of this page.
	- if the page has a translation in the requested language, edit the translation
	- if the page hasn't a translated version, create a new translate page
	*/
	if($_SESSION['ll']!=$page['ll']) {
		if(isset($page['traduzioni'][$_SESSION['ll']])&&$page['traduzioni'][$_SESSION['ll']]!="") $url="?idpag=".$page['traduzioni'][$_SESSION['ll']];
		else $url="new.php?translate=".$_GET['idpag'];
		?>
		<div class="MsgNeutral">
			<h2><?= $kaTranslate->translate('Pages:Searching for translation'); ?></h2>
			<a href="<?= $url; ?>"><?= $kaTranslate->translate('Pages:if nothing happens, click here'); ?></a>
			<meta http-equiv="refresh" content="0;URL='<?= $url; ?>'">
			</div>
		<?php 
		die();
		}


	/* actions (update, etc..) in case of submit */
	if(isset($_POST['update'])) {
		/* update translation table in all involved pages (past and current) */
		if(isset($_POST['translation_id']))
		{
			// translation has this format: |LL=idpag|LL=idpag|...
			$translations="";
			$_POST['translation_id'][$_SESSION['ll']]=$_GET['idpag'];
			foreach($_POST['translation_id'] as $k=>$v)
			{
				if($v!="")
				{
					$translations.=$k.'='.$v.'|';
					$kaPages->removePageFromTranslations($v);
				}
			}
			// first of all, clear translations from previous+current pages
			foreach($page['traduzioni'] as $k=>$v)
			{
				if($v!="") $kaPages->removePageFromTranslations($v);
			}
			// then set the new translations in the current pages
			foreach($_POST['translation_id'] as $k=>$v)
			{
				if($v!="")
				{
					$kaPages->setTranslations($v,$translations);
				}
			}
		}

		/* categories */
		$categories=",";
		if(isset($_POST['idcat']))
		{
			foreach($_POST['idcat'] as $idcat) { $categories.=intval($idcat).','; }
		}

		
		$vars=array();
		if(isset($_POST['titolo'])) $vars['title']=$_POST['titolo'];
		if(isset($_POST['sottotitolo'])) $vars['subtitle']=$_POST['sottotitolo'];
		if(isset($_POST['anteprima'])) $vars['preview']=$_POST['anteprima'];
		if(isset($_POST['testo'])) $vars['text']=$_POST['testo'];
		if(isset($_POST['photogallery'])) $vars['photogallery']=$_POST['photogallery'];
		if(isset($_POST['dir'])) $vars['dir']=$_POST['dir'];
		$vars['categories']=$categories;
		if(isset($_POST['template'])) $vars['template']=$_POST['template'];
		if(isset($_POST['layout'])) $vars['layout']=$_POST['layout'];
		if(isset($_POST['featuredimage'])) $vars['featuredimage']=$_POST['featuredimage'];
		if($kaImpostazioni->getVar('pages-commenti',1)=='s')
		{
			if(isset($vars['allowcomments'])) $vars['allowconversions']='s';
			else $vars['allowconversions']='n';
		}
		if(strpos($pageLayout,",conversion,")!==false)
		{
			if(isset($_POST['allowconversions'])) $vars['allowconversions']='s';
			else $vars['allowconversions']='n';
		}

		if(isset($_POST['seo_robots'])) $_POST['seo_robots']=implode(",",$_POST['seo_robots']);
		if(strpos($pageLayout,",seo,")!==false)
		{
			foreach($_POST as $k => $v)
			{
				if(substr($k,0,4) == "seo_")
				{
					$vars[$k]=$v;
				}
			}
		}
		if(isset($_POST['offline'])) $vars['offline']='s';

		$log=$kaPages->update($_GET['idpag'],$vars);
		
		if($log!=true) {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Pages:Error updating page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.b3_htmlize($_POST['titolo'],true,"").'</a> <em>(ID: '.$_GET['idpag'].')</em>');
			}
		else {
			/* SUCCESS! */
			// update menu with permalink (if it's different)
			if($_POST['dir']!=$page['dir']||b3_htmlize($_POST['titolo'],true,"")!=$page['titolo']) {
				require_once('../menu/menu.lib.php');
				$kaMenu=new kaMenu();
				foreach($kaMenu->getCollections() as $c) {
					$kaMenu->setCollection($c);
					foreach($kaMenu->getMenuElementsByUrl(array("url"=>$page['dir'])) as $m) {
						//change the menu label only if the old page title is equal to the label of the menu element (so probably it wasn't manually changed)
						$m['label']==$page['titolo']&&b3_htmlize($_POST['titolo'],true,"")!=$page['titolo']?$newtitle=$_POST['titolo']:$newtitle=false;
						$kaMenu->updateDirAndLabel($m['idmenu'],$_POST['dir'],$newtitle);
						}
					}
				}

			echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
			$kaLog->add("UPD",'Pages:Successfully update page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['titolo'].'</a> <em>(ID: '.$_GET['idpag'].')</em>');
			}
		}
	/* end actions */

	/* reload page contents */
	$page=$kaPages->get($_GET['idpag']);

	?>
	<form name="update" action="?<?=  $_SERVER['QUERY_STRING']; ?>" method="post" enctype="multipart/form-data">

	<div class="subset">
		<div class="box small">
		<?= $kaTranslate->translate('Pages:Created'); ?>: <?= preg_replace('/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/','$3.$2.$1 - $4:$5',$page['created']); ?><br />
		<?= $kaTranslate->translate('Pages:Last change'); ?>: <?= preg_replace('/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/','$3.$2.$1 - $4:$5',$page['modified']); ?><br />
		</div>
		<br />

		<?php  if(strpos($pageLayout,",categories,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Pages:Categories'); ?></h2>
				<div id="categorie"><?= $kaTranslate->translate('Pages:Please wait'); ?>...</div>
				<script type="text/javascript" src="./ajax/categorie.js"></script>
				<script type="text/javascript">k_reloadCat(<?php  echo $page['idpag']; ?>);</script>
				</div>
			<br />
			<?php  } ?>

		<?php  if($kaImpostazioni->getVar('pages-commenti',1)=='s') { ?>
			<script style="text/javascript" src="<?= ADMINRELDIR; ?>js/comments.js"></script>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Pages:Comments'); ?></legend>
				<?= b3_create_input("allowcomments","checkbox",$kaTranslate->translate('Pages:This page is commentable'),'s',"","",($page['allowcomments']=='s'?'checked':'')); ?><br />
				<?= $kaTranslate->translate('Pages:This page has %s comments, %s of which still to moderate',$page['commentiTot'],($page['commentiTot']-$page['commentiOnline'])); ?>.<br />
				<a href="javascript:k_openIframeWindow(ADMINDIR+'inc/commentsManager.php?t=<?= TABLE_PAGINE; ?>&id=<?= $page['idpag']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Pages:Comment management'); ?></a>
				</fieldset><br />
			<?php  } ?>

		<?php  if(strpos($pageLayout,",conversion,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Pages:Conversions'); ?></legend>
				<?= b3_create_input("allowconversions","checkbox",$kaTranslate->translate('Pages:Conversions are active'),'s',"","",($page['allowconversions']==1?'checked':'')); ?><br />
				<a href="javascript:k_openIframeWindow('ajax/conversionsManager.inc.php?idpag=<?= $page['idpag']; ?>','1000px','500px');" class="smallbutton"><?= $kaTranslate->translate('Pages:Conversions management'); ?></a>
				</fieldset><br />
			<?php  } ?>

		<?php  if(strpos($pageLayout,",featuredimage,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Pages:Featured Image'); ?></legend>
				<div id="featuredImageContainer"><?php 					if($page['featuredimage']>0)
					{
						$img=$kaImages->getImage($page['featuredimage']);
						?>
						<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
						<?php 
					}
					?></div>
				<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $page['featuredimage']; ?>">
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Pages:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('Pages:Choose featured image'); ?></a>
				<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" <?php  if($page['featuredimage']==0) echo 'style="display:none;"'; ?>><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
				</fieldset><br />
			<?php  } ?>
		</div>

	<div class="topset">
		<?php  if(strpos($pageLayout,",title,")!==false) {
			echo '<div class="title">'.b3_create_input("titolo","text",$kaTranslate->translate('Pages:Title')."<br />",b3_lmthize($page['titolo'],"input"),"70%",250).'</div>';
			} ?>
		<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('Pages:Page URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/",b3_lmthize($page['dir'],"input"),"400px",64,'onkeyup="checkURL(this)"'); ?>
			<a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$page['dir']; ?>" target="_blank"><?= $kaTranslate->translate('Pages:Visit'); ?></a>
			<span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span></div>
		<script type="text/javascript">
			var target=document.getElementById('dir');
			target.setAttribute("oldvalue",target.value);
			</script>
		<br />

		<?php  if(strpos($pageLayout,",subtitle,")!==false) {
			echo b3_create_input("sottotitolo","text",$kaTranslate->translate('Pages:Subtitle')."<br />",b3_lmthize($page['sottotitolo'],"input"),"70%",250).'<br /><br />';
			} ?>
		<?php  if(strpos($pageLayout,",preview,")!==false) {
			echo b3_create_textarea("anteprima",$kaTranslate->translate('Pages:Introduction')."<br />",b3_lmthize($page['anteprima'],"textarea"),"99%","100px",RICH_EDITOR,false,TABLE_PAGINE,$page['idpag']).'<br />';
			} ?>
		<?php  if(strpos($pageLayout,",text,")!==false) {
			echo b3_create_textarea("testo",$kaTranslate->translate('Pages:Contents')."<br />",b3_lmthize($page['testo'],"textarea"),"99%","300px",RICH_EDITOR,false,TABLE_PAGINE,$page['idpag']).'<br />';
			} ?>

		<?php  if(strpos($pageLayout,",photogallery,")!==false) { ?>
			<div class="box <?= trim($page['photogallery'],",")=="" ? "closed" : "opened"; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('UI:Photogallery'); ?></h2>
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?submitlabel=<?= urlencode($kaTranslate->translate('UI:Add selected images to the list')); ?>&onsubmit=kAddImagesToPhotogallery','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('UI:Add images to gallery'); ?></a>
				<div id="photogallery"></div>
				<script type="text/javascript">
					kLoadPhotogallery('<?= $page['photogallery']; ?>');
				</script>
			</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",documentgallery,")!==false) { ?>
			<div class="box <?= count($page['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Pages:Document gallery'); ?></h2>
			<iframe src="<?php echo ADMINDIR; ?>inc/docgallery.inc.php?refid=docgallery&mediatable=<?= TABLE_PAGINE; ?>&mediaid=<?= $page['idpag']; ?>" class="docsframe" id="docgallery" onload="kAutosizeIframe(this);"></iframe>
			</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",seo,")!==false) {
			?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Pages:SEO'); ?></h2>
			<table>
				<tr>
					<td><label for="seo_changefreq"><?= $kaTranslate->translate('Pages:Change frequency'); ?></label></td>
					<td><select name="seo_changefreq" id="seo_changefreq">
						<?php 
						foreach(array(""=>"","always"=>$kaTranslate->translate('Pages:Always'),"hourly"=>$kaTranslate->translate('Pages:Hourly'),"daily"=>$kaTranslate->translate('Pages:Daily'),"weekly"=>$kaTranslate->translate('Pages:Weekly'),"monthly"=>$kaTranslate->translate('Pages:Monthly'),"yearly"=>$kaTranslate->translate('Pages:Yearly'),"never"=>$kaTranslate->translate('Pages:Never')) as $ka=>$v) {
							$md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_changefreq');
							?><option value="<?= $ka; ?>" <?= ($md['value']==$ka?'selected':''); ?>><?= $v; ?></option><?php 
							} ?>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
					<td><label for="seo_title"><?= $kaTranslate->translate('Pages:Title'); ?></label></td>
					<td><input type="text" name="seo_title" id="seo_title" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_title'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td><label for="seo_priority"><?= $kaTranslate->translate('Pages:Priority'); ?></label></td>
					<td><input type="text" name="seo_priority" id="seo_priority" style="width:50px;" value="<?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_priority'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					<td><label for="seo_description"><?= $kaTranslate->translate('Pages:Description'); ?></label></td>
					<td><input type="text" name="seo_description" id="seo_description" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_description'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td colspan="2">
					<td><label for="seo_keywords"><?= $kaTranslate->translate('Pages:Keywords'); ?></label></td>
					<td><input type="text" name="seo_keywords" id="seo_keywords" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_keywords'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td colspan="2">
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noindex" value="noindex" <?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_robots'); if(strpos($md['value'],"noindex")!==false) { echo 'checked'; }; ?> /> <label for="seo_robots_noindex"><?= $kaTranslate->translate('Pages:No index'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_nofollow" value="nofollow" <?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_robots'); if(strpos($md['value'],"nofollow")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_nofollow"><?= $kaTranslate->translate('Pages:No follow'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noarchive" value="noarchive" <?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_robots'); if(strpos($md['value'],"noarchive")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_noarchive"><?= $kaTranslate->translate('Pages:No archive'); ?></label>
						</td>
					<td><label for="seo_canonical">Canonical URL</label></td>
					<td><input type="text" name="seo_canonical" id="seo_canonical" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_PAGINE,$page['idpag'],'seo_canonical'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				</table>
			</div><?php 
			} ?>

		<?php  if(strpos($pageLayout,",metadata,")!==false) {
			?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Pages:Meta-data'); ?></h2>
			<div id="divMetadata"></div>
			<script type="text/javascript">kaMetadataReload('<?= TABLE_PAGINE; ?>',<?= $page['idpag']; ?>);</script>
			<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_PAGINE; ?>&id=<?= $page['idpag']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Pages:Add Meta-data'); ?></a>
			</div><?php 
			} ?>

		<?php  if(strpos($pageLayout,",template,")!==false||strpos($pageLayout,",layout,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Pages:Template'); ?></h2>
			<?php  if(strpos($pageLayout,",template,")!==false) { ?>
				<?php 
				$option=array("");
				$value=array("-default-");
				foreach($kaImpostazioni->getTemplateList() as $file) {
					$option[]=$file;
					$value[]=str_replace("_"," ",$file);
					}
				echo b3_create_select("template",$kaTranslate->translate('Pages:Template')." ",$value,$option,$page['template']);
				} ?>

			<?php  if(strpos($pageLayout,",layout,")!==false) { ?>
				<?php 
				$option=array("");
				$value=array("-default-");
				foreach($kaImpostazioni->getLayoutList() as $file) {
					$option[]=$file;
					$file=str_replace("_"," ",$file);
					$file=str_replace(".php"," ",$file);
					$file=str_replace(".html"," ",$file);
					$value[]=$file;
					}
				echo b3_create_select("layout",$kaTranslate->translate('Pages:Layout')." ",$value,$option,$page['layout']);
				} ?>
				</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",traduzioni,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Pages:Translations'); ?></h2>
				<table><?php 
					$translation=array();
					$translation_id=array();
					$query_l="SELECT * FROM ".TABLE_LINGUE." WHERE ll<>'".$page['ll']."' ORDER BY lingua";
					$results_l=mysql_query($query_l);
					while($page_l=mysql_fetch_array($results_l)) {
						if(!isset($page['traduzioni'][$page_l['ll']])||$page['traduzioni'][$page_l['ll']]=="") {
							$translation[$page_l['ll']]="";
							$translation_id[$page_l['ll']]="";
							}
						else {
							$tmp=$kaPages->getTitleById($page['traduzioni'][$page_l['ll']]);
							$translation[$page_l['ll']]=$tmp['titolo'];
							$translation_id[$page_l['ll']]=$tmp['idpag'];
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
						<?php  } ?>
					</table>
				</div>
			<?php  } ?>

		<br />

		<div class="submit">
			<input type="submit" name="update" class="button" value="<?= $kaTranslate->translate('UI:Save'); ?>" />
			<div class="draft"><?= b3_create_input("riservata","checkbox",$kaTranslate->translate('Pages:DRAFT'),"s","","",($page['riservata']=='s'?'checked':'')); ?></div>
			</div>
	</div></form>
	<?php 
	}

include_once("../inc/foot.inc.php");
