<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Shop:Shop");
include_once("../inc/head.inc.php");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="add.php"><?= $kaTranslate->translate('Shop:Add an item'); ?></a></li>
<li><a href="edit.php"><?= $kaTranslate->translate('Shop:Manage items'); ?></a></li>
<li><a href="delete.php"><?= $kaTranslate->translate('Shop:Delete items from shop'); ?></a></li>
<li><a href="categories.php"><?= $kaTranslate->translate('Shop:Categories management'); ?></a></li>
</ul>

<?
$pageLayout=$kaImpostazioni->getVar('admin-shop-layout',1,"*");
if(strpos($pageLayout,",ordersummary,")!==false) { ?>
<ul class="mainopt">
<li><a href="orders-open.php"><?= $kaTranslate->translate('Shop:Opened orders'); ?></a></li>
<li><a href="orders-closed.php"><?= $kaTranslate->translate('Shop:Closed orders'); ?></a></li>
<li><a href="orders-canceled.php"><?= $kaTranslate->translate('Shop:Canceled orders'); ?></a></li>
</ul>
<?
}


include_once("../inc/foot.inc.php");
?>
