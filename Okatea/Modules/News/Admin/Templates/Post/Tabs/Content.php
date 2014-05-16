<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('m_news_post_tab_title_content') ?></h3>

<?php foreach ($okt->languages->list as $aLanguage) : ?>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_post_title') : printf(__('m_news_post_title_in_%s'),$aLanguage['title']) ?> <span
		class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, $view->escape($aPostData['locales'][$aLanguage['code']]['title'])) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_subtitle_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_post_subtitle') : printf(__('m_news_post_subtitle_in_%s'),$aLanguage['title']) ?> <span
		class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_subtitle['.$aLanguage['code'].']','p_subtitle_'.$aLanguage['code']), 100, 255, $view->escape($aPostData['locales'][$aLanguage['code']]['subtitle'])) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>">
	<label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_post_content') : printf(__('m_news_post_content_in_%s'),$aLanguage['title']) ?> <span
		class="lang-switcher-buttons"></span></label>
<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 97, 15, $aPostData['locales'][$aLanguage['code']]['content'],'richTextEditor') ?></p>

<?php endforeach; ?>
