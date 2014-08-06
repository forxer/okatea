<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('c_a_config_tab_seo') ?></h3>

<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php _e('c_a_config_title_tag') ?><span
		class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, (isset($aPageData['values']['title_tag'][$aLanguage['code']]) ? $view->escape($aPageData['values']['title_tag'][$aLanguage['code']]) : ''))?>
<span class="note"><?php _e('c_a_config_title_tag_note') ?></span>
</p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_desc') ?><span
		class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($aPageData['values']['meta_description'][$aLanguage['code']]) ? $view->escape($aPageData['values']['meta_description'][$aLanguage['code']]) : '')) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_keywords') ?><span
		class="lang-switcher-buttons"></span></label>
<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($aPageData['values']['meta_keywords'][$aLanguage['code']]) ? $view->escape($aPageData['values']['meta_keywords'][$aLanguage['code']]) : '')) ?></p>

<?php endforeach; ?>
