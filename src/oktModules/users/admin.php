<?php
/**
 * @ingroup okt_module_users
 * @brief Fichier principal des pages d'administration du module.
 *
 */

# Accès direct interdit
if (!defined('ON_USERS_MODULE')) die;

# Perm ?
if (!$okt->checkPerm('users') && $okt->page->action !== 'profil') {
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

if ($okt->page->action === 'profil') {
	require __DIR__.'/inc/admin/profil.php';
}
else {
	# titre de la page
	$okt->page->addGlobalTitle(__('Users'),'module.php?m=users');

	# button set
	$okt->page->setButtonset('users',array(
		'id' => 'users-buttonset',
		'type' => '', #  buttonset-single | buttonset-multi | ''
		'buttons' => array(
			array(
				'permission' => ($okt->page->action === 'add' || $okt->page->action === 'edit'),
				'title' => __('c_c_action_Go_back'),
				'url' => 'module.php?m=users&amp;action=index',
				'ui-icon' => 'arrowreturnthick-1-w',
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
	if (!$okt->page->action || $okt->page->action === 'index') {
		require __DIR__.'/inc/admin/index.php';
	}
	elseif ($okt->page->action === 'add') {
		require __DIR__.'/inc/admin/add.php';
	}
	elseif ($okt->page->action === 'edit') {
		require __DIR__.'/inc/admin/edit.php';
	}
	elseif ($okt->page->action === 'groups' && $okt->checkPerm('groups')) {
		require __DIR__.'/inc/admin/groups.php';
	}
	elseif ($okt->page->action === 'fields' && $okt->users->config->enable_custom_fields && $okt->checkPerm('users_custom_fields')) {
		require __DIR__.'/inc/admin/fields.php';
	}
	elseif ($okt->page->action === 'field' && $okt->users->config->enable_custom_fields && $okt->checkPerm('users_custom_fields')) {
		require __DIR__.'/inc/admin/field.php';
	}
	elseif ($okt->page->action === 'export' && $okt->checkPerm('users_export')) {
		require __DIR__.'/inc/admin/export.php';
	}
	elseif ($okt->page->action === 'display' && $okt->checkPerm('users_display')) {
		require __DIR__.'/inc/admin/display.php';
	}
	elseif ($okt->page->action === 'config' && $okt->checkPerm('users_config')) {
		require __DIR__.'/inc/admin/config.php';
	}
	else {
		$okt->redirect('index.php');
	}

}
