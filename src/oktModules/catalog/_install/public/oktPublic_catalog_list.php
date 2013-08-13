<?php

# paramètres personnalisés de selection des produits
//$aProductsCustomParams = array();


# fichier nécessaire pour afficher une liste de produits
require_once dirname(__FILE__).'/oktModules/catalog/inc/public/list.php';


# affichage du template
echo $okt->tpl->render('catalog_list_tpl', array(
	'productsList' => $productsList
));


