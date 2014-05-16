<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('c_c_seo_help') ?></h3>

<?php foreach ($okt->languages->list as $aLanguage) : ?>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_tag') : printf(__('c_c_seo_title_tag_in_%s'),$aLanguage['title']) ?> <span
		class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, $view->escape($aPostData['locales'][$aLanguage['code']]['title_tag'])) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'),$aLanguage['title']) ?> <span
		class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, $view->escape($aPostData['locales'][$aLanguage['code']]['meta_description'])) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_seo') : printf(__('c_c_seo_title_seo_in_%s'),$aLanguage['title']) ?> <span
		class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, $view->escape($aPostData['locales'][$aLanguage['code']]['title_seo'])) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'),$aLanguage['title']) ?> <span
		class="lang-switcher-buttons"></span></label>
<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, $view->escape($aPostData['locales'][$aLanguage['code']]['meta_keywords'])) ?></p>

<div class="lockable" lang="<?php echo $aLanguage['code'] ?>">
	<p class="field">
		<label for="p_slug_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_url') : printf(__('c_c_seo_url_in_%s'),$aLanguage['title']) ?> <span
			class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, $view->escape($aPostData['locales'][$aLanguage['code']]['slug']))?>
	<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span>
	</p>
</div>

<?php endforeach; ?>
