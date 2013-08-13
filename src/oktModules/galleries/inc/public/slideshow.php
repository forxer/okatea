<?php
/**
 * @ingroup okt_module_galleries
 * @brief Prepend pour l'affichage d'un diaporama
 *
 */

# inclusion du preprend public général
require_once dirname(__FILE__).'/../../../../oktInc/public/prepend.php';


# récupération aléatoire des éléments des galeries
$randomGalleriesItems = $okt->galleries->getItems(array(
	'visibility' => 1,
	'order' => 'RAND()',
	'limit' => '0,10'
));

