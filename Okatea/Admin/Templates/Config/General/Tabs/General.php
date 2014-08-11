<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('c_a_config_tab_general') ?></h3>

<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_title_<?php echo $aLanguage['code'] ?>"
		title="<?php _e('c_c_required_field') ?>"
		class="required"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_a_config_website_title') : printf(__('c_a_config_website_title_in_%s'), $view->escape($aLanguage['title'])); ?><span
		class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($aPageData['values']['title'][$aLanguage['code']]) ? $view->escape($aPageData['values']['title'][$aLanguage['code']]) : '')) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_desc_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_a_config_website_desc') : printf(__('c_a_config_website_desc_in_%s'), $view->escape($aLanguage['title'])); ?><span
		class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_desc['.$aLanguage['code'].']','p_desc_'.$aLanguage['code']), 60, 255, (isset($okt['config']->desc[$aLanguage['code']]) ? $view->escape($aPageData['values']['desc'][$aLanguage['code']]) : '')) ?></p>

<?php endforeach; ?>

<fieldset id="config_website_home_page">
	<legend><?php _e('c_a_config_website_home_page') ?></legend>

<?php if (count($aPageData['home_page_items']) <= 1) : ?>
	<p><?php _e('c_a_config_website_no_home_page_item') ?></p>

<?php else : ?>
	<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>
	<div class="two-cols">
		<p class="field col" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_home_page_item_<?php echo $aLanguage['code'] ?>">
		<?php $okt['languages']->hasUniqueLanguage() ? _e('c_a_config_website_home_page_item') : printf(__('c_a_config_website_home_page_item_in_%s'), $view->escape($aLanguage['title'])); ?><span
				class="lang-switcher-buttons"></span>
			</label>
		<?php echo form::select(array('p_home_page_item['.$aLanguage['code'].']','p_home_page_item_'.$aLanguage['code']), $aPageData['home_page_items'], (isset($aPageData['values']['home_page']['item'][$aLanguage['code']]) ? $aPageData['values']['home_page']['item'][$aLanguage['code']] : '')) ?></p>

		<p class="field col" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_home_page_details_<?php echo $aLanguage['code'] ?>">
		<?php $okt['languages']->hasUniqueLanguage() ? _e('c_a_config_website_home_page_details') : printf(__('c_a_config_website_home_page_details_in_%s'), $view->escape($aLanguage['title'])); ?><span
				class="lang-switcher-buttons"></span>
			</label>
		<?php echo form::select(array('p_home_page_details['.$aLanguage['code'].']','p_home_page_details_'.$aLanguage['code']), (isset($aPageData['home_page_details'][$aLanguage['code']]) ? $aPageData['home_page_details'][$aLanguage['code']] : []), (isset($aPageData['values']['home_page']['details'][$aLanguage['code']]) ? $aPageData['values']['home_page']['details'][$aLanguage['code']] : '')) ?></p>
	</div>
	<?php endforeach; ?>

<?php endif; ?>

</fieldset>
