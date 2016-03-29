<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
/* record events on internal analytics */

// filter inputs
if(empty($_GET['family'])) die('No family defined');
if(empty($_GET['event'])) die('No event defined');
if(empty($_GET['ref'])) die('No reference defined');

require_once('./tplshortcuts.lib.php');
kInit('../');

kRegisterEvent($_GET['family'], $_GET['event'], $_GET['ref']);

