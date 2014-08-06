<?php
/**
 * @ingroup okt_module_catalog
 * @brief Service pour la requete AJAX ordering categories
 *
 */

# inclusion du preprend public général
require_once __DIR__ . '/../../oktInc/admin/prepend.php';

if (! $okt['visitor']->checkPerm('catalog_categories'))
{
	exit();
}

$order = ! empty($_GET['ord']) ? $_GET['ord'] : array();

foreach ($order as $ord => $id)
{
	$ord = ((integer) $ord) + 1;
	$okt->catalog->updCategoryOrder($id, $ord);
}

