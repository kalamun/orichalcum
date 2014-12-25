<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:Automatic image resize settings");
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_POST['update'])||isset($_POST['test'])) {
	$log="";

	//img_size
	$params=array("img_size","img_quality","img_resize","thumb_size","thumb_quality","thumb_resize","img_mobile");
	foreach($params as $param)
	{
		if(empty($_POST[$param.'1'])) $_POST[$param.'1']="";
		if(empty($_POST[$param.'2'])) $_POST[$param.'2']="";
		$kaImpostazioni->setParam($param,$_POST[$param.'1'],$_POST[$param.'2'],"*");
	}

	if($log=="") echo '<div id="MsgSuccess">'.$kaTranslate->translate('Setup:Well done! Successfully saved').'</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}
/* FINE AZIONI */



?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<form action="?" method="post" enctype="multipart/form-data">

<div class="subset">
	<h2><?= $kaTranslate->translate('Setup:You have %d images in your database', $GLOBALS['kaImages']->countList()); ?></h2>
	<br>
	<strong><?= $kaTranslate->translate('Setup:These settings are not retroactive'); ?></strong>.<br>
	<?= $kaTranslate->translate('Setup:If you want to apply the new settings to the entire image database, you can re-process it from here'); ?>.<br>
	<br>
	<input type="button" class="button" value="<?= $kaTranslate->translate('Setup:Re-process the image database'); ?>" onclick="kStartReprocess(<?= intval($GLOBALS['kaImages']->countList()); ?>)">
	<small><?= $kaTranslate->translate('Setup:Please note that it may take a lot of time'); ?></small><br>
	<br>
	<div id="progressBar">
		<div id="completedBar">
			<div class="percent"></div>
		</div>
	</div>
	
</div>

<div class="topset">
	<h2><?= $kaTranslate->translate('Setup:Images'); ?></h2>
	<br>
	
	<div class="resizepreview">
		<div class="center">
			<div id="img_container" class="container"></div>
			<div id="img_imageh" class="image"></div>
			<div id="img_imagev" class="image"></div>
		</div>
	</div>
	
	<?php 
	
	/* SETTINGS FOR FULL SIZE IMAGES */
	
	$resize1=$kaImpostazioni->getVar('img_resize',1,'*');
	$resize2=$kaImpostazioni->getVar('img_resize',2,'*');
	$size1=$kaImpostazioni->getVar('img_size',1,'*');
	$size2=$kaImpostazioni->getVar('img_size',2,'*');
	$mobile1=$kaImpostazioni->getVar('img_mobile',1,'*');
	$mobile2=$kaImpostazioni->getVar('img_mobile',2,'*');
	$quality=$kaImpostazioni->getVar('img_quality',1,'*');

	?>
	<h3><?= $kaTranslate->translate('Setup:Container size'); ?></h3>
	<table>
	<tr>
		<td><label for="img_size1"><?= $kaTranslate->translate('Setup:Width'); ?></label></td>
		<td><?= b3_create_input("img_size1","text","",b3_lmthize($size1,"input"),"50px",5).' px'; ?></td>
	</tr>
	<tr>
		<td><label for="img_size2"><?= $kaTranslate->translate('Setup:Height'); ?></label></td>
		<td><?= b3_create_input("img_size2","text","",b3_lmthize($size2,"input"),"50px",5).' px'; ?></td>
	</tr>
	<tr>
		<td><label for="img_quality1"><?= $kaTranslate->translate('Setup:Quality'); ?></label></td>
		<td><?= b3_create_input("img_quality1","text","",b3_lmthize($quality,"input"),"50px",5).' %'; ?></td>
	</tr>
	</table>
	<br />

	<h3><?= $kaTranslate->translate('Setup:Resize options'); ?></h3>
	<?php
	$option=array("all","bigger","smaller","none");
	$value=array(
		$kaTranslate->translate('Setup:Resize all the images'),
		$kaTranslate->translate('Setup:Resize only bigger images'),
		$kaTranslate->translate('Setup:Resize only smaller images'),
		$kaTranslate->translate('Setup:Never resize')
		);
	
	echo b3_create_select("img_resize1","",$value,$option,b3_lmthize($resize1,"input")).' ';
	
	$option=array("inside","outside","fit");
	$value=array(
		$kaTranslate->translate('Setup:Inside the container'),
		$kaTranslate->translate('Setup:Outside the container'),
		$kaTranslate->translate('Setup:Stretch image to fit the container')
		);
	echo b3_create_select("img_resize2","",$value,$option,b3_lmthize($resize2,"input")).'<br />';
	?>
	
	<br />
	<?= b3_create_input("img_mobile1","checkbox",$kaTranslate->translate("Setup:Serve a reduced version to mobile devices"),"y","","",($mobile1=="y" ? 'checked' : '')); ?>
	<span id="img_mobile2container"><?= b3_create_input("img_mobile2","text",'- '.$kaTranslate->translate("Setup:Ratio").' ',b3_lmthize($mobile2,"input"),"30px",4); ?> %</span>
	
	<br />
	<br /><br />

	<?php
	$resize1=$kaImpostazioni->getVar('thumb_resize',1,'*');
	$resize2=$kaImpostazioni->getVar('thumb_resize',2,'*');
	$size1=$kaImpostazioni->getVar('thumb_size',1,'*');
	$size2=$kaImpostazioni->getVar('thumb_size',2,'*');
	$quality=$kaImpostazioni->getVar('thumb_quality',1,'*');
	?>
	
	<div class="clearBoth"></div>
	<h2><?= $kaTranslate->translate('Setup:Thumbnails'); ?></h2>
	<br>
	<div class="resizepreview">
		<div class="center">
			<div id="thumb_container" class="container"></div>
			<div id="thumb_imageh" class="image"></div>
			<div id="thumb_imagev" class="image"></div>
		</div>
	</div>

	<h3><?= $kaTranslate->translate('Setup:Container size'); ?></h3>
	<table>
	<tr>
		<td><label for="thumb_size1"><?= $kaTranslate->translate('Setup:Width'); ?></label></td>
		<td><?= b3_create_input("thumb_size1","text","",b3_lmthize($size1,"input"),"50px",5).' px'; ?></td>
	</tr>
	<tr>
		<td><label for="thumb_size2"><?= $kaTranslate->translate('Setup:Height'); ?></label></td>
		<td><?= b3_create_input("thumb_size2","text","",b3_lmthize($size2,"input"),"50px",5).' px'; ?></td>
	</tr>
	<tr>
		<td><label for="thumb_quality1"><?= $kaTranslate->translate('Setup:Quality'); ?></label></td>
		<td><?= b3_create_input("thumb_quality1","text","",b3_lmthize($quality,"input"),"50px",5).' %'; ?></td>
	</tr>
	</table>
	<br />

	<h3><?= $kaTranslate->translate('Setup:Resize options'); ?></h3>
	<?php
	$option=array("inside","outside","fit");
	$value=array(
		$kaTranslate->translate('Setup:Inside the container'),
		$kaTranslate->translate('Setup:Outside the container'),
		$kaTranslate->translate('Setup:Stretch image to fit the container')
		);
	echo b3_create_select("thumb_resize2","",$value,$option,b3_lmthize($resize2,"input")).'<br />';
	?>
	<br /><br />

	<div class="clearBoth"></div>

</div>



	<div class="submit">
		<input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button">
	</div>

	<script type="text/javascript" src="js/images.js"></script>

</form>
</div>

<?php 

include_once("../inc/foot.inc.php");
