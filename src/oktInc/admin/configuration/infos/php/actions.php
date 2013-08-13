<?php
/**
 * Outil infos Okatea (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

# affichage phpinfo()
if (!empty($_GET['phpinfo']))
{
	phpinfo();
	exit;
}
