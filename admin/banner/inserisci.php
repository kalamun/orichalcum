<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Banner:Add a new banner");
include_once("../inc/head.inc.php");
include_once("./banner.lib.php");
include_once('../inc/categorie.lib.php');
include_once('../inc/metadata.lib.php');
$kaBanner=new kaBanner();
$kaMetadata=new kaMetadata();

/* INSERT A NEW BANNER ON POST */
if(isset($_POST['insert']))
{
	$vars = array(
		"type" => $_POST['type'],
		"title" => $_POST['title'],
		"idcat" => $_POST['idcat'],
		"url" => $_POST['url'],
	);
	
	if($_POST['type']=="image")
	{
		$vars['featuredimage'] = $_POST['featuredimage'];
		$vars['description'] = $_POST['description'];
		
	} elseif($_POST['type']=="text") {
		$vars['description'] = $_POST['description'];
		
	} elseif($_POST['type']=="code") {
		$vars['description'] = $_POST['code'];

	}
	
	$log = $kaBanner->add($vars);
	
	if($log==false)
	{
		$kaLog->add("ERR",'Banner: Error uploading a new banner: "'.$_POST['title'].'"');
		echo '<div id="MsgAlert">'.$kaTranslate->translate('Banner:Error while uploading').'</div>';
	} else {
		$kaLog->add("INS",'Banner: Successfully added a new banner: "'.$_POST['title'].'" (<em>ID: '.$log.'</em>)');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Banner:Successfully uploaded').'</div>';
		echo '<meta http-equiv="refresh" content="0; url=modifica.php?idbanner='.$log.'">';
		include(ADMINRELDIR.'inc/foot.inc.php');
		die();
	}
}
/* FINE AZIONI */


/* CONTROLLO FORM */
?>
<script type="text/javascript"><!--
	function checkForm() {
		if(f.alt=='') { alert('<?= addslashes($kaTranslate->translate('Banner:Please write the title')); ?>'); f.alt.focus(); return false; }
		return true;
	}
--></script>
<?php 
/* FINE CONTROLLO FORM */

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<div class="topset">
	<form action="" method="post" onsubmit="return checkForm(f);">
	
		<div class="box">
		<?php 
		$option=array("image", "text", "code");
		$value=array($kaTranslate->translate("Banner:Image"), $kaTranslate->translate("Banner:Text"), $kaTranslate->translate("Banner:External code"));
		echo b3_create_select("type", $kaTranslate->translate("Banner:Banner type").' ', $value, $option, "");
		?></div><br>


		<div class="box">
			<?= $kaTranslate->translate('Banner:Category'); ?><br>
			<?php
			$kaCategorie=new kaCategorie();

			$i=0;
			foreach($kaCategorie->getList(TABLE_BANNER) as $cat)
			{
				$w = $kaMetadata->get(TABLE_CATEGORIE, $cat['idcat'], 'width');
				$h = $kaMetadata->get(TABLE_CATEGORIE, $cat['idcat'], 'height');

				echo b3_create_input("idcat", "radio", ' '.$cat['categoria'].' ('.$w['value'].' x '.$h['value'].')', $cat['idcat'], "", "", ($i==0 ? 'checked="checked"' : ''), true).'<br>';
				$i++;
			}
			?>
		</div><br>
		<br>
		
		<div class="title"><?= b3_create_input("title","text",$kaTranslate->translate('Banner:Title').'<br>',"", "100%"); ?></div><br>
		
		<div class="hidewhencode">
			<?= b3_create_textarea("description",$kaTranslate->translate('Banner:Short description').'<br>', '', "100%","100px",RICH_EDITOR); ?><br><br>
		</div>
		
		<div class="hidewhenimage hidewhentext">
			<?= b3_create_textarea("code",$kaTranslate->translate('Banner:Code').'<br>', '', "100%","100px",false); ?><br><br>
		</div>

		<div class="hidewhencode hidewhentext">
			<fieldset class="box" style="max-width:300px;"><legend><?= $kaTranslate->translate('News:Featured Image'); ?></legend>
				<div id="featuredImageContainer"></div>
				<input type="hidden" name="featuredimage" id="featuredimage" value="0">
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Banner:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('News:Choose banner image'); ?></a><br>
				<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" style="display:none;"><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
			</fieldset>
			<script type="text/javascript">
			var setFeaturedImage=function(ids)
			{
				var inpt=document.getElementById('featuredimage');
				if(inpt)
				{
					inpt.value=ids[0].id;
					var imgcontainer=document.getElementById('featuredImageContainer');
					imgcontainer.innerHTML='';
					var img=document.createElement('IMG');
					img.src=ids[0].dir + ids[0].thumbnail;
					imgcontainer.appendChild(img);
					var imgcontainer=document.getElementById('removeFeaturedImage').style.display='';
				}
				k_closeIframeWindow();
			}
			
			var removeFeaturedImage=function(ids)
			{
				var inpt=document.getElementById('featuredimage');
				if(inpt)
				{
					inpt.value='';
					var imgcontainer=document.getElementById('featuredImageContainer');
					imgcontainer.innerHTML='';
					var imgcontainer=document.getElementById('removeFeaturedImage').style.display='none';
				}
			}
			</script>
		</div>
		<br>

		<div class="hidewhencode">
			<?= b3_create_input("url", "text", $kaTranslate->translate('Banner:Target URL'), "http://","100%"); ?><br><br>
		</div>
		
		<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" onclick="b3_openMessage('<?= addslashes($kaTranslate->translate('Banner:Saving...')); ?>');" /></div>
	</form>
</div>

<script>
	function setType()
	{
		var type = document.getElementById('type').value;
		for(var i=0, c=document.querySelectorAll('.hidewhenimage'); c[i]; i++)
		{
			c[i].style.display = 'block';
		}
		for(var i=0, c=document.querySelectorAll('.hidewhentext'); c[i]; i++)
		{
			c[i].style.display = 'block';
		}
		for(var i=0, c=document.querySelectorAll('.hidewhencode'); c[i]; i++)
		{
			c[i].style.display = 'block';
		}
		for(var i=0, c=document.querySelectorAll('.hidewhen'+type); c[i]; i++)
		{
			c[i].style.display = 'none';
		}
	}
	kAddEvent(document.getElementById('type'), "change", setType);
	setType();
</script>

<?php 
include_once("../inc/foot.inc.php");
