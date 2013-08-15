<?php

# fichier nécessaire pour afficher un élément
require_once __DIR__.'/oktModules/##module_id##/inc/public/item.php';


# affichage du template
echo $okt->tpl->render('##module_id##_item_tpl', array(
	'rsItem' => $rsItem
));
