
		<div id="rightCol">
			<?
			$bnrs=kGetBannerList('Default');
			if(!is_array($bnrs)) $bnrs=array();
			?>
			<div id="inEvidenzaTabs"><?
				$i=0;
				foreach($bnrs as $b) {
					echo '<a href="javascript:kInEvidenzaShow('.$i.');" class="'.($i==0?'sel':'').'">'.$b['title'].'</a>';
					$i++;
					}
				?></div>
			<div id="inEvidenza"><?
				$i=0;
				foreach($bnrs as $b) {
					echo '<a href="'.$b['url'].'" class="'.($i==0?'sel':'').'"><img src="'.$b['banner']['url'].'" alt="" /></a>';
					$i++;
					}
				?></div>
			
			<div id="latestNews">
				<?= kTranslate('LATEST NEWS'); ?>
				<ul><?
				foreach(kGetLatestNews("*",5) as $n) {
					kSetNewsByDir($n['dir']);
					?>
					<li><a href="<?= kGetNewsPermalink(); ?>"><?= kGetNewsTitle(); ?></a></li>
					<?
					}
				?></ul>
				</div>
			
			</div>

		</div>

	<div id="footer">
		<?= kGetFooter(); ?>
		</div>
	</div>
<?= kGetExternalStatistics(); ?>

</body>
</html>