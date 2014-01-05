<div class="newsList">
	<div class="data">
		<h2><?= kGetNewsDate("d"); ?></h2>
		<h4><?= kTranslate('mese'.kGetNewsDate("m")); ?> <?= kGetNewsDate("Y"); ?></h4>
		</div>

	<div class="contents">
		<h2><a href="<?= kGetNewsPermalink(); ?>"><?= kGetNewsTitle(); ?></a></h2>
		<? foreach(kGetNewsCategories() as $cat) { ?><a href="<?= $cat['permalink']; ?>"><?= $cat['categoria']; ?></a> <? } ?>
		</div>
	<div style="clear:both;"></div>
	<?= kGetNewsPreview(); ?>
	<a href="<?= kGetNewsPermalink(); ?>"><?= kTranslate('Leggi tutto'); ?></a>
</div>
