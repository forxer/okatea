<?php

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Misc\Utilities;

?>

<h3><?php _e('m_pages_page_tab_title_images')?></h3>
<div class="two-cols modal-box">
<?php for ($i=1; $i<=$okt->module('Pages')->config->images['number']; $i++) : ?>
	<div class="col">
		<fieldset>
			<legend><?php printf(__('m_pages_page_image_%s'), $i) ?></legend>

			<p class="field"><label for="p_images_<?php echo $i ?>"><?php printf(__('m_pages_page_image_%s'), $i) ?></label>
			<?php echo form::file('p_images_'.$i) ?></p>

			<?php # il y a une image ?
			if (!empty($aPageData['images'][$i])) :

				# affichage square ou icon ?
				if (isset($aPageData['images'][$i]['min_url'])) {
					$sCurImageUrl = $aPageData['images'][$i]['min_url'];
					$sCurImageAttr = $aPageData['images'][$i]['min_attr'];
				}
				elseif (isset($aPageData['images'][$i]['square_url'])) {
					$sCurImageUrl = $aPageData['images'][$i]['square_url'];
					$sCurImageAttr = $aPageData['images'][$i]['square_attr'];
				}
				else {
					$sCurImageUrl = $okt->options->public_url.'/img/media/image.png';
					$sCurImageAttr = ' width="48" height="48" ';
				}

				$aCurImageAlt = isset($aPageData['images'][$i]['alt']) ? $aPageData['images'][$i]['alt'] : array();
				$aCurImageTitle = isset($aPageData['images'][$i]['title']) ? $aPageData['images'][$i]['title'] : array();

				?>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_title_%s'), $i) : printf(__('m_pages_page_image_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageTitle[$aLanguage['code']]) ? $view->escape($aCurImageTitle[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_alt_text_%s'), $i) : printf(__('m_pages_page_image_alt_text_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageAlt[$aLanguage['code']]) ? $view->escape($aCurImageAlt[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

				<p><a href="<?php echo $aPageData['images'][$i]['img_url']?>" rel="pages_images"
				title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_page_image_title_attr_%s'), $aPageData['locales'][$okt->user->language]['title'], $i)) ?>"
				class="modal"><img src="<?php echo $sCurImageUrl ?>"
				<?php echo $sCurImageAttr ?> alt="" /></a></p>

				<p><a href="<?php echo $view->generateUrl('Pages_post', array('page_id' => $aPageData['post']['id'])) ?>?delete_image=<?php echo $i ?>"
				onclick="return window.confirm('<?php echo $view->escapeJs(_e('m_pages_page_delete_image_confirm')) ?>')"
				class="icon delete"><?php _e('m_pages_page_delete_image') ?></a></p>

			<?php else : ?>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_title_%s'), $i) : printf(__('m_pages_page_image_title_%s_in_%s'), $i,$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_alt_text_%s'), $i) : printf(__('m_pages_page_image_alt_text_%s_in_%s'), $i,$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
				<?php endforeach; ?>

			<?php endif; ?>

		</fieldset>
	</div>
<?php endfor; ?>
</div>
<p class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></p>
