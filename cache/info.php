<pre>
<?php
	print $_SERVER['SERVER_NAME']."-<br>";
	$host_name=gethostname();
	print $host_name."-<br>";
	$current_dir=pathinfo(__DIR__);
	print_r($current_dir)."<br>";
	print($_SERVER['PHP_SELF']);
?>
</pre>
<?php
	phpinfo();


?>