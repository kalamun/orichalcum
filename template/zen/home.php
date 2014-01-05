<?= kPrintHeader() ?>

		<?
		$n=kGetLatestNews();
		kSetNewsByDir($n[0]['dir']);
		?>
			<h1><?= kGetNewsTitle(); ?></h1>
			<div class="small"><?= kGetNewsDate(); ?></div>
			<?
			$contents=kGetNewsText();
			$contents=preg_replace('#<a href="(.*?\.mp3)" rel="player">[^<]*</a>#','<object type="application/x-shockwave-flash" data="'.kGetTemplateDir().'mp3/dewplayer.swf?mp3=$1" width="300" height="20" id="'.kGetTemplateDir().'mp3/dewplayer"><param name="wmode" value="transparent" /><param name="movie" value="dewplayer-mini.swf?mp3=$1" /></object>',$contents);
			echo $contents;
			?>
			<a href="<?= kGetNewsPermalink(); ?>"><?= kTranslate('Leggi tutto'); ?></a>
			<div style="clear:both;"></div>
			</div>
		<div style="clear:both;"></div>

		<div id="linkToArch">
			<a href="<?= kGetBaseDir().strtolower(kGetLanguage()).'/'.kGetNewsDir(); ?>"><?= kTranslate('Leggi tutti gli articoli'); ?> &raquo;</a>
			</div>

		<div id="newsBox">
			<?
			$n=kGetLatestNews(false,4);
			for($i=1;isset($n[$i]);$i++) {
				?>
				<div class="trittico">
				<h3><a href="<?= $n[$i]['permalink']; ?>"><?= $n[$i]['titolo']; ?></a></h3>
				<?= $n[$i]['anteprima']; ?>
				<a href="<?= $n[$i]['permalink']; ?>"><?= kTranslate('Leggi tutto'); ?></a>
				</div>
				<? } ?>
			<div style="clear:both;"></div>
			</div>

<?= kPrintFooter() ?>