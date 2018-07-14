<?php

$submenu = [
	"index.php" => "Gestione utenti",
	"add.php" => "Crea un nuovo utente",
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