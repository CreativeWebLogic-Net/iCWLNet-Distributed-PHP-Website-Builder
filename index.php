<?php
	
	session_start();
	ini_set( 'display_errors', '1' );
	include_once("classes/clsDCMS.php");
	$cms=new DistributedCMS();
	$cms->CommandInterface($_SERVER['REQUEST_URI']);
	
?>