<?php
/**
 * @ingroup okt_module_catalog
 * @brief "controller" pour l'affichage de la liste des catégories
 *
 */


# inclusion du preprend public général
require_once dirname(__FILE__).'/../../../../oktInc/public/prepend.php';

# récupération de la liste des catégories actives
$rsCategories = $okt->catalog->getCategories(array('active'=>1,'with_count'=>false));

$okt->tpl->assign(array('rsCategories'=>$rsCategories));