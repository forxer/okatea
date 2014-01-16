<?php
/**
 * @ingroup okt_module_galleries
 * @brief Service pour la requete AJAX ordering items
 *
 */


# inclusion du preprend public général
require_once __DIR__.'/../../oktInc/admin/prepend.php';

if (!$okt->checkPerm('galleries')) {
	exit;
}

$order = !empty($_GET['ord']) ? $_GET['ord'] : array();

foreach ($order as $ord=>$id)
{
	$ord = ((integer) $ord)+1;
	$okt->galleries->items->setItemPosition($id, $ord);
}
