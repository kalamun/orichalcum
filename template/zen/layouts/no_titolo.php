<? include($_SERVER['DOCUMENT_ROOT'].kGetTemplateDir().'inc/header.php'); ?>

	<?
	if(kHavePage()) {
		kSetPageByDir();
		?>
		<div class="contentsBox">
			<?
			if(count(kGetPageDocuments())>0) { ?>
				<div id="pageDocuments"><ul>
				<? $i=0;
				foreach(kGetPageDocuments() as $doc) {
					$template->docDB=$doc;
					?>
					<li><a href="<?= kGetDocumentURL(); ?>" title="<?= kGetDocumentAlt(); ?>"><?= trim(kGetDocumentCaption())!=""?kGetDocumentCaption():kGetDocumentFilename(); ?> <span class="filesize">(<?= kGetDocumentFilesize("Mb",2); ?> Mb)</span></a></li>
					<?
					$i++;
					} ?>
				</div>
				<? }
			?>
			<div class="newsPreview">
			<?= kGetPagePreview(); ?>
			<? if(trim(kGetPageText())!="") echo kGetPageText(); ?>
			</div>
			
			<?
			if(count(kGetPagePhotogallery())>0) { ?>
				<div id="pageGallery">
				<? $i=0;
				foreach(kGetPagePhotogallery() as $img) {
					$template->imgDB=$img;
					?>
					<div class="phgthumb" id="photoThumb<?= $i; ?>"><a href="<?= kGetImageURL(); ?>" rel="lightbuzz"><img src="<?= kGetThumbURL(); ?>" width="<?= kGetThumbWidth(); ?>" height="<?= kGetThumbHeight(); ?>" alt="<?= kGetThumbAlt(); ?>" onload="centerImgOnParent(this);" /></a></div>
					<?
					$i++;
					} ?>
					<div style="clear:both;"></div>
				</div>
				<? }
			?>
			</div>
		
		<div id="submenu">
			<?
			$ancestors=kGetCrumbs();
			if($ancestors[0]['idmenu']>0&&$ancestors[0]['idmenu']!=kGetMenuId()) echo kPrintMenu($ancestors[0]['idmenu'],false);
			?>
			</div>
		<?
		}
	?>
	<div style="clear:both;"></div>

<? include($_SERVER['DOCUMENT_ROOT'].kGetTemplateDir().'inc/footer.php'); ?>
