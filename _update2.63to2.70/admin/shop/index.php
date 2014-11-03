<?php 
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

<?php 
if(strpos($pageLayout,",manufacturers,")!==false) { ?>
<ul class="mainopt">
<li><a href="manufacturers-add.php"><?= $kaTranslate->translate('Shop:Create a new manufacturer'); ?></a></li>
<li><a href="manufacturers-edit.php"><?= $kaTranslate->translate('Shop:Edit a manufacturer'); ?></a></li>
<li><a href="manufacturers-delete.php"><?= $kaTranslate->translate('Shop:Delete a manufacturer'); ?></a></li>
</ul>
<?php 
}

$pageLayout=$kaImpostazioni->getVar('admin-shop-layout',1,"*");
if(strpos($pageLayout,",ordersummary,")!==false) { ?>
<ul class="mainopt">
<li><a href="orders-open.php"><?= $kaTranslate->translate('Shop:Opened orders'); ?></a></li>
<li><a href="orders-closed.php"><?= $kaTranslate->translate('Shop:Closed orders'); ?></a></li>
<li><a href="orders-canceled.php"><?= $kaTranslate->translate('Shop:Canceled orders'); ?></a></li>
</ul>
<?php 
}


include_once("../inc/foot.inc.php");
