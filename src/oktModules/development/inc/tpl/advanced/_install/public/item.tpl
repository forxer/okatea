<?php

# fichier nécessaire pour afficher un élément
require_once dirname(__FILE__).'/oktModules/##module_id##/inc/public/item.php';


# affichage du template
echo $okt->tpl->render('##module_id##_item_tpl', array(
	'rsItem' => $rsItem
));
