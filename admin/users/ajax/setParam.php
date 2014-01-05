<?php
session_start();
if(!isset($_SESSION['iduser'])) die();
if(!isset($_POST['param'])) die();
if(!isset($_POST['family'])) die();
if(!isset($_POST['value'])) die();

include('../../inc/connect.inc.php');
include('../users.lib.php');
$kaUsers=new kaUsers();

$kaUsers->propReplace($_SESSION['iduser'],$_POST['family'],$_POST['param'],$_POST['value']);

?>