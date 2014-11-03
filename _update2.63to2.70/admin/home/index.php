<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Home:Home page");
include_once("../inc/head.inc.php");
include_once("../inc/images.lib.php");
include_once("../inc/categorie.lib.php");

$kaImages=new kaImages();
$kaCategorie=new kaCategorie();


/* ACTIONS */
if(isset($_POST['news-insert'])) {
	// insert link to main news page
	$_POST['dir']=$kaImpostazioni->getVar('dir_news',1);
	$_POST['insert']=true;
	}
elseif(isset($_POST['news-idcat'])) {
	// insert link to a news category
	$cat=$kaCategorie->get($_POST['news-idcat'],TABLE_NEWS);
	$_POST['dir']=$kaImpostazioni->getVar('dir_news',1).'/'.$cat['dir'];
	$_POST['insert']=true;
	}
elseif(isset($_POST['photogallery-insert'])) {
	// insert link to main shop page
	$_POST['dir']=$kaImpostazioni->getVar('dir_photogallery',1);
	$_POST['insert']=true;
	}
elseif(isset($_POST['shop-insert'])) {
	// insert link to main shop page
	$_POST['dir']=$kaImpostazioni->getVar('dir_shop',1);
	$_POST['insert']=true;
	}
elseif(isset($_POST['shop-idcat'])) {
	// insert link to a shop category
	$cat=$kaCategorie->get($_POST['shop-idcat'],TABLE_SHOP_ITEMS);
	$_POST['dir']=$kaImpostazioni->getVar('dir_shop',1).'/'.$cat['dir'];
	$_POST['insert']=true;
	}
elseif(isset($_POST['private-insert'])) {
	// insert link to main shop page
	$_POST['dir']=$kaImpostazioni->getVar('dir_private',1);
	$_POST['insert']=true;
	}

if(isset($_POST['insert'])) {
	$log="";
	$query="SELECT * FROM ".TABLE_CONFIG." WHERE `param`='home_page' AND `ll`='".mysql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
	$results=mysql_query($query);
	if(!mysql_fetch_array($results)) {
		$query="INSERT INTO ".TABLE_CONFIG." (`param`,`value1`,`value2`,`ll`) VALUES (`home_page`,'','','".mysql_real_escape_string($_SESSION['ll'])."')";
		mysql_query($query);
		}

	$query="UPDATE `".TABLE_CONFIG."` SET `value1`='".b3_htmlize($_POST['dir'],true,"")."' WHERE `param`='home_page' AND ll='".mysql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
	if(!mysql_query($query)) {
		$log=$kaTranslate->translate("Home:Error while setting the new home page");
		}
	else $id=mysql_insert_id();

	if($log=="") {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Home:Successfully saved").'</div>';
		$kaLog->add("INS",'Home: New home page is '.b3_htmlize($_POST['dir'],true,""));
		}
	else {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Home: Error while setting a new home page: '.b3_htmlize($_POST['dir'],true,""));
		}
	}

/***/


$query="SELECT * FROM ".TABLE_CONFIG." WHERE `param`='home_page' AND `ll`='".mysql_real_escape_string($_SESSION['ll'])."' LIMIT 1";
$results=mysql_query($query);
$row=mysql_fetch_array($results);
$dir=$row['value1'];
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<script type="text/javascript" src="js/edit.js"></script>
<br />
<div class="title">
	<?= $kaTranslate->translate('Home:Current home page is'); ?>: <u><?= $dir; ?></u> <a href="<?= SITE_URL.BASEDIR.$dir; ?>" class="smallbutton" /><?= $kaTranslate->translate('UI:View'); ?></a>
	</div>
<br />
<br />
<?= $kaTranslate->translate('Home:Choose a new home page'); ?>:
<?php  if(TRANSLATIONS) { echo '('.$kaTranslate->translate('Home:remember to do it for each language!').')'; } ?>
<br />

<div class="tab" id="tabs">
	<dl>
	<dt><a href="javascript:showTab('page')"><?= $kaTranslate->translate('Menu:Pages'); ?></a></dt>
	<dt><a href="javascript:showTab('news')"><?= $kaTranslate->translate('Menu:News'); ?></a></dt>
	<dt><a href="javascript:showTab('photogallery')"><?= $kaTranslate->translate('Menu:Photogallery'); ?></a></dt>
	<dt><a href="javascript:showTab('shop')"><?= $kaTranslate->translate('Menu:Shop'); ?></a></dt>
	<dt><a href="javascript:showTab('private')"><?= $kaTranslate->translate('Menu:Private Area'); ?></a></dt>
	<dt><a href="javascript:showTab('custom')"><?= $kaTranslate->translate('Home:Custom'); ?></a></dt>
	</dl>
	</div>

<div id="tabsContents">
	<div id="tab-page" style="display:none">
		<h2><?= $kaTranslate->translate('Home:Set the page to display as home page'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
		<table><tr>
			<td><?= $kaTranslate->translate('Home:Page')." "; ?></td>
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
			<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Home:Set as home page'); ?>" />
			</div>
		</form>
		</div>

	<div id="tab-news" style="display:none">
		<h2><?= $kaTranslate->translate('Home:Choose something about news'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="news-insert" class="button" value="<?= $kaTranslate->translate('Home:Set news as home page'); ?>" /></td>
			<td>
				<?= $kaTranslate->translate('Home:or choose a category:'); ?><br/>
				<br />
				<?php 
				foreach($kaCategorie->getList(TABLE_NEWS) as $cat) { ?>
					<input type="radio" name="news-idcat" id="news-idcat-<?= $cat['idcat']; ?>" value="<?= $cat['idcat']; ?>" <?= ($cat['ordine']==1?'checked':''); ?> /> <label for="news-idcat-<?= $cat['idcat']; ?>"><?= $cat['categoria']; ?></label><br />
					<?php  } ?>

				<br />
				<div class="submit">
					<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Home:Set selected category as home page'); ?>" />
					</div>
				</td>
			</tr></table>
		</form>
		</div>

	<div id="tab-photogallery" style="display:none">
		<h2><?= $kaTranslate->translate('Home:Choose something about photogalleries'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="photogallery-insert" class="button" value="<?= $kaTranslate->translate('Home:Set photogalleries as home page'); ?>" /></td>
			</tr></table>
		</form>
		</div>

	<div id="tab-shop" style="display:none">
		<h2><?= $kaTranslate->translate('Home:Choose something about shop'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="shop-insert" class="button" value="<?= $kaTranslate->translate('Home:Set shop as home page'); ?>" /></td>
			<td>
				<?= $kaTranslate->translate('Home:or choose a category:'); ?><br/>
				<br />
				<?php 
				foreach($kaCategorie->getList(TABLE_SHOP_ITEMS) as $cat) { ?>
					<input type="radio" name="shop-idcat" id="shop-idcat-<?= $cat['idcat']; ?>" value="<?= $cat['idcat']; ?>" <?= ($cat['ordine']==1?'checked':''); ?> /> <label for="shop-idcat-<?= $cat['idcat']; ?>"><?= $cat['categoria']; ?></label><br />
					<?php  } ?>

				<br />
				<div class="submit">
					<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Home:Set selected category as home page'); ?>" />
					</div>
				</td>
			</tr></table>
		</form>
		</div>

	<div id="tab-private" style="display:none">
		<h2><?= $kaTranslate->translate('Home:Choose something about private area'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<table width="100%"><tr>
			<td><input type="submit" name="private-insert" class="button" value="<?= $kaTranslate->translate('Home:Set private area as home page'); ?>" /></td>
			</tr></table>
		</form>
		</div>

	<div id="tab-custom" style="display:none">
		<h2><?= $kaTranslate->translate('Home:Set a custom item'); ?></h2>
		<br />
		<form action="" method="post" enctype="multipart/form-data">
			<?php 
			echo b3_create_input("dir","text",$kaTranslate->translate('Home:URL').": ","","400px",250,'onkeyup="checkURL(this)"').'<br /><br />';
			?>
		<br />
		<div class="submit">
			<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('Home:Set as home page'); ?>" />
			</div>
		</form>
		</div>
	</div>

<script type="text/javascript" src="./js/menu.js"></script>
<script type="text/javascript">
	showTab('page');
	</script>



<?php 
include_once("../inc/foot.inc.php");
