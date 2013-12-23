<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil gestion du .htaccess (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


# création du fichier .htaccess
if (!empty($_GET['create_htaccess']))
{
	if ($bHtaccessExists) {
		$okt->error->set(__('c_a_tools_htaccess_allready_exists'));
	}
	elseif (!$bHtaccessDistExists)
	{
		$okt->error->set(__('c_a_tools_htaccess_template_not_exists'));
	}
	else
	{
		file_put_contents(OKT_ROOT_PATH.'/.htaccess',file_get_contents(OKT_ROOT_PATH.'/.htaccess.oktDist'));

		$okt->page->flash->success(__('c_a_tools_htaccess_created'));

		http::redirect('configuration.php?action=tools');
	}
}


# suppression du fichier .htaccess
if (!empty($_GET['delete_htaccess']))
{
	@unlink(OKT_ROOT_PATH.'/.htaccess');
	http::redirect('configuration.php?action=tools&htaccess_deleted=1');
}


# modification du fichier .htaccess
if (!empty($_POST['htaccess_form_sent']))
{
	$sHtaccessContent = !empty($_POST['p_htaccess_content']) ? $_POST['p_htaccess_content'] : '';

	file_put_contents(OKT_ROOT_PATH.'/.htaccess',$sHtaccessContent);

	$okt->page->flash->success(__('c_a_tools_htaccess_edited'));

	http::redirect('configuration.php?action=tools1');
}
