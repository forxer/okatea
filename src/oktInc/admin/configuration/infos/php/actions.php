<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
