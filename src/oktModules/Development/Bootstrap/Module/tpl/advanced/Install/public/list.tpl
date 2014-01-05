<?php

# paramètres personnalisés de selection des éléments
//$aItemsCustomParams = array();


# fichier nécessaire pour afficher une liste d'éléments
require_once __DIR__.'/oktModules/##module_id##/inc/public/list.php';


# affichage du template
echo $okt->tpl->render('##module_id##_list_tpl', array(
	'rsItemsList' => $rsItemsList
));

