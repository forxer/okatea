<?php
/**
 * @ingroup okt_module_partners
 * @brief Service pour la requete AJAX ordering categories
 *
 */


# inclusion du preprend public général
require_once dirname(__FILE__).'/../../oktInc/admin/prepend.php';

if (!$okt->checkPerm('partners')) {
	exit;
}

$order = !empty($_GET['ord']) ? $_GET['ord'] : array();

foreach ($order as $ord=>$id)
{
	$ord = ((integer) $ord)+1;
	$okt->partners->updCategoryOrder($id,$ord);
}

