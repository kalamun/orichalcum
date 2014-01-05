<div class="newsPreview" onclick="window.location.href=this.getElementsByTagName('A')[0].href">
<h2><a href="<?= kGetPhotogalleryPermalink(); ?>"><?= kGetPhotogalleryTitle(); ?></a></h2>
<?= count(kGetPhotogalleryImages()); ?> <?= kTranslate('photos'); ?> &gt;
<a href="<?= kGetPhotogalleryPermalink(); ?>"><?= kTranslate('View'); ?></a>
</div>
