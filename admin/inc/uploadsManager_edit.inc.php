<?php /* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['id'])) die('Invalid ID');

require_once("main.lib.php");
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("x-frame-options"=>"", "check-permissions"=>false) );

define("PAGE_NAME",$kaTranslate->translate('Uploads:Edit'));
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
elseif($filetype=="med") $filetype=substr($_GET['id'],0,5);

$id=intval(substr($_GET['id'],strlen($filetype)));
if($id==0) die('Invalid ID');

$img=$kaImages->getImage($id);
if(empty($img['idimg'])) die('The requested image is not defined in repository');

/* file type */
$isImage = false;
$isMedia = false;
$isDocument = false;
if($img['filetype'] == 1) $isImage = true;
elseif($img['filetype'] == 2) $isMedia = true;
elseif($img['filetype'] == 3) $isDocument = true;

?>

<div class="frameheader">
	<a id="closeModal" href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow" style="display:none;"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	<script type="text/javascript">
		if(closeModal) document.getElementById('closeModal').style.display='block';
	</script>

	<div class="smenu sel">
		<ul>
		<?php 
		$menu=array();
		$menu['properties'] = $kaTranslate->translate('Img:Properties');
		$menu['files'] = $kaTranslate->translate('Img:Files');
		$menu['istances'] = $kaTranslate->translate('Img:Usage');
		$menu['fullsize'] = $kaTranslate->translate('Img:Watch it');
		$menu['delete'] = $kaTranslate->translate('Img:Delete');
		
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
	/**************/
	/* PROPERTIES */
	/**************/
	if($_GET['action']=="properties")
	{
		if(isset($_POST['save']))
		{
			$log="";
			$img=$kaImages->getImage($id);
			$metadata = $img['metadata'];
			
			// duration
			if(isset($_POST['duration'])) $metadata['duration'] = intval($_POST['duration']);
			
			// dimensions
			if(isset($_POST['width'])) $metadata['width'] = intval($_POST['width']);
			if(isset($_POST['height'])) $metadata['height'] = intval($_POST['height']);

			// subtitles
			$metadata['subtitles'] = array();
			
			// subtitles already uploaded
			if(!empty($_POST['srtUploadedLanguage']))
			{
				foreach($_POST['srtUploadedLanguage'] as $i=>$language)
				{
					$metadata['subtitles'][$language] = $_POST['srtUploadedFile'][$i];
				}
			}
			
			// upload any new subtitle
			$i=0;
			foreach($_FILES as $file)
			{
				if($file['error']>0) continue;
				$filename = $kaImages->addFile($id, $file);
				if(!empty($filename))
				{
					$metadata['subtitles'][$_POST['srtLanguage'][$i]] = $filename;
				}
				$i++;
			}

			if($kaImages->updateAlt($id, $_POST['alt']) == false) $log .= $kaTranslate->translate('Img:An error occurred while saving');
			if($kaImages->updateMetadata($id, $metadata) == false) $log .= $kaTranslate->translate('Img:An error occurred while saving metadatas');
		
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
			<?php
			$img=$kaImages->getImage($id);
			
			
			?>
			<table class="properties">
				<tr>
					<td colspan="2" align="center">
						<img src="<?= BASEDIR.$img['thumb']['url']; ?>" alt="" />
					</td>
					<td>
						<label><?= $kaTranslate->translate('Uploads:File name'); ?></label>
						<?= $img['filename']; ?><br>
						<br>
						
						<label for="alt"><?= $kaTranslate->translate('Img:Caption'); ?></label>
						<textarea name="alt" id="alt" style="width:400px;height:100px;"><?= b3_lmthize($img['alt'],"textarea"); ?></textarea><br>
						<br>
						
						<label><?= $kaTranslate->translate('Uploads:File URL'); ?></label>
						<?= SITE_URL.BASEDIR.$img['url']; ?><br>
						<br>
						
						<label><?= $kaTranslate->translate('Uploads:Dimensions'); ?></label>
						<?php
						if($img['filetype'] == 1)
						{ ?>
							<?= $img['width']; ?> x <?= $img['height']; ?> px<br>
						<?php
						} elseif($img['filetype']==2) {
							?>
							<input type="number" name="width" value="<?= intval($img['width']); ?>"> x <input type="number" name="height" value="<?= intval($img['height']); ?>"> px<br>
						<?php } ?>
						<br>

						<?php
						if(!empty($img['metadata']['rotation']))
						{ ?>
							<label><?= $kaTranslate->translate('Uploads:Rotation'); ?></label>
							<?= $img['metadata']['rotation']; ?><br>
							<br>
						<?php } ?>
						
						<?php
						// duration, only for medias
						if($img['filetype'] == 2)
						{
							if(!isset($img['metadata']['duration'])) $img['metadata']['duration'] = 0;
							?>
							<label><?= $kaTranslate->translate('Uploads:Duration'); ?></label>
							<input type="number" name="duration" value="<?= intval($img['metadata']['duration']); ?>"> <?= $kaTranslate->translate('Uploads:seconds'); ?><br>
							<br>
						<?php } ?>
						
						<label><?= $kaTranslate->translate('Uploads:Thumbnail name'); ?></label>
						<?= $img['thumb']['filename']; ?><br>
						<br>
						
						<?php
						/* subtitles, display only for media */
						if($img['filetype'] == 2)
						{
							$countries = kaGetCountries();
							?>
							<label><?= $kaTranslate->translate('Uploads:Subtitles'); ?></label>
							<?php
							if(!empty($img['metadata']['subtitles']))
							{
								foreach($img['metadata']['subtitles'] as $lang=>$filename)
								{
									?>
									<div class="subtitle selected">
										<div class="filename"><?= $filename; ?></div>
										<div class="language">
											<select name="srtUploadedLanguage[]">
												<?php
												foreach($countries as $code => $language)
												{
													?>
													<option value="<?= $code; ?>" <?php if($code == $lang) echo 'selected'; ?>><?= $language; ?></option>
													<?php
												}
												?>
											</select>
											<img src="<?= ADMINRELDIR; ?>img/close.png" width="12" height="12" alt="<?= $kaTranslate->translate('UI:Delete'); ?>" class="srtDelete">
										</div>
										<input type="hidden" name="srtUploadedFile[]" value="<?= htmlentities($filename); ?>">
									</div>
									<?php
								}
							}
							?>
							<div class="subtitle">
								<div class="filename"></div>
								<div class="language">
									<select name="srtLanguage[]">
										<?php
										foreach($countries as $code => $language)
										{
											?>
											<option value="<?= $code; ?>"><?= $language; ?></option>
											<?php
										}
										?>
									</select>
									<img src="<?= ADMINRELDIR; ?>img/close.png" width="12" height="12" alt="<?= $kaTranslate->translate('UI:Delete'); ?>" class="srtDelete">
								</div>
								<input type="file" name="srtFile1" class="file">
								<input type="button" value="<?= $kaTranslate->translate('Uploads:Add subtitle'); ?>" class="smallbutton">
							</div>
							
							<script type="text/javascript">
								function srtFileChangeHandler(e)
								{
									var subtitles = document.querySelectorAll('.subtitle');
									var subtitle = subtitles[ subtitles.length-1 ];
									var newNode = subtitle.parentNode.insertBefore(subtitle.cloneNode(true), subtitle.nextSibling);
									
									subtitle.className += ' selected';
									subtitle.getElementsByClassName('filename')[0].innerHTML = e.target.value;
									newNode.getElementsByClassName('file')[0].value = '';
									newNode.getElementsByClassName('file')[0].name += '1';
									kAddEvent(newNode.getElementsByClassName('file')[0], "change", srtFileChangeHandler);
									kAddEvent(newNode.getElementsByClassName('srtDelete')[0], "click", srtRemove);
								}
								
								function srtRemove(e)
								{
									this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode,true);
								}
								
								kAddEvent(document.querySelector('.subtitle .file'), "change", srtFileChangeHandler);
								
								for(var i=0, c=document.getElementsByClassName('srtDelete'); c[i]; i++)
								{
									kAddEvent(c[i], "click", srtRemove);
								}
							</script>
							<br>
							<?php
						}
						?>
					</td>
				</tr>
			</table>
			<br />
			
			<div class="note"><?= $kaTranslate->translate('Img:Warning: changes will be applied to all the instances of this file'); ?></div>
			<div class="submit">
				<input type="submit" name="save" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
				<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallbutton" onclick="closeModal ? window.parent.k_closeIframeWindow() : window.parent.kUploads.closeEditDialog();">
			</div>
		</form>
		<?php 
	
	} elseif($_GET['action']=="files") {
	
		/**************/
		/* EDIT FILES */
		/**************/

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
			<?php
			$img=$kaImages->getImage($id);
			?>
			<table class="properties">
			<tr>
				<td>
					<?php
					if($img['filetype'] == 1)
					{
						?>
						<img src="<?= ($img['hotlink']==false?BASEDIR:'').$img['url']; ?>" height="100" alt="" />
						<?php
					} elseif($img['filetype'] == 2) {
						?>
						<video width="180" height="100" controls>
							<source src="<?= ($img['hotlink']==false?BASEDIR:'').$img['url']; ?>" type="<?= $img['mime-type']; ?>">
						</video> 
						<?php
					}
					?>
				</td>
				<td>
					<h2><?= $img['filename']; ?></h2>
					<br>
					<label ref="img"><?= $kaTranslate->translate('UI:Change'); ?></label>
					<?php 
					if($img['hotlink']==false)
					{
						?>
						<input name="img" type="file" id="img" /> <input name="saveimg" type="submit" value="<?= $kaTranslate->translate('Img:Upload file'); ?>" class="smallbutton" />
						<?php
					} else {
						?>
						<input type="text" name="img" value="<?= str_replace('"','&quot;',$img['url']); ?>" style="width:300px;"> <input name="save" type="submit" value="<?= $kaTranslate->translate('Img:Update hotlink'); ?>" class="smallbutton" /> <input name="hotlinktoimg" type="submit" value="<?= $kaTranslate->translate('Img:Import to your website'); ?>" class="smallbutton" />
						<?php
					}
					?><br />

					<?php
					if($img['filetype'] == 1)
					{
						?>
						<input type="checkbox" name="autoresize" id="autoresize" value="1" checked="checked" onchange="this.checked?document.getElementById('manualresize').style.display='none':document.getElementById('manualresize').style.display='block';" /> <label for="autoresize"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
						<?php
					}
					?>
					
					<div id="manualresize" style="display:none;"><label for="imgwidth"><?= $kaTranslate->translate('Img:Width'); ?></label> <input type="text" name="imgwidth" id="imgwidth" value="" style="width:50px;" />px <label for="imgheight"><?= $kaTranslate->translate('Img:Height'); ?></label> <input type="text" name="imgheight" id="imgheight" value="" style="width:50px;" />px</div><br />
				</td>
			</tr>
			<tr>
				<td>
					<img src="<?= BASEDIR.$img['thumb']['url']; ?>" height="100" alt="" />
					</td>
				<td>
					<h2><?= $kaTranslate->translate('Img:Thumbnail'); ?>: <?= $img['thumb']['filename']; ?></h2>
					<br>
					<label ref="thumbnail"><?= $kaTranslate->translate('UI:Change'); ?></label>
					<input name="thumbnail" type="file" id="thumbnail" />
					<input name="savethumb" type="submit" value="<?= $kaTranslate->translate('Img:Upload thumbnail'); ?>" class="smallbutton" /><br>
					<input type="checkbox" name="autoresizethumb" id="autoresizethumb" value="1" checked="checked" /> <label for="autoresizethumb"><?= $kaTranslate->translate('Img:Automatic resize'); ?></label>
				</td>
			</tr>
			</table><br />
			<div class="note"><?= $kaTranslate->translate('Img:Warning: changes will be applied to all the instances of this file'); ?></div>
			<div class="submit">
				<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallbutton" onclick="closeModal ? window.parent.k_closeIframeWindow() : window.parent.kUploads.closeEditDialog();">
			</div>
		</form>
		<?php  }

	elseif($_GET['action']=="istances")
	{
		/*********/
		/* USAGE */
		/*********/

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
				}
				?>
			</tbody>
		</table>

		<?php
	}

	elseif($_GET['action']=="fullsize")
	{
		/*********************/
		/* FULL SIZE PREVIEW */
		/*********************/

		$img=$kaImages->getImage($id);
		
		if($img['filetype']==1)
		{
			?>
			<img src="<?= ($img['hotlink']==false?BASEDIR:'').$img['url']; ?>" width="<?= $img['width']; ?>" height="<?= $img['height']; ?>" alt="" />
			<?php
		} elseif($img['filetype']==2) {
			?>
			<video width="<?= $img['width']; ?>" height="<?= $img['height']; ?>" controls>
				<source src="<?= ($img['hotlink']==false?BASEDIR:'').$img['url']; ?>" type="<?= $img['mime-type']; ?>">
			</video> 
			<?php
		}
	}

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
				<?= $kaTranslate->translate('Img:You are deleting this image from the server'); ?>.<br>
				<strong><?= $kaTranslate->translate('Img:No recovery will be possible after deletion'); ?></strong>.<br>
				<?= $kaTranslate->translate('Img:Are you sure that this is what you want?'); ?><br>
				</td></tr>
				</table><br />
				
				<div class="note"><?= $kaTranslate->translate('Img:Warning: changes will be applied to all the instances of this file'); ?></div>
				<div class="submit">
					<input type="submit" name="delete" value="<?= $kaTranslate->translate('UI:Delete'); ?>" class="button" />
					<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallbutton" onclick="closeModal ? window.parent.k_closeIframeWindow() : window.parent.kUploads.closeEditDialog();">
				</div>
				</form>
			<?php  }
		}
	?>
</div>


</body>
</html>
