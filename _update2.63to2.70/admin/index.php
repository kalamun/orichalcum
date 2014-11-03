<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_ID",".");
define("PAGE_NAME","Home:Welcome to your Content Management System");

include_once("inc/head.inc.php");

$siteboard=array();
$siteboard['active']=false;
$siteboard['title']=$kaImpostazioni->getVar('siteboard',1,"*");
$siteboard['text']=$kaImpostazioni->getVar('siteboard',2,"*");
if($siteboard['title']!="" || $siteboard['text']!="") $siteboard['active']=true;
?>

<div class="subset">
	<?php 
	$status=@file_get_contents('http://download.orichalcum.it/status/status.php?v='.SW_VERSION);
	if($status!="") { ?>
		<div id="lastTwit"><?= $status; ?></div>
		<?php  } ?>

	<fieldset class="box"><legend><?= $kaTranslate->translate('Home:License'); ?></legend>
		<p><strong>Orichalcum <?= SW_VERSION; ?></strong> <?= $kaTranslate->translate('Home:Copyright'); ?> (<a href="LICENSE"><?= $kaTranslate->translate('Home:read'); ?></a>).<br />
		<br />
		&copy; 2005-2014 <a href="http://www.kalamun.org"><strong>Kalamun</strong></a></p>
		</fieldset>
	</div>

<div class="topset <?= $siteboard['active']==true ? 'siteboard' : ''?>">
	<div class="homeicons">
		<?php 
		$count=array();
		if($kaUsers->canIUse('pages')) $count[]='pages';
		if($kaUsers->canIUse('news')) $count[]='news';
		if($kaUsers->canIUse('photogallery')) $count[]='photogallery';
		if($kaUsers->canIUse('shop')) $count[]='shop';
		if($kaUsers->canIUse('banner')) $count[]='banner';
		if($kaUsers->canIUse('private')) $count[]='private';
		if($kaUsers->canIUse('newsletter')) $count[]='newsletter';
		if($kaUsers->canIUse('members')) $count[]='members';
		if($kaUsers->canIUse('stats')) $count[]='stats';

		foreach($count as $section) {
			echo '<div class="homeicon"><a href="'.$section.'/index.php"><img src="img/home_'.$section.'.png" width="40" height="40" /> '.$kaTranslate->translate('Home:'.$section).'</a></div>';
			}

		?>
	</div>


	<?php 
	if($siteboard['active']==true)
	{ ?>
		<div class="sitenotes">
			<h1><?= $siteboard['title']; ?></h1>
			<?= $siteboard['text']; ?>
		</div>
	<?php  }
	?>

	<div style="clear:both;"></div>
</div>
	
<?php 
include_once("inc/foot.inc.php");
