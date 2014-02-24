<?php

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Misc\Utilities;

?>

<h3><?php _e('m_news_post_tab_title_files')?></h3>

<div class="two-cols">
<?php for ($i=1; $i<=$okt->module('News')->config->files['number']; $i++) : ?>
	<div class="col">
		<p class="field"><label for="p_files_<?php echo $i ?>"><?php printf(__('m_news_post_file_%s'), $i)?> </label>
		<?php echo form::file('p_files_'.$i) ?></p>

		<?php # il y a un fichier ?
		if (!empty($aPostData['files'][$i])) :

			$aCurFileTitle = isset($aPageData['files'][$i]['title']) ? $aPageData['files'][$i]['title'] : array(); ?>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_file_title_%s'), $i) : printf(__('m_news_post_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurFileTitle[$aLanguage['code']]) ? $view->escape($aCurFileTitle[$aLanguage['code']]) : '')) ?></p>

			<?php endforeach; ?>

			<p><a href="<?php echo $aPostData['files'][$i]['url'] ?>"><img src="<?php echo $okt->options->public_url.'/img/media/'.$aPostData['files'][$i]['type'].'.png' ?>" alt="" /></a>
			<?php echo $aPostData['files'][$i]['type'] ?> (<?php echo $aPostData['files'][$i]['mime'] ?>)
			- <?php echo Utilities::l10nFileSize($aPostData['files'][$i]['size']) ?></p>

			<?php if ($aPermissions['bCanEditPost']) : ?>
			<p><a href="<?php echo $view->generateUrl('News_post', array('post_id' => $aPostData['post']['id'])) ?>?delete_file=<?php echo $i ?>"
			onclick="return window.confirm('<?php echo $view->escapeJs(_e('m_news_post_delete_file_confirm')) ?>')"
			class="icon delete"><?php _e('m_news_post_delete_file')?></a></p>
			<?php endif; ?>

		<?php else : ?>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>
			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_file_title_%s'), $i) : printf(__('m_news_post_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
			<?php endforeach; ?>

		<?php endif; ?>
	</div>
<?php endfor; ?>
</div>

<p class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></p>