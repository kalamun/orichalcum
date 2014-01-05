<div id="titleBox">
	<div class="title"><div class="bottom">
		<h1><?= kGetPageTitle(); ?></h1>
		<? if(kGetPageSubtitle()!="") { ?><h2><?= kGetPageSubtitle(); ?></h2><? } ?>
		</div></div>
	<div class="curve"></div>
	</div>

<div id="contentsBox">
	<? if(trim(strip_tags(kGetPagePreview(),"<img><embed><object><form>"))!="") echo '<div class="preview">'.kGetPagePreview().'</div>'; ?>
	<?= kGetPageText(); ?>
	</div>

<? if(count(kGetPageDocuments())>0) { ?>
	<div id="documentgalleryBox">
		<h4><?= kTranslate('Documents'); ?></h4>
		<ul>
		<? foreach(kGetPageDocuments() as $doc) {
			kSetDocument($doc);
			$ext=substr(kGetDocumentFilename(),strrpos(kGetDocumentFilename(),".")+1);
			$icon=kGetTemplateDir().'img/mime/'.$ext.'.png';
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].$icon)) $icon=kGetTemplateDir().'img/mime/_.png';
			?>
			<li><a href="<?= kGetDocumentURL(); ?>"><img src="<?= $icon; ?>" width="16" height="16" /> <?= (kGetDocumentAlt()!=""?kGetDocumentAlt():kGetDocumentFilename()); ?> (<?= ceil(kGetDocumentFilesize()/10)/100; ?>Mb)</a></li>
			<? } ?>
			</ul>
		</div>
	<? } ?>

<? if(count(kGetPagePhotogallery())>0) { ?>
	<div id="photogalleryBox">
		<? foreach(kGetPagePhotogallery() as $img) {
			kSetImage($img);
			?>
			<a href="<?= kGetImageURL(); ?>" rel="lightbuzz"><img src="<?= kGetThumbURL(); ?>" alt="<?= str_replace('"','&quot;',kGetThumbAlt()); ?>" width="<?= kGetThumbWidth(); ?>" height="<?= kGetThumbHeight(); ?>" /></a>
			<? } ?>
		</div>
	<? } ?>
