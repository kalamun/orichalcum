<?php /* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['id'])) die('Invalid ID');

require_once("main.lib.php");
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("x-frame-options"=>"", "check-permissions"=>false) );

define("PAGE_NAME",$kaTranslate->translate('Uploads:Loading'));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $_SESSION['ll']; ?>" lang="<?= $_SESSION['ll']; ?>">
<head>
<title><?= ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/uploadsmanager.css?<?= SW_VERSION; ?>" type="text/css" />

<script type="text/javascript">
	var ADMINDIR='<?= str_replace("'","\'",ADMINDIR); ?>';
	</script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/dictionary.js.php?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/uploadsManager.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
</head>

<body>

<script type="text/javascript">
	var closeModal=true;
	if(window.parent.kUploads) closeModal=false;
</script>


<?php 

/* parse the id */

$filetype=substr($_GET['id'],0,3);
if($filetype=="thu") $filetype=substr($_GET['id'],0,5);
if($filetype!="img" && $filetype!="thumb") die('Unsupported file type');

$id=intval(substr($_GET['id'],strlen($filetype)));
if($id==0) die('Invalid ID');


if($filetype=='img' || $filetype=='thumb')
{ ?>

	<div class="frameheader">
		<a id="closeModal" href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow" style="display:none;"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<script type="text/javascript">
			if(closeModal) document.getElementById('closeModal').style.display='block';
		</script>

		<div class="smenu sel">
			<ul>
			<?php 
			$menu=array();
			$menu['properties']=$kaTranslate->translate('Img:Picture Properties');
			$menu['files']=$kaTranslate->translate('Img:Files');
			$menu['istances']=$kaTranslate->translate('Img:Usage');
			$menu['fullsize']=$kaTranslate->translate('Img:Watch it');
			$menu['delete']=$kaTranslate->translate('Img:Delete');
			if(!isset($_GET['action'])) $_GET['action']='properties';
			foreach($menu as $ka=>$m) {
				echo '<li><a href="?id='.$_GET['id'].'&action='.$ka.'" class="'.($_GET['action']==$ka?'sel':'').'">'.$m.'</a></li>';
				}
			?>
			</ul>
		</div>
	</div>

	<div class="padding">
		<?php 
		/* EDIT CAPTION */
		if($_GET['action']=="properties")
		{
			if(isset($_POST['save']))
			{
				$log="";
				$img=$kaImages->getImage($id);

				if($kaImages->updateAlt($id,$_POST['alt'])==false) $log.=$kaTranslate->translate('Img:An error occurred while saving');
			
				if($log=="")
				{
					$kaLog->add("UPD","Images: The properties of the image ".$img['filename']." (<em>ID: ".$img['idimg']."</em>) was changed.");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Image successfully updated'); ?></div>
					<script type="text/javascript">
						window.parent.kUploads.reloadImage(<?= $id; ?>);
					</script>
					<?php 
				} else {
					$kaLog->add("ERR","Images: An error occurred while changing the image ".$img['filename']." (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
				}
			}
			?>

			<form action="" method="post" enctype="multipart/form-data">
				<?php  $img=$kaImages->getImage($id); ?>
		
				<table style="margin:10px auto;">
					<tr>
						<td colspan="2" align="center">
							<img src="<?= BASEDIR.$img['thumb']['url']; ?>" height="100" alt="" />
						</td>
					</tr><tr>
						<td style="text-align:right"><label for="alt"><?= $kaTranslate->translate('Img:Caption'); ?></label></td>
						<td><textarea name="alt" id="alt" style="width:400px;height:100px;"><?= b3_lmthize($img['alt'],"textarea"); ?></textarea></td>
					</tr>
				</table>
				<br />
				
				<div class="note"><?= $kaTranslate->translate('Img:Warning: changes will be applied to all the instances of this picture'); ?></div>
				<div class="submit">
					<input type="submit" name="save" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
					<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallbutton" onclick="closeModal ? window.parent.k_closeIframeWindow() : window.parent.kUploads.closeEditDialog();">
				</div>
			</form>
			<?php 
		
		/* EDIT FILES */
		} elseif($_GET['action']=="files") {
		
			// upload a new image
			if(isset($_POST['saveimg']))
			{
				$log="";
				$img=$kaImages->getImage($id);
				isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
				if(!isset($_POST['imgwidth'])) $_POST['imgwidth']=0;
				if(!isset($_POST['imgheight'])) $_POST['imgheight']=0;
				$idimg=$kaImages->updateImage($img['idimg'],$_FILES['img']['tmp_name'],$_FILES['img']['name'],$_POST['autoresize'],$_POST['imgwidth'],$_POST['imgheight']);
			
				if($log=="")
				{
					$kaLog->add("UPD","Images: Replaced the image ".$img['filename']." with ".$_FILES['img']['name']." (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Image successfully updated'); ?></div>
					<script type="text/javascript">
						if(!closeModal) window.parent.kUploads.reloadImage(<?= $id; ?>);
					</script>
					<?php 
				} else {
					$kaLog->add("ERR","Images: Error replacing ".$img['filename']." (<em>ID: ".$img['idimg']."</em>) with a new file");
					echo '<div class="alert">'.$log.'</div>';
				}

			// upload a new thumb
			} elseif(isset($_POST['savethumb'])) {
				$log="";
				isset($_POST['autoresizethumb'])?$_POST['autoresizethumb']=true:$_POST['autoresizethumb']=false;
				$img=$kaImages->getImage($id);
				$idimg=$kaImages->setThumb($img['idimg'],$_FILES['thumbnail']['tmp_name'],$_FILES['thumbnail']['name'],$_POST['autoresizethumb']);
				if($log=="")
				{
					$kaLog->add("UPD","Images: Replaced the thumbnail ".$img['thumb']['filename']." with ".$_FILES['thumbnail']['name']." (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Thumbnail successfully updated'); ?></div>
					<script type="text/javascript">
						if(!closeModal) window.parent.kUploads.reloadImage(<?= $id; ?>);
					</script>
					<?php 
				} else {
					$kaLog->add("ERR","Images: Error replacing the thumbnail ".$img['thumb']['filename']." (<em>ID: ".$img['idimg']."</em>) with a new one");
					echo '<div class="alert">'.$log.'</div>';
				}

			// import hotlink
			} elseif(isset($_POST['hotlinktoimg'])) {
				/* IMPORTA HOTLINK */
				$log="";
				//controllo l'esistenza/raggiungibilità dell'immagine
				$file_headers=@get_headers($_POST['image']);
				if(!$file_headers) $log=$kaTranslate->translate('Img:Invalid URL'); 
				else if($file_headers[0]=='HTTP/1.1 404 Not Found') $log=$kaTranslate->translate('Img:Image not found');
				else {
					$img=$kaImages->getImage($id);
					isset($_POST['autoresize'])?$_POST['autoresize']=true:$_POST['autoresize']=false;
					if(!isset($_POST['imgwidth'])) $_POST['imgwidth']=0;
					if(!isset($_POST['imgheight'])) $_POST['imgheight']=0;
					$idimg=$kaImages->updateImage($img['idimg'],$_POST['img'],basename($_POST['img']),$_POST['autoresize'],$_POST['imgwidth'],$_POST['imgheight']);
					if($idimg==false) $log.=$kaTranslate->translate('Img:Error while importing')." ".$img['url']."";
				}

				if($log=="") {
					$kaLog->add("UPD","Images: Imported the hotlink ".$_POST['img']." (<em>ID: ".$img['idimg']."</em>)");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Image successfully uploaded'); ?></div><br />
					<script type="text/javascript">
						if(!closeModal) window.parent.kUploads.reloadImage(<?= $id; ?>);
					</script>
					<?php  }
				else {
					$kaLog->add("ERR","Images: Error thile importing the hotlink ".$_POST['img']." (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div><br />';
					}
			
			// save hotlink
			} elseif(isset($_POST['save'])) {
				$log="";
				$img=$kaImage->getImage($id);
				if(!$kaImage->setHotlink($id,$_POST['img'])) $log=$kaTranslate->translate('Error while saving the hotlink');
				if($log=="")
				{
					$kaLog->add("UPD","Images: Updated hotlink ".$img['hotlink']." (<em>ID: ".$img['idmedia']."</em>)");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Hotlink successfully updated'); ?></div>
					<script type="text/javascript">
						if(!closeModal) window.parent.kUploads.reloadImage(<?= $id; ?>);
					</script>
					<?php 
				} else {
					$kaLog->add("ERR","Images: Error while saving hotlink ".$img['hotlink']." (<em>ID: ".$img['idmedia']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
				}
			}

			?>
			<form action="" method="post" enctype="multipart/form-data">
				<?php  $img=$kaImages->getImage($id); ?>
				<table style="margin:10px auto;">
				<tr><td align="center"><h2>Immagine</h2><img src="<?= ($img['hotlink']==false?BASEDIR:'').$img['url']; ?>" height="100" alt="" /></td>
					<td style="vertical-align:middle;"><label ref="img"><?= $kaTranslate->translate('UI:Change'); ?></label><br /><?php 
						if($img['hotlink']==false) { ?><input name="img" type="file" id="img" /> <input name="saveimg" type="submit" value="<?= $kaTranslate->translate('Img:Upload picture'); ?>" class="smallbutton" /><?php  }
						else { ?><input type="text" name="img" value="<?= str_replace('"','&quot;',$img['url']); ?>" style="width:300px;"> <input name="save" type="submit" value="<?= $kaTranslate->translate('Img:Update hotlink'); ?>" class="smallbutton" /> <input name="hotlinktoimg" type="submit" value="<?= $kaTranslate->translate('Img:Import to your website'); ?>" class="smallbutton" /><?php  }
						?><br />
						<input type="checkbox" name="autoresize" id="autoresize" value="1" checked="checked" onchange="this.checked?document.getElementById('manualresize').style.display='none':document.getElementById('manualresize').style.display='block';" /> <label for="autoresize"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
						<div id="manualresize" style="display:none;"><label for="imgwidth"><?= $kaTranslate->translate('Img:Width'); ?></label> <input type="text" name="imgwidth" id="imgwidth" value="" style="width:50px;" />px <label for="imgheight"><?= $kaTranslate->translate('Img:Height'); ?></label> <input type="text" name="imgheight" id="imgheight" value="" style="width:50px;" />px</div><br />
					</td>
				</tr><tr>
					<td align="center"><h2><?= $kaTranslate->translate('Img:Thumbnail'); ?></h2><img src="<?= BASEDIR.$img['thumb']['url']; ?>" height="100" alt="" /></td>
					<td style="vertical-align:middle;">
						<label ref="thumbnail"><?= $kaTranslate->translate('UI:Change'); ?></label><br /><input name="thumbnail" type="file" id="thumbnail" /> <input name="savethumb" type="submit" value="<?= $kaTranslate->translate('Img:Upload thumbnail'); ?>" class="smallbutton" /><br />
						<input type="checkbox" name="autoresizethumb" id="autoresizethumb" value="1" checked="checked" /> <label for="autoresizethumb"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
					</td>
				</tr>
				</table><br />
				<div class="note"><?= $kaTranslate->translate('Img:Warning: changes will be applied to all the instances of this picture'); ?></div>
				<div class="submit">
					<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallbutton" onclick="closeModal ? window.parent.k_closeIframeWindow() : window.parent.kUploads.closeEditDialog();">
				</div>
			</form>
			<?php  }

		elseif($_GET['action']=="istances") {
			?><h2><?= $kaTranslate->translate('Img:Usage'); ?></h2><br /><?php 
			
			?>
			<table class="tabella" style="margin:0 auto;">
				<thead>
					<tr>
						<th>Context</th>
						<th>ID</th>
						<th>Title</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach($kaImages->usage($id) as $u)
					{ ?>
						<tr>
							<td class="small"><?= $u['descr']; ?></td>
							<td class="small"><?= $u['id']; ?></td>
							<td><?= isset($u['titolo'])?$u['titolo']:'<em>Non disponibile</em>'; ?></td>
						</tr>
						<?php 
					} ?>
				</tbody>
			</table>

			<?php  }

		elseif($_GET['action']=="fullsize") {
			$img=$kaImages->getImage($id);
			?>
			<img src="<?= ($img['hotlink']==false?BASEDIR:'').$img['url']; ?>" width="<?= $img['width']; ?>" height="<?= $img['height']; ?>" alt="" />
			<?php  }

		elseif($_GET['action']=="delete") {
			if(isset($_POST['delete'])) {
				$log="";
				$img=$kaImages->getImage($id);
				if(!$kaImages->delete($id)) $log.=$kaTranslate->translate("Img:An error occurred while deleting the image");
			
				if($log=="") {
					$kaLog->add("DEL","Images: Image ".$img['filename']." (<em>ID: ".$img['idimg']."</em>) successfully deleted");
					?>
					<div class="success"><?= $kaTranslate->translate('Img:Image successfully deleted'); ?></div>
					<?php  if(isset($_GET['forcerefresh']) && $_GET['forcerefresh']==true) { ?>
						<script type="text/javascript">
							window.parent.location.reload();
							</script>
						<?php  }
					else { ?>
						<script type="text/javascript">
							window.parent.b3_openMessage('<?= addslashes($kaTranslate->translate('Img:Image successfully deleted')); ?>',true);
							window.parent.k_closeIframeWindow();
							</script>
						<?php  } ?>
					<?php  }
				else {
					$kaLog->add("ERR","Images: Error while deleting the image ".$img['filename']." (<em>ID: ".$img['idimg']."</em>)");
					echo '<div class="alert">'.$log.'</div>';
					}
				}
			else {
				?>
				<form action="" method="post" enctype="multipart/form-data">
					<?php  $img=$kaImages->getImage($id); ?>
					<table style="margin:10px auto;">
					<tr><td colspan="2" align="center"><img src="<?= BASEDIR.$img['thumb']['url']; ?>" height="100" alt="" /><br /><br />
					<?= $kaTranslate->translate('Img:You are deleting this image from server'); ?>.<br>
					<strong><?= $kaTranslate->translate('Img:No recovery will be possible after deletion'); ?></strong>.<br>
					<?= $kaTranslate->translate('Img:Are you sure that it is what you want?'); ?><br>
					</td></tr>
					</table><br />
					
					<div class="note"><?= $kaTranslate->translate('Img:Warning: changes will be applied to all the instances of this picture'); ?></div>
					<div class="submit">
						<input type="submit" name="delete" value="<?= $kaTranslate->translate('UI:Delete'); ?>" class="button" />
						<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallbutton" onclick="closeModal ? window.parent.k_closeIframeWindow() : window.parent.kUploads.closeEditDialog();">
					</div>
					</form>
				<?php  }
			}
		?>
	</div>


<?php  }

?>

</body>
</html>
