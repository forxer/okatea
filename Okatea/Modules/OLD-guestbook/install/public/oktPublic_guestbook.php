<?php

# fichier nécessaire pour afficher un livre d'or
require_once __DIR__ . '/oktModules/guestbook/inc/public/list.php';

# affichage du template
echo $okt['tpl']->render('guestbook_tpl', array(
	'aSigData' => $aSigData,
	'signaturesList' => $signaturesList,
	'aLanguages' => $aLanguages,
	'sUserLanguage' => $sUserLanguage
));


