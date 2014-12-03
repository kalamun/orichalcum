<?php /* (c) Kalamun.org - GNU/GPL 3 */
define("PAGE_NAME","Shop:Edit a manufacturer");
include_once("../inc/head.inc.php");
include_once("./shop.lib.php");
include_once("../inc/metadata.lib.php");

$kaShop=new kaShop;
$kaMetadata=new kaMetadata;
$pageLayout=$kaImpostazioni->getVar('admin-manufacturers-layout',1,"*");

if(!isset($_GET['search'])) $_GET['search']="";


?><script type="text/javascript" src="./js/edit.js" charset="UTF-8"></script><?php 


/**************************************/
/* if no page is specified, show list */
/**************************************/
if(!isset($_GET['idsman'])) {

	/* ACTIONS */	

	//add to menu -> $_GET['addtomenu'] contains the idmenu and, comma separated, "after" or "before"
	if(isset($_GET['addtomenu'])) {
		$log="";

		require_once("../menu/menu.lib.php");
		$kaMenu=new kaMenu();

		$query="SELECT * FROM ".TABLE_SHOP_MANUFACTURERS." WHERE idsman='".$_GET['usePage']."' AND ll='".$_SESSION['ll']."' LIMIT 1";
		$results=ksql_query($query);
		if($page=ksql_fetch_array($results))
		{
			$vars['title']=$page['name'];
			$vars['dir']=$page['dir'];
			$vars['idpag']=$page['idsman'];
			$addtomenu=explode(",",$_GET['addtomenu']);
			$vars['idmenu']=$addtomenu[0];
			$vars['where']=$addtomenu[1];
			$log=$kaMenu->addElement($vars);

			if($log==false)
			{
				echo '<div id="MsgAlert">'.$kaTranslate->translate('Shop:An error occurred while inserting the manufacturer into the menu').'</div>';
				$kaLog->add("ERR",'Shop: Error while inserting in the menu the manufacturer <a href="'.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$kaImpostazioni->getVar('dir_shop_manufacturers',1).'/'.$dir.'">'.$titolo.'</a> <em>(ID: '.$id.')</em>');
			} else {
				echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:The manufacturer was successfully added to the menu').'</div>';
				$kaLog->add("INS",'Shop: Manufacturer was inserted in the menu: <a href="'.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$kaImpostazioni->getVar('dir_shop_manufacturers',1).'/'.$dir.'">'.$titolo.'</a> <em>(ID: '.$id.')</em>');
			}
		}		
	}


	/* END ACTIONS */

	
	$numberOfItems=$kaShop->countManufacturers();
	?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />

	<div class="subset">
		<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Search'); ?></legend>
		<input type="text" name="search" id="searchQ" style="width:180px;" value="<?= str_replace('"','&quot;',$_GET['search']); ?>" />
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
		
		<?php 
		/* if there are a search key, show her */
		if(isset($_GET['search'])&&$_GET['search']!="") { ?>
			<div class="box pager" style="text-align:center;">
				<?= $kaTranslate->translate('Shop:You are filtering results by').' "<strong>'.$_GET['search'].'</strong>"'; ?>
				<small><a href="?"><?= $kaTranslate->translate('remove filter'); ?></a></small>
			</div>
			<br />
			<?php 

		/* if there are more than 50 items, split into different pages */
		} elseif($numberOfItems>50) { ?>
			<div class="box pager" style="text-align:center;">
				<?php 
				$append_var=$_SERVER['QUERY_STRING'];
				foreach($_GET as $kaey => $value)
				{
					if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm"||$kaey=="p"||$kaey=="l")
					{
						$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
					}
				}

				$letters="#ABCDEFGHIJKLMNOPQRSTUWYXZ";
				for($i=0;isset($letters[$i]);$i++)
				{
					if(!isset($_GET['l'])) $_GET['l']=$letters[$i];
					?>
					<a href="?l=<?= urlencode($letters[$i]); ?>&<?= $append_var; ?>" class="<?= $_GET['l']==$letters[$i]?'selected':''; ?>">
						<?= ($letters[$i]=="!" ? $kaTranslate->translate('Shop:recently added') : $letters[$i]); ?>
					</a>
					<?php 
				}
				?>
			</div>
			<br />
			<?php 
		}
		?>

		<table class="tabella">
		
			<tr>
				<?php  if(strpos($pageLayout,",featuredimage,")!==false) { ?>
					<th>&nbsp;</th>
				<?php  } ?>

				<th><?= $kaTranslate->translate('Shop:Name'); ?></th>
			</tr>

			<tbody>
			<?php 
			$vars=array();
			$vars['conditions']="";
			if(isset($_GET['search']) && $_GET['search']!="") $vars["search"]=$_GET['search'];
			if(isset($_GET['l']) && $_GET['l']!="")
			{
				if($_GET['l']=="#") $vars['conditions'].="`name` RLIKE '^[^[A-Za-z].*'";
				else $vars['conditions'].="`name` LIKE '".ksql_real_escape_string($_GET['l'])."%'";
			}

			foreach($kaShop->getManufacturersList($vars) as $page) { ?>
				<tr>
				<?php if(strpos($pageLayout,",featuredimage,")!==false) { ?>
					<td class="featuredimage">
						<div class="container"><?php 							if($page['featuredimage']>0)
							{
								$img=$kaImages->getImage($page['featuredimage']);
								?>
								<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
								<?php 
							}
						?></div>
					</td>
				<?php } ?>
					<td>
						<a href="?idsman=<?= $page['idsman']; ?>" class="title"><?= $page['name']; ?></a><br>
						<a href="?idsman=<?= $page['idsman']; ?>" class="url"><?= $page['dir']; ?></a><br>
						<small class="actions">
							<a href="?idsman=<?= $page['idsman']; ?>"><?= $kaTranslate->translate('Shop:Edit'); ?></a> |
							<a href="manufacturers-add.php?copyfrom=<?= $page['idsman']; ?>"><?= $kaTranslate->translate('Shop:Create a copy'); ?></a> |
							<a href="javascript:selectMenuRef(<?= $page['idsman']; ?>);"><?= $kaTranslate->translate('Shop:Add to menu'); ?></a> |
							<a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$kaImpostazioni->getVar('dir_shop_manufacturers',1)."/".$page['dir']; ?>" target="_blank"><?= $kaTranslate->translate('Shop:Visit'); ?></a>
						</small>
					</td>
				</tr>
				<?php  } ?>

			</tbody>
		</table>
		</div>
	<?php  }


/*********************************************/
/* if a page is selected, show the edit form */
/*********************************************/
else {
	$_GET['idsman']=intval($_GET['idsman']);
	$page=$kaShop->getManufacturer($_GET['idsman']);

	?>
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<?php 

	/*
	if the current language is different from the page language, means that the user have clicked on the flag and are requesting the translation of this page.
	- if the page has a translation in the requested language, edit the translation
	- if the page hasn't a translated version, create a new translate page
	*/
	if($_SESSION['ll']!=$page['ll'])
	{
		if(isset($page['traduzioni'][$_SESSION['ll']])&&$page['traduzioni'][$_SESSION['ll']]!="") $url="?idsman=".$page['traduzioni'][$_SESSION['ll']];
		else $url="new.php?translate=".$_GET['idsman'];
		?>
		<div class="MsgNeutral">
			<h2><?= $kaTranslate->translate('Shop:Searching for translation'); ?></h2>
			<a href="<?= $url; ?>"><?= $kaTranslate->translate('Shop:if nothing happens, click here'); ?></a>
			<meta http-equiv="refresh" content="0;URL='<?= $url; ?>'">
			</div>
		<?php 
		die();
	}


	/* actions (update, etc..) in case of submit */
	if(isset($_POST['update']))
	{
		$vars=array();
		$vars['idsman']=$_GET['idsman'];
		if(isset($_POST['translation_id'])) $vars['translation_id']=$_POST['translation_id'];
		if(isset($_POST['idcat'])) $vars['idcat']=$_POST['idcat'];
		if(isset($_POST['name'])) $vars['name']=$_POST['name'];
		if(isset($_POST['subtitle'])) $vars['subtitle']=$_POST['subtitle'];
		if(isset($_POST['preview'])) $vars['preview']=$_POST['preview'];
		if(isset($_POST['description'])) $vars['description']=$_POST['description'];
		if(isset($_POST['dir'])) $vars['dir']=$_POST['dir'];
		if(isset($_POST['featuredimage'])) $vars['featuredimage']=$_POST['featuredimage'];
		if(isset($_POST['photogallery'])) $vars['photogallery']=$_POST['photogallery'];
		if(strpos($pageLayout,",seo,")!==false)
		{
			$vars['seo_robots']= (isset($_POST['seo_robots'])) ? $vars['seo_robots']=implode(",",$_POST['seo_robots']) : "";
			foreach($_POST as $ka=>$v)
			{
				if(substr($ka,0,4)=="seo_") $vars[$ka]=$v;
			}
		}

		$log=$kaShop->updateManufacturer($vars);

		if($log==false)
		{
			echo '<div id="MsgAlert">'.$kaTranslate->translate('Shop:An error occurred while saving').'</div>';
			$kaLog->add("ERR",'Shop:Error updating manufacturer '.b3_htmlize($_POST['name'],true,"").' <em>(ID: '.$_GET['idsman'].')</em>');
		} else {
			/* SUCCESS! */
			echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
			$kaLog->add("UPD",'Shop:Successfully update page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['name'].'</a> <em>(ID: '.$_GET['idsman'].')</em>');
		}
	}
	/* end actions */

	/* reload page contents */
	$page=$kaShop->getManufacturer($_GET['idsman']);

	?>
	<form name="update" action="?<?=  $_SERVER['QUERY_STRING']; ?>" method="post" enctype="multipart/form-data">

	<div class="subset">
		<div class="box small">
		<?= $kaTranslate->translate('Shop:Created'); ?>: <?= preg_replace('/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/','$3.$2.$1 - $4:$5',$page['created']); ?><br />
		<?= $kaTranslate->translate('Shop:Last change'); ?>: <?= preg_replace('/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/','$3.$2.$1 - $4:$5',$page['modified']); ?><br />
		</div>
		<br />

		<?php  if($kaImpostazioni->getVar('pages-commenti',1)=='s') { ?>
			<script style="text/javascript" src="<?= ADMINRELDIR; ?>js/comments.js"></script>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Comments'); ?></legend>
				<?= b3_create_input("allowcomments","checkbox",$kaTranslate->translate('Shop:This page is commentable'),'s',"","",($page['allowcomments']=='s'?'checked':'')); ?><br />
				<?= $kaTranslate->translate('Shop:This page has %s comments, %s of which still to moderate',$page['commentiTot'],($page['commentiTot']-$page['commentiOnline'])); ?>.<br />
				<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/commentsManager.php','t=<?= TABLE_SHOP_MANUFACTURERS; ?>&id=<?= $page['idsman']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Shop:Comment management'); ?></a>
				</fieldset><br />
			<?php  } ?>

		<?php  if(strpos($pageLayout,",featuredimage,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Featured Image'); ?></legend>
				<div id="featuredImageContainer"><?php 					if($page['featuredimage']>0)
					{
						$img=$kaImages->getImage($page['featuredimage']);
						?>
						<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
						<?php 
					}
					?></div>
				<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $page['featuredimage']; ?>">
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Shop:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('Shop:Choose featured image'); ?></a>
				<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" <?php  if($page['featuredimage']==0) echo 'style="display:none;"'; ?>><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
				</fieldset><br />
			<?php  } ?>
		</div>

	<div class="topset">
		<?php  if(strpos($pageLayout,",title,")!==false) {
			echo '<div class="title">'.b3_create_input("name","text",$kaTranslate->translate('Shop:Title')."<br />",b3_lmthize($page['name'],"input"),"70%",250).'</div>';
			} ?>
		<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('Shop:Page URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$kaImpostazioni->getVar('dir_shop_manufacturers',1)."/",b3_lmthize($page['dir'],"input"),"400px",64,'onkeyup="checkURL(this)"'); ?>
			<a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$kaImpostazioni->getVar('dir_shop_manufacturers',1)."/".$page['dir']; ?>" target="_blank"><?= $kaTranslate->translate('Shop:Visit'); ?></a>
			<span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span></div>
		<script type="text/javascript">
			var target=document.getElementById('dir');
			target.setAttribute("oldvalue",target.value);
			</script>
		<br />

		<?= b3_create_input("subtitle","text",$kaTranslate->translate('Shop:Subtitle')."<br />",b3_lmthize($page['subtitle'],"input"),"70%",250).'<br /><br />'; ?>
		<?= b3_create_textarea("preview",$kaTranslate->translate('Shop:Introduction')."<br />",b3_lmthize($page['preview'],"textarea"),"99%","100px",RICH_EDITOR,false,TABLE_SHOP_MANUFACTURERS,$page['idsman']).'<br />'; ?>
		<?= b3_create_textarea("description",$kaTranslate->translate('Shop:Contents')."<br />",b3_lmthize($page['description'],"textarea"),"99%","300px",RICH_EDITOR,false,TABLE_SHOP_MANUFACTURERS,$page['idsman']).'<br />'; ?>

		<?php  if(strpos($pageLayout,",photogallery,")!==false) { ?>
			<div class="box <?= trim($page['photogallery'],",")=="" ? "closed" : "opened"; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('UI:Photogallery'); ?></h2>
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?submitlabel=<?= urlencode($kaTranslate->translate('UI:Add selected images to the list')); ?>&onsubmit=kAddImagesToPhotogallery','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('UI:Add images to gallery'); ?></a>
				<div id="photogallery"></div>
				<script type="text/javascript">
					kLoadPhotogallery('<?= $page['photogallery']; ?>');
				</script>
			</div>
			<?php  } ?>

		<div class="box <?= count($page['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Document gallery'); ?></h2>
		<iframe src="<?php echo ADMINDIR; ?>inc/docgallery.inc.php?refid=docgallery&mediatable=<?= TABLE_SHOP_MANUFACTURERS; ?>&mediaid=<?= $page['idsman']; ?>" class="docsframe" id="docgallery" onload="kAutosizeIframe(this);"></iframe>
		</div>

		<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:SEO'); ?></h2>
			<table>
				<tr>
					<td><label for="seo_changefreq"><?= $kaTranslate->translate('Shop:Change frequency'); ?></label></td>
					<td><select name="seo_changefreq" id="seo_changefreq">
						<?php 
						foreach(array(""=>"","always"=>$kaTranslate->translate('Shop:Always'),"hourly"=>$kaTranslate->translate('Shop:Hourly'),"daily"=>$kaTranslate->translate('Shop:Daily'),"weekly"=>$kaTranslate->translate('Shop:Weekly'),"monthly"=>$kaTranslate->translate('Shop:Monthly'),"yearly"=>$kaTranslate->translate('Shop:Yearly'),"never"=>$kaTranslate->translate('Shop:Never')) as $ka=>$v) {
							$md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_changefreq');
							?><option value="<?= $ka; ?>" <?= ($md['value']==$ka?'selected':''); ?>><?= $v; ?></option><?php 
							} ?>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
					<td><label for="seo_title"><?= $kaTranslate->translate('Shop:Title'); ?></label></td>
					<td><input type="text" name="seo_title" id="seo_title" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_title'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td><label for="seo_priority"><?= $kaTranslate->translate('Shop:Priority'); ?></label></td>
					<td><input type="text" name="seo_priority" id="seo_priority" style="width:50px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_priority'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					<td><label for="seo_description"><?= $kaTranslate->translate('Shop:Description'); ?></label></td>
					<td><input type="text" name="seo_description" id="seo_description" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_description'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td colspan="2">
					<td><label for="seo_keywords"><?= $kaTranslate->translate('Shop:Keywords'); ?></label></td>
					<td><input type="text" name="seo_keywords" id="seo_keywords" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_keywords'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td colspan="2">
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noindex" value="noindex" <?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_robots'); if(strpos($md['value'],"noindex")!==false) { echo 'checked'; }; ?> /> <label for="seo_robots_noindex"><?= $kaTranslate->translate('Shop:No index'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_nofollow" value="nofollow" <?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_robots'); if(strpos($md['value'],"nofollow")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_nofollow"><?= $kaTranslate->translate('Shop:No follow'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noarchive" value="noarchive" <?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_robots'); if(strpos($md['value'],"noarchive")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_noarchive"><?= $kaTranslate->translate('Shop:No archive'); ?></label>
						</td>
					<td><label for="seo_canonical">Canonical URL</label></td>
					<td><input type="text" name="seo_canonical" id="seo_canonical" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_MANUFACTURERS,$page['idsman'],'seo_canonical'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				</table>
		</div>

		<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Meta-data'); ?></h2>
			<div id="divMetadata"></div>
			<script type="text/javascript">kaMetadataReload('<?= TABLE_SHOP_MANUFACTURERS; ?>',<?= $page['idsman']; ?>);</script>
			<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_SHOP_MANUFACTURERS; ?>&id=<?= $page['idsman']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Shop:Add Meta-data'); ?></a>
			</div>

		<?php  if(strpos($pageLayout,",translations,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Translations'); ?></h2>
				<table><?php 
					$translation=array();
					$translation_id=array();
					$query_l="SELECT * FROM ".TABLE_LINGUE." WHERE ll<>'".$page['ll']."' ORDER BY lingua";
					$results_l=ksql_query($query_l);
					while($page_l=ksql_fetch_array($results_l)) {
						if(!isset($page['traduzioni'][$page_l['ll']])||$page['traduzioni'][$page_l['ll']]=="") {
							$translation[$page_l['ll']]="";
							$translation_id[$page_l['ll']]="";
							}
						else {
							$tmp=$kaShop->getTitleById($page['traduzioni'][$page_l['ll']]);
							$translation[$page_l['ll']]=$tmp['name'];
							$translation_id[$page_l['ll']]=$tmp['idsman'];
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
			</div>
	</div></form>
	<?php 
	}

include_once("../inc/foot.inc.php");
