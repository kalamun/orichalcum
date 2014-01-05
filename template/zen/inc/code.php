<div class="code">
	<ol><?
	foreach(explode("<br />",trim(kGetContents())) as $line) { ?>
		<li><?= trim($line); ?></li>
		<? }
	?></ol>
	</div>