<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateAdminUrl('config_navigation') . '?do=items&amp;menu_id=' . $iMenuId,
			'ui-icon' => 'arrowreturnthick-1-w'
		)
	)
));

if (! empty($iItemId))
{
	$okt->page->addGlobalTitle(sprintf(__('c_a_config_navigation_edit_item_of_%s'), $aMenu['title']));
}
else
{
	$okt->page->addGlobalTitle(sprintf(__('c_a_config_navigation_add_item_to_%s'), $aMenu['title']));
}

# Lang switcher
if (! $okt['languages']->hasUniqueLanguage())
{
	$okt->page->langSwitcher('#item-form', '.lang-switcher-buttons');
}

# Build possibles URL labels
$aUrlLabel = array();
foreach ($okt['languages']->getList() as $aLanguage)
{
	$sBaseUrl = '<code>' . $okt->page->getBaseUrl($aLanguage['code']) . '</code>';

	$aUrlLabel[$aLanguage['code']] = array();

	if ($okt['languages']->hasUniqueLanguage())
	{
		$aUrlLabel[$aLanguage['code']][0] = sprintf(__('c_a_config_navigation_item_url_from_%s'), $sBaseUrl);
		$aUrlLabel[$aLanguage['code']][1] = sprintf(__('c_a_config_navigation_item_url'));
	}
	else
	{
		$aUrlLabel[$aLanguage['code']][0] = sprintf(__('c_a_config_navigation_item_url_in_%s_from_%s'), $aLanguage['title'], $sBaseUrl);
		$aUrlLabel[$aLanguage['code']][1] = sprintf(__('c_a_config_navigation_item_url_in_%s'), $aLanguage['title']);
	}
}

$okt->page->js->addReady('
	var possibles_labels = ' . json_encode($aUrlLabel) . ';

	$("#p_type").change(function(){
		var value = $(this).val();

		$(".item_url").each(function(){
			var language = $(this).attr("id").replace("p_url_", "");
			var label = possibles_labels[language][value];
			$("span#text_"+language).html(label);
		});

	});
');
?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<form id="item-form"
	action="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=item"
	method="post">

	<div class="two-cols">
		<p class="field col">
			<label for="p_type"><?php _e('c_a_config_navigation_item_type') ?></label>
		<?php echo form::select('p_type', array(__('c_a_config_navigation_item_internal') => 0, __('c_a_config_navigation_item_external') => 1), $aItemData['item']['type']) ?></p>

		<p class="field col">
			<label><?php echo form::checkbox('p_active', 1, $aItemData['item']['active']) ?> <?php _e('c_c_action_visible') ?></label>
		</p>
	</div>

	<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>

	<div class="two-cols">
		<p class="field col" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_a_config_navigation_item_title') : printf(__('c_a_config_navigation_item_title_in_%s'), $aLanguage['title']) ?> <span
				class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, $view->escape($aItemData['locales'][$aLanguage['code']]['title'])) ?></p>

		<p class="field col" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_url_<?php echo $aLanguage['code'] ?>"><span
				id="text_<?php echo $aLanguage['code'] ?>"><?php echo $aUrlLabel[$aLanguage['code']][$aItemData['item']['type']] ?></span>
				<span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_url['.$aLanguage['code'].']','p_url_'.$aLanguage['code']), 100, 255, $view->escape($aItemData['locales'][$aLanguage['code']]['url']), 'item_url') ?></p>
	</div>
	<?php endforeach; ?>

	<p><?php echo form::hidden('menu_id', $iMenuId); ?>
	<?php echo !empty($iItemId) ? form::hidden('item_id', $iItemId) : ''; ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php  _e('c_c_action_save'); ?>" />
	</p>
</form>
