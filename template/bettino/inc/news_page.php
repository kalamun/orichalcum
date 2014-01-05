<div id="titleBox">
	<div class="title"><div class="bottom">
		<h1><?= kGetNewsTitle(); ?></h1>
		<? if(kGetNewsSubtitle()!="") { ?><h2><?= kGetNewsSubtitle(); ?></h2><? } ?>
		</div></div>
	<div class="curve"></div>
	</div>

<div id="contentsBox">
	<? if(trim(strip_tags(kGetNewsPreview(),"<img><embed><object><form>"))!="") echo '<div class="preview">'.kGetNewsPreview().'</div>'; ?>
	<?= kGetNewsText(); ?>
	</div>

<? if(count(kGetNewsDocuments())>0) { ?>
	<div id="documentgalleryBox">
		<h4><?= kTranslate('Documents'); ?></h4>
		<ul>
		<? foreach(kGetNewsDocuments() as $doc) {
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

<? if(count(kGetNewsPhotogallery())>0) { ?>
	<div id="photogalleryBox">
		<? foreach(kGetNewsPhotogallery() as $img) {
			kSetImage($img);
			?>
			<a href="<?= kGetImageURL(); ?>" rel="lightbuzz"><img src="<?= kGetThumbURL(); ?>" alt="<?= str_replace('"','&quot;',kGetThumbAlt()); ?>" width="<?= kGetThumbWidth(); ?>" height="<?= kGetThumbHeight(); ?>" /></a>
			<? } ?>
		</div>
	<? } ?>

<? if(kGetNewsCommentsCount()>0) { ?><div id="commentsBox"><h2><?= kTranslate('Comments'); ?></h2><?= kPrintNewsComments(); ?></div><? } ?>
<?= kPrintNewsCommentsForm(); ?>

<div id="pager">
	<? $tmp=kGetNewsPrevious();
	if(isset($tmp[0])&&$tmp[0]['permalink']!="") { ?><a href="<?= $tmp[0]['permalink']; ?>" class="prev" title="<?= str_replace('"','&quot;',$tmp[0]['titolo']); ?>"><?= kTranslate('Older News'); ?></a><? } ?>
	<? $tmp=kGetNewsNext();
	if(isset($tmp[0])&&$tmp[0]['permalink']!="") { ?><a href="<?= $tmp[0]['permalink']; ?>" class="next" title="<?= str_replace('"','&quot;',$tmp[0]['titolo']); ?>"><?= kTranslate('Newer News'); ?></a><? } ?>
	</div>
