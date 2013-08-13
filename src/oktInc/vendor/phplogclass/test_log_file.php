<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Class Log php5 file mode test</title>
</head>
<body>
	<?php
		include './class.log.php';
	?>
	<h1>Test de la classe Log en mode fichier</h1>	
	
	<h2>Intanciation fichier fileInsString.log en loggant la chaine "Intanciation avec une chaine"</h2>	
	<em>$objLog = Log::newLog('./log/fileInsString.log','Intanciation avec une chaine');</em>
	<?php
		$objLog = Log::newLog('./log/fileInsString.log','Intanciation avec une chaine');
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
	<h2>Création d'un log de plus de 5 Mo pour voir si la rotation par archivage fonctione</h2>	
	<em>for ($i=0;$i<=60000;$i++){$objLog->logThis('J\'te log en boucle, plein de fois !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');}</em>	
	<?php
		for ($i=0;$i<=60000;$i++){$objLog->logThis('J\'te log en boucle, plein de fois !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');}
	?>
	<h2>Cherchons "J\'te log !" dans de ce log à la date d'aujourd'hui (permet de tester la recherche dans un log archivé). </h2>	
	<em>$objLog->visuLog(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'),'J\'te log !');</em>	
	<?php
		$objLog->visuLog(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'),'J\'te log !');
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
	?>
</body>
</html>
