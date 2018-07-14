<?php

$submenu = [
	"index.php" => "Lingue attive",
	"dictionary.php" => "Dizionario",
	"map.php" => "Mappa delle traduzioni",
];

?>
<ul role="nav">
	<?php

	foreach( $submenu as $url => $label )
	{
		?>
		<li<?= basename( $_SERVER['PHP_SELF'] ) == $url ? ' class="selected"' : ''; ?>>
			<a href="<?= $url; ?>"><?= $label; ?></a>
		</li>
		<?php
	}
	?>
</ul>