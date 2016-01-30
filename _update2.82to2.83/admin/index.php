<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_ID",".");
define("PAGE_NAME","Home:Welcome to your Content Management System");

include_once("inc/head.inc.php");

$siteboard=array();
$siteboard['active'] = false;
$siteboard['title'] = $kaImpostazioni->getVar('siteboard',1,"*");
$siteboard['text'] = $kaImpostazioni->getVar('siteboard',2,"*");
if($siteboard['title']!="" || $siteboard['text']!="") $siteboard['active']=true;
?>

<div class="subset">
	<?php 
	$status = @file_get_contents('http://download.orichalcum.it/status/status.php?v='.SW_VERSION);
	if($status!="")
	{
		?>
		<div id="lastTwit"><?= $status; ?></div>
		<?php 
	}
	?>

	<fieldset class="box">
		<legend><?= $kaTranslate->translate('Home:License'); ?></legend>
		<p><strong>Orichalcum <?= SW_VERSION; ?></strong> <?= $kaTranslate->translate('Home:Copyright'); ?> (<a href="LICENSE"><?= $kaTranslate->translate('Home:read'); ?></a>).<br />
		<br />
		&copy; 2005-2016 <a href="http://www.kalamun.org"><strong>Kalamun</strong></a></p>
	</fieldset>
	</div>

<div class="topset <?= $siteboard['active']==true ? 'siteboard' : ''?>">
	<div class="homeicons">
		<?php 
		$kaAdminMenu=new kaAdminMenu();
		$allowedSections = array('pages', 'news', 'photogallery', 'shop', 'banner', 'private', 'newsletter', 'members', 'stats');

		foreach( $kaAdminMenu->getStructure() as $main)
		{
			if(!isset($main['submenu'])) continue;
			
			foreach( $main['submenu'] as $section)
			{
				if(array_search($section['id'], $allowedSections) !== false)
				{
					?>
					<div class="homeicon">
						<a href="<?= $section['id']; ?>/index.php">
							<span class="icon"><?= $section['icon']; ?></span>
							<?= $kaTranslate->translate('Home:'.$section['title']); ?>
						</a>
					</div>
					<?php
				}
			}
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
