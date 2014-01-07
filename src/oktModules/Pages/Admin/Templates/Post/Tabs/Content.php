<?php

use Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('m_pages_page_tab_title_content') ?></h3>

<?php foreach ($okt->languages->list as $aLanguage) : ?>

<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_pages_page_title') : printf(__('m_pages_page_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['title'])) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_subtitle_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_pages_page_subtitle') : printf(__('m_pages_page_subtitle_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_subtitle['.$aLanguage['code'].']','p_subtitle_'.$aLanguage['code']), 100, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['subtitle'])) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_pages_page_content') : printf(__('m_pages_page_content_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 97, 15, $aPageData['locales'][$aLanguage['code']]['content'],'richTextEditor') ?></p>

<?php endforeach; ?>

