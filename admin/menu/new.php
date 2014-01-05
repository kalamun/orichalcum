<?
/* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['collection'])) $_GET['collection']="";

define("PAGE_NAME","Menu:Add a new element");
include_once("../inc/head.inc.php");
include_once("../inc/images.lib.php");
include_once("../inc/categorie.lib.php");
include_once("./menu.lib.php");

$kaMenu=new kaMenu();
$kaImages=new kaImages();
$kaCategorie=new kaCategorie();

$kaMenu->setCollection($_GET['collection']);

/* ACTIONS */
if(isset($_POST['news-insert'])) {
	// insert link to main news page
	$_POST['title']=$kaTranslate->translate('Menu:News');
	$_POST['dir']=$kaImpostazioni->getVar('dir_news',1);
	$_POST['insert']=true;
	}
elseif(isset($_POST['news-idcat'])) {
	// insert link to a news category
	$cat=$kaCategorie->get($_POST['news-idcat'],TABLE_NEWS);
	$_POST['title']=$cat['categoria'];
	$_POST['dir']=$kaImpostazioni->getVar('dir_news',1).'/'.$cat['dir'];
	$_POST['insert']=true;
	}
elseif(isset($_POST['photogallery-insert'])) {
	// insert link to main shop page
	$_POST['title']=$kaTranslate->translate('Menu:Photogallery');
	$_POST['dir']=$kaImpostazioni->getVar('dir_photogallery',1);
	$_POST['insert']=true;
	}
elseif(isset($_POST['shop-insert'])) {
	// insert link to main shop page
	$_POST['title']=$kaTranslate->translate('Menu:Shop');
	$_POST['dir']=$kaImpostazioni->getVar('dir_shop',1);
	$_POST['insert']=true;
	}
elseif(isset($_POST['shop-idcat'])) {
	// insert link to a shop category
	$cat=$kaCategorie->get($_POST['shop-idcat'],TABLE_SHOP_ITEMS);
	$_POST['title']=$cat['categoria'];
	$_POST['dir']=$kaImpostazioni->getVar('dir_shop',1).'/'.$cat['dir'];
	$_POST['insert']=true;
	}
elseif(isset($_POST['private-insert'])) {
	// insert link to main shop page
	$_POST['title']=$kaTranslate->translate('Menu:Private Area');
	$_POST['dir']=$kaImpostazioni->getVar('dir_private',1);
	$_POST['insert']=true;
	}


if(isset($_POST['insert'])) {
	$log="";
	$query="SELECT `ordine` FROM `".TABLE_MENU."` WHERE `collection`='".mysql_real_escape_string($_GET['collection'])."' AND `ll`='".$_SESSION['ll']."' AND `ref`='0' ORDER BY `ordine` DESC LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	$ordine=$row['ordine']+1;

	$query="INSERT INTO `".TABLE_MENU."` (`label`,`url`,`ref`,`ll`,`ordine`,`collection`) VALUES('".b3_htmlize($_POST['title'],true,"")."','".b3_htmlize($_POST['dir'],true,"")."','0','".$_SESSION['ll']."','".$ordine."','".mysql_real_escape_string($_GET['collection'])."')";
	if(!mysql_query($query)) {
		$log=$kaTranslate->translate("Menu:Error occurred while saving item");
		}
	else $id=mysql_insert_id();

	if($log=="") {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Menu:Successfully saved").'</div>';
		$kaLog->add("INS",'Menu: Item <em>'.b3_htmlize($_POST['title'],true,"").'</em> (DIR: '.b3_htmlize($_POST['dir'],true,"").') successfully inserted');
		echo '<meta http-equiv="refresh" content="0; url=index.php?collection='.urlencode($_GET['collection']).'">';
		}
	else {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Menu: Error inserting item <em>'.b3_htmlize($_POST['title'],true,"").'</em> (DIR: '.b3_htmlize($_POST['dir'],true,"").')');
		}
	}

/***/

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<script type="text/javascript" src="js/edit.js"></script>
<br />
	<?php /* COLLECTIONS */ ?>
	<div class="tab"><dl>
		<?php
		foreach($kaMenu->getCollections() as $c) { ?>
			<dt><a href="?collection=<?= urlencode($c); ?>" class="<?= ($_GET['collection']==$c?'sel':''); ?>"><?= $c==""?$kaTranslate->translate('Menu:Main menu'):$c; ?></a></dt>
			<?php } ?>
		</dl></div>
	<br />

<div class="tab" id="tabs">
	<dl>
	<dt><a href="javascript:showTab('page')"><?= $kaTranslate->translate('Menu:Page'); ?></a></dt>
	<dt><a href="javascript:showTab('news')"><?= $kaTranslate->translate('Menu:News'); ?></a></dt>
	<dt><a href="javascript:showTab('photogallery')"><?= $kaTranslate->translate('Menu:Photogallery'); ?></a></dt>
	<dt><a href="javascript:showTab('shop')"><?= $kaTranslate->translate('Menu:Shop'); ?></a></dt>
	<dt><a href="javascript:showTab('private')"><?= $kaTranslate->translate('Menu:Private Area'); ?></a></dt>
	<dt><a href="javascript:showTab('custom')"><?= $kaTranslate->translate('Menu:Custom'); ?></a></dt>
	</dl>
	</div>

<div id="tabsContents">
	<div id="tab-page" style="display:none">
		<h2><?= $kaTranslate->translate('Menu:Add a page'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
		<table><tr>
			<td><?= $kaTranslate->translate('Menu:Page')." "; ?></td>
			<td><div class="suggestionsContainer">
				<?= b3_create_input("title","text","","","400px",250,'autocomplete="off"',true); ?>
				<?= b3_create_input("dir","hidden","","","","","",true); ?>
				<script type="text/javascript">
					pageHandler=new kAutocomplete();
					pageHandler.init('<?= $_SESSION['ll']; ?>',document.getElementsByTagName('input')[document.getElementsByTagName('input').length-2].id,document.getElementsByTagName('input')[document.getElementsByTagName('input').length-1].id);
					</script>
				</div>
				</td>
			</tr>
			</table>
		<br />
		<div class="submit">
			<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Menu:Add to menu'); ?>" />
			</div>
		</form>
		</div>

	<div id="tab-news" style="display:none">
		<h2><?= $kaTranslate->translate('Menu:Add something about news'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="news-insert" class="button" value="<?= $kaTranslate->translate('Menu:Add link to the main news page'); ?>" /></td>
			<td>
				<?= $kaTranslate->translate('Menu:or choose a category:'); ?><br/>
				<br />
				<?
				foreach($kaCategorie->getList(TABLE_NEWS) as $cat) { ?>
					<input type="radio" name="news-idcat" id="news-idcat-<?= $cat['idcat']; ?>" value="<?= $cat['idcat']; ?>" <?= ($cat['ordine']==1?'checked':''); ?> /> <label for="news-idcat-<?= $cat['idcat']; ?>"><?= $cat['categoria']; ?></label><br />
					<? } ?>

				<br />
				<div class="submit">
					<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Menu:Add link to the selected category'); ?>" />
					</div>
				</td>
			</tr></table>
		</form>
		</div>

	<div id="tab-photogallery" style="display:none">
		<h2><?= $kaTranslate->translate('Menu:Add something about photogalleries'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="photogallery-insert" class="button" value="<?= $kaTranslate->translate('Menu:Add link to the main photogallery page'); ?>" /></td>
			</tr></table>
		</form>
		</div>

	<div id="tab-shop" style="display:none">
		<h2><?= $kaTranslate->translate('Menu:Add something about shop'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="shop-insert" class="button" value="<?= $kaTranslate->translate('Menu:Add link to the main shop page'); ?>" /></td>
			<td>
				<?= $kaTranslate->translate('Menu:or choose a category:'); ?><br/>
				<br />
				<?
				foreach($kaCategorie->getList(TABLE_SHOP_ITEMS) as $cat) { ?>
					<input type="radio" name="shop-idcat" id="shop-idcat-<?= $cat['idcat']; ?>" value="<?= $cat['idcat']; ?>" <?= ($cat['ordine']==1?'checked':''); ?> /> <label for="shop-idcat-<?= $cat['idcat']; ?>"><?= $cat['categoria']; ?></label><br />
					<? } ?>

				<br />
				<div class="submit">
					<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Menu:Add link to the selected category'); ?>" />
					</div>
				</td>
			</tr></table>
		</form>
		</div>

	<div id="tab-private" style="display:none">
		<h2><?= $kaTranslate->translate('Menu:Add something about private area'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="private-insert" class="button" value="<?= $kaTranslate->translate('Menu:Add link to the private area'); ?>" /></td>
			</tr></table>
		</form>
		</div>

	<div id="tab-custom" style="display:none">
		<h2><?= $kaTranslate->translate('Menu:Add custom item'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<?
			echo b3_create_input("title","text",$kaTranslate->translate('Menu:Title').": ","","300px",64).'<br /><br />';
			echo b3_create_input("dir","text",$kaTranslate->translate('Menu:URL').": ","","400px",250,'onkeyup="checkURL(this)"').'<br /><br />';
			?>
		<br />
		<div class="submit">
			<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Menu:Add to menu'); ?>" />
			</div>
		</form>
		</div>
	</div>

<script type="text/javascript" src="./js/menu.js"></script>
<script type="text/javascript">
	showTab('page');
	</script>
<?	
include_once("../inc/foot.inc.php");
?>
