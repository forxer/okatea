<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Class Log php5 mysql mode test</title>
</head>
<body>
	<?php
		$objMysqlLink = mysql_connect('localhost','votreusermysql','motdepassemysql') or die(mysql_error());
		mysql_select_db('votrebase') or die(mysql_error());
		include './class.log.php';
	?>
	<h1>Test de la classe Log en mode mysql</h1>	
	
	<h2>Intanciation mysql dans table log_test</h2>	
	<em>$objLog = Log::newLog('mysql','','log_test');</em>
	<?php
		$objLog = Log::newLog('mysql','','log_test');
	?>
	<h2>Ajout de la chaine "J'te log !"</h2>	
	<em>$objLog->logThis('J\'te log !');</em>	
	<?php
		$objLog->logThis('J\'te log !');
	?>
	<h2>Visualisation compl&eacute;te de ce log</h2>	
	<em>$objLog->visuLog();</em>	
	<?php
		$objLog->visuLog();
	?>
	<h2>Création de 20 log </h2>	
	<em>for ($i=0;$i<=19;$i++){$objLog->logThis('J\'te log en boucle, plein de fois !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');}</em>	
	<?php
		for ($i=0;$i<=19;$i++){$objLog->logThis('J\'te log en boucle, plein de fois !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');}
	?>
	<h2>Visu dans de ce log à la date d'aujourd'hui. </h2>	
	<em>$objLog->visuLog(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'));</em>	
	<?php
		$objLog->visuLog(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'));
	?>
	<h2>Purge compl&eacute;te de ce log</h2>	
	<em>$objLog->purgeLog();</em>	
	<?php
		$objLog->purgeLog();
	?>
	<h2>Re-visualisation compl&eacute;te de ce log</h2>	
	<em>$objLog->visuLog();</em>	
	<?php
		$objLog->visuLog();
		mysql_close($objMysqlLink);
	?>
</body>
</html>
