<?php
/**
 * @ingroup okt_module_users
 * @brief Fichier principal des pages d'administration du module.
 *
 */

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	# Perm ?
if (!$okt['visitor']->checkPerm('users') && $okt->page->action !== 'profil')
{
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

if ($okt->page->action === 'profil')
{
	require __DIR__ . '/admin/profil.php';
}
else
{
	# titre de la page
	$okt->page->addGlobalTitle(__('Users'), 'module.php?m=users');
	
	# button set
	$okt->page->setButtonset('users', array(
		'id' => 'users-buttonset',
		'type' => '', #  buttonset-single | buttonset-multi | ''
		'buttons' => array(
			array(
				'permission' => ($okt->page->action === 'add' || $okt->page->action === 'edit'),
				'title' => __('c_c_action_Go_back'),
				'url' => 'module.php?m=users&amp;action=index',
				'ui-icon' => 'arrowreturnthick-1-w'
			),
			array(
				'permission' => (!$okt->page->action || $okt->page->action === 'index' || $okt->page->action === 'edit'),
				'title' => __('m_users_Add_user'),
				'url' => 'module.php?m=users&amp;action=add',
				'ui-icon' => 'plusthick'
			)
		)
	));
	
	# inclusion du fichier requis
	if (!$okt->page->action || $okt->page->action === 'index')
	{
		require __DIR__ . '/admin/index.php';
	}
	elseif ($okt->page->action === 'add')
	{
		require __DIR__ . '/admin/add.php';
	}
	elseif ($okt->page->action === 'edit')
	{
		require __DIR__ . '/admin/edit.php';
	}
	elseif ($okt->page->action === 'groups' && $okt['visitor']->checkPerm('users_groups'))
	{
		require __DIR__ . '/admin/groups.php';
	}
	elseif ($okt->page->action === 'fields' && $okt->users->config->enable_custom_fields && $okt['visitor']->checkPerm('users_custom_fields'))
	{
		require __DIR__ . '/admin/fields.php';
	}
	elseif ($okt->page->action === 'field' && $okt->users->config->enable_custom_fields && $okt['visitor']->checkPerm('users_custom_fields'))
	{
		require __DIR__ . '/admin/field.php';
	}
	elseif ($okt->page->action === 'export' && $okt['visitor']->checkPerm('users_export'))
	{
		require __DIR__ . '/admin/export.php';
	}
	elseif ($okt->page->action === 'display' && $okt['visitor']->checkPerm('users_display'))
	{
		require __DIR__ . '/admin/display.php';
	}
	elseif ($okt->page->action === 'config' && $okt['visitor']->checkPerm('users_config'))
	{
		require __DIR__ . '/admin/config.php';
	}
	else
	{
		http::redirect('index.php');
	}
}
