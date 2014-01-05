<div class="newsPreview" onclick="window.location.href=this.getElementsByTagName('A')[0].href">
<a href="<?= kGetPhotogalleryPermalink(); ?>" class="readmore"><img src="<?= kGetTemplateDir(); ?>img/readmore.png" width="8" height="12" alt="<?= addslashes(kTranslate('View photos')); ?>" /></a>
<h2><a href="<?= kGetPhotogalleryPermalink(); ?>"><?= kGetPhotogalleryTitle(); ?></a></h2>
<?= count(kGetPhotogalleryImages()); ?> <?= kTranslate('photos'); ?>
</div>
