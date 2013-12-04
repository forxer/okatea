<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration d'un menu de navigation
 *
 * @addtogroup Okatea
 *
 */

# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$iMenuId = null;

$aMenuData = array(
	'title' => '',
	'active' => 1,
	'tpl' => ''
);

# menu update ?
if (!empty($_REQUEST['menu_id']))
{
	$iMenuId = intval($_REQUEST['menu_id']);

	$rsMenu = $okt->navigation->getMenu($iMenuId);

	if ($rsMenu->isEmpty())
	{
		$okt->error->set(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
		$iMenuId = null;
	}
	else
	{
		$aMenuData = array(
			'title' => $rsMenu->title,
			'active' => $rsMenu->active,
			'tpl' => $rsMenu->tpl
		);
	}
}


/* Traitements
----------------------------------------------------------*/

# add/update a menu
if (!empty($_POST['sended']))
{
	$aMenuData = array(
		'title' => !empty($_POST['p_title']) ? $_POST['p_title'] : '',
		'active' => !empty($_POST['p_active']) ? 1 : 0,
		'tpl' => !empty($_POST['p_tpl']) ? $_POST['p_tpl'] : ''
	);

	# update menu
	if (!empty($iMenuId))
	{
		$aMenuData['id'] = $iMenuId;

		if ($okt->navigation->checkPostMenuData($aMenuData) !== false)
		{
			try
			{
				$okt->navigation->updMenu($aMenuData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'menus',
					'message' => 'menu #'.$iMenuId
				));

				$okt->page->flashMessages->addSuccess(__('c_a_config_navigation_menu_updated'));

				http::redirect('configuration.php?action=navigation&do=menu&menu_id='.$iMenuId);
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}
	}

	# add menu
	else
	{
		if ($okt->navigation->checkPostMenuData($aMenuData) !== false)
		{
			try
			{
				$iMenuId = $okt->navigation->addMenu($aMenuData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'menus',
					'message' => 'menu #'.$iMenuId
				));

				$okt->page->flashMessages->addSuccess(__('c_a_config_navigation_menu_added'));

				http::redirect('configuration.php?action=navigation&do=menu&menu_id='.$iMenuId);
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

if (!empty($iMenuId)) {
	$okt->page->addGlobalTitle(__('c_a_config_navigation_edit_menu'));
}
else {
	$okt->page->addGlobalTitle(__('c_a_config_navigation_add_menu'));
}

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'configuration.php?action=navigation&amp;do=index',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));

if ($iMenuId)
{
	# bouton add menu
	$okt->page->addButton('navigationBtSt', array(
		'permission' 	=> true,
		'title' 		=> __('c_a_config_navigation_add_menu'),
		'url' 			=> 'configuration.php?action=navigation&amp;do=menu',
		'ui-icon' 		=> 'plusthick'
	));

	# bouton manage items
	$okt->page->addButton('navigationBtSt', array(
		'permission' 	=> true,
		'title' 		=> __('c_a_config_navigation_manage_items'),
		'url' 			=> 'configuration.php?action=navigation&amp;do=items&amp;menu_id='.$iMenuId,
		'ui-icon' 		=> 'pencil'
	));
}


# Liste des templates utilisables
$oTemplates = new oktTemplatesSet($okt, $okt->config->navigation_tpl, 'navigation', 'navigation');
$aTplChoices = array_merge(
	array('&nbsp;' => null),
	$oTemplates->getUsablesTemplatesForSelect($okt->config->navigation_tpl['usables'])
);


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<?php if (!empty($iMenuId)) : ?>
	<h3><?php _e('c_a_config_navigation_edit_menu') ?></h3>
<?php else : ?>
	<h3><?php _e('c_a_config_navigation_add_menu') ?></h3>
<?php endif; ?>

<form id="menu-form" action="configuration.php" method="post">

	<div class="two-cols">
		<p class="field col"><label for="p_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_navigation_menu_title') ?></label>
		<?php echo form::text('p_title', 100, 255, html::escapeHTML($aMenuData['title'])) ?>

		<?php if (!empty($okt->config->navigation_tpl['usables'])) : ?>
		<p class="field col"><label for="p_tpl"><?php _e('c_a_config_navigation_menu_tpl') ?></label>
		<?php echo form::select('p_tpl', $aTplChoices, $aMenuData['tpl'])?></p>
		<?php endif; ?>
	</div>

	<p class="field"><label for="p_active"><?php echo form::checkbox('p_active', 1, $aMenuData['active']) ?> <?php _e('c_c_action_visible') ?></label></p>

	<p><?php echo form::hidden('action', 'navigation'); ?>
	<?php echo form::hidden('do', 'menu'); ?>
	<?php echo !empty($iMenuId) ? form::hidden('menu_id', $iMenuId) : ''; ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php empty($iMenuId) ? _e('c_c_action_add') : _e('c_c_action_edit'); ?>" /></p>
</form>

<?php if (!empty($iMenuId)) : ?>
<div class="note">
	<p><?php _e('c_a_config_navigation_menu_usage') ?></p>
	<p><?php _e('c_a_config_navigation_menu_usage_by_id') ?><br />
	<code><?php echo html::escapeHTML('<?php echo $okt->navigation->render('.$iMenuId.') ?>') ?></code></p>
	<p><?php _e('c_a_config_navigation_menu_usage_by_title') ?><br />
	<code><?php echo html::escapeHTML('<?php echo $okt->navigation->render(\''.$aMenuData['title'].'\') ?>') ?></code> </p>
</div>
<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
