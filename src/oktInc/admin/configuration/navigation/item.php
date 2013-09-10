<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration d'un élément d'un menu de navigation
 *
 * @addtogroup Okatea
 *
 */

# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$iMenuId = !empty($_REQUEST['menu_id']) ? intval($_REQUEST['menu_id']) : null;

$rsMenu = $okt->navigation->getMenu($iMenuId);

if (empty($iMenuId) || $rsMenu->isEmpty()) {
	$okt->redirect('configuration.php?action=navigation');
}

# Données de l'élément
$aItemData = new ArrayObject();

$aItemData['item'] = array();
$aItemData['item']['id'] = null;

$aItemData['item']['menu_id'] = $iMenuId;
$aItemData['item']['active'] = 1;

$aItemData['locales'] = array();

foreach ($okt->languages->list as $aLanguage)
{
	$aItemData['locales'][$aLanguage['code']] = array();

	$aItemData['locales'][$aLanguage['code']]['title'] = '';
	$aItemData['locales'][$aLanguage['code']]['url'] = '';
}

# item update ?
if (!empty($_REQUEST['item_id']))
{
	$aItemData['item']['id'] = intval($_REQUEST['item_id']);

	$rsItem = $okt->navigation->getItem($aItemData['item']['id']);

	if ($rsItem->isEmpty())
	{
		$okt->error->set(sprintf(__('c_a_config_navigation_item_%s_not_exists'), $aItemData['item']['id']));
		$aItemData['item']['id'] = null;
	}
	else
	{
		$aItemData['item']['menu_id'] = $rsItem->menu_id;
		$aItemData['item']['active'] = $rsItem->active;

		$rsItemI18n = $okt->navigation->getItemI18n($aItemData['item']['id']);

		foreach ($okt->languages->list as $aLanguage)
		{
			while ($rsItemI18n->fetch())
			{
				if ($rsItemI18n->language == $aLanguage['code'])
				{
					$aItemData['locales'][$aLanguage['code']]['title'] = $rsItemI18n->title;
					$aItemData['locales'][$aLanguage['code']]['url'] = $rsItemI18n->url;
				}
			}
		}
	}
}


/* Traitements
----------------------------------------------------------*/

#  ajout / modifications d'un élément
if (!empty($_POST['sended']))
{
	$aItemData['item']['active'] = !empty($_POST['p_active']) ? 1 : 0;


	foreach ($okt->languages->list as $aLanguage)
	{
		$aItemData['locales'][$aLanguage['code']]['title'] = !empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';
		$aItemData['locales'][$aLanguage['code']]['url'] = !empty($_POST['p_url'][$aLanguage['code']]) ? $_POST['p_url'][$aLanguage['code']] : '';
	}

	# update item
	if (!empty($aItemData['item']['id']))
	{
		if ($okt->navigation->checkPostItemData($aItemData) !== false)
		{
			try
			{
				$okt->navigation->updItem($aItemData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'menu item',
					'message' => 'item #'.$aItemData['item']['id']
				));

				$okt->redirect('configuration.php?action=navigation&do=item&menu_id='.$iMenuId.'&item_id='.$aItemData['item']['id'].'&updated=1');
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}
	}
	# add item
	else
	{
		if ($okt->navigation->checkPostItemData($aItemData) !== false)
		{
			try
			{
				$iItemId = $okt->navigation->addItem($aItemData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'menu item',
					'message' => 'item #'.$iItemId
				));

				$okt->redirect('configuration.php?action=navigation&do=item&menu_id='.$iMenuId.'&item_id='.$iItemId.'&added=1');
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'configuration.php?action=navigation&amp;do=items&amp;menu_id='.$iMenuId,
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));

if (!empty($aItemData['item']['id']))
{
	$okt->page->addGlobalTitle(sprintf(__('c_a_config_navigation_edit_item_of_%s'), $rsMenu->title));
}
else
{
	$okt->page->addGlobalTitle(sprintf(__('c_a_config_navigation_add_item_to_%s'), $rsMenu->title));
}

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#item-form', '.lang-switcher-buttons');
}

# Confirmations
$okt->page->messages->success('added', __('c_a_config_navigation_item_added'));
$okt->page->messages->success('updated', __('c_a_config_navigation_item_updated'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<form id="item-form" action="configuration.php" method="post">

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_navigation_item_title') : printf(__('c_a_config_navigation_item_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['title'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_url_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_navigation_item_url') : printf(__('c_a_config_navigation_item_url_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_url['.$aLanguage['code'].']','p_url_'.$aLanguage['code']), 100, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['url'])) ?></p>

	<?php endforeach; ?>

	<p class="field"><label><?php echo form::checkbox('p_active', 1, $aItemData['item']['active']) ?> <?php _e('c_c_action_visible') ?></label></p>

	<p><?php echo form::hidden('action', 'navigation'); ?>
	<?php echo form::hidden('do', 'item'); ?>
	<?php echo form::hidden('menu_id', $iMenuId); ?>
	<?php echo !empty($aItemData['item']['id']) ? form::hidden('item_id', $aItemData['item']['id']) : ''; ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php  _e('c_c_action_save'); ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
