<? kPrintHeader(); ?>

	<?
	if(kHaveNews()) {
		kPrintNews();	
		}

	else { ?>
		<div id="titleBox">
			<div class="title"><div class="bottom">
				<h1><?= kGetTitle(); ?></h1>
				</div></div>
			<div class="curve"></div>
			</div>

		<div id="contentsBox">
			<?
			if(!isset($_GET['p'])) $_GET['p']=1;
			$nws=kGetNewsQuickList(false,$_GET['p'],false,false);
			for($i=0;isset($nws[$i]);$i++) {
				?>
				<div class="newsPreview" onclick="window.location.href=this.getElementsByTagName('A')[0].href">
				<a href="<?= $nws[$i]['permalink']; ?>" class="readmore"><img src="<?= kGetTemplateDir(); ?>img/readmore.png" width="8" height="12" alt="<?= addslashes(kTranslate('Read more')); ?>" /></a>
				<h2><a href="<?= $nws[$i]['permalink']; ?>"><?= $nws[$i]['titolo']; ?></a></h2>
				<?= $nws[$i]['anteprima']; ?>
				</div>
				<?
				}
			?>
			</div>

		<div id="pager">
			<? if(count(kGetNewsQuickList(false,$_GET['p']+1,false,false))>0) { ?><a href="?p=<?= $_GET['p']+1; ?>" class="next"><?= kTranslate('Older News'); ?></a><? } ?>
			<? if($_GET['p']>1) { ?><a href="?p=<?= $_GET['p']-1; ?>" class="prev"><?= kTranslate('Newer News'); ?></a><? } ?>
			</div>
		<? } ?>

<? kPrintFooter(); ?>
