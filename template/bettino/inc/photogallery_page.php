<div id="titleBox">
	<div class="title"><div class="bottom">
		<h1><?= kGetPhotogalleryTitle(); ?></h1>
		</div></div>
	<div class="curve"></div>
	</div>

<div id="contentsBox">
	<?= kGetPhotogalleryText(); ?>
	</div>

<div id="photogalleryBox">
	<? foreach(kGetPhotogalleryImages() as $img) {
		kSetImage($img);
		?>
		<a href="<?= kGetImageURL(); ?>" rel="lightbuzz"><img src="<?= kGetThumbURL(); ?>" alt="<?= str_replace('"','&quot;',kGetThumbAlt()); ?>" width="<?= kGetThumbWidth(); ?>" height="<?= kGetThumbHeight(); ?>" /></a>
		<? } ?>
	</div>

<? if(kGetPhotogalleryCommentsCount()>0) { ?><div id="commentsBox"><h2><?= kTranslate('Comments'); ?></h2><?= kPrintPhotogalleryComments(); ?></div><? } ?>
<?= kPrintPhotogalleryCommentsForm(); ?>

<div id="pager">
	<a href="<?= kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetPhotogalleriesDir(); ?>"><?= kTranslate('Back to photogallery list'); ?></a>
	</div>
