<?php

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Modules\News\Helpers as NewsHelpers;

?>

<h3><?php _e('m_news_post_tab_title_options')?></h3>

<div class="two-cols">
	<p class="field col"><label for="p_date"><?php _e('m_news_post_date') ?></label>
	<?php echo form::text('p_date', 20, 255, (!empty($aPostData['extra']['date']) ? dt::dt2str('%d-%m-%Y', $aPostData['extra']['date']) : ''), 'datepicker') ?>
	<span class="note"><?php _e('m_news_post_date_note') ?></span></p>

	<div class="col">
		<p class="field floatLeftEspace"><label for="p_hours"><?php _e('m_news_post_hour') ?></label>
		<?php echo form::text('p_hours', 2, 2, (!empty($aPostData['extra']['hours']) ? $aPostData['extra']['hours'] : '')) ?></p>

		<p class="field floatLeftEspace"><label for="p_minutes"><?php _e('m_news_post_minute') ?></label>
		<?php echo form::text('p_minutes', 2, 2, (!empty($aPostData['extra']['minutes']) ? $aPostData['extra']['minutes'] : '')) ?></p>

		<div class="clearer"></div>
	</div>
</div>

<div class="two-cols">
	<?php if ($okt->module('News')->config->categories['enable']) : ?>
	<p class="field col"><label for="p_category_id"><?php _e('m_news_post_category')?></label>
	<select id="p_category_id" name="p_category_id">
		<option value="0"><?php _e('m_news_post_category_first_level') ?></option>
		<?php
		while ($rsCategories->fetch())
		{
			echo '<option value="'.$rsCategories->id.'"'.
			($aPostData['post']['category_id'] == $rsCategories->id ? ' selected="selected"' : '').
			'>'.str_repeat('&nbsp;&nbsp;&nbsp;', $rsCategories->level).
			'&bull; '.$view->escape($rsCategories->title).
			'</option>';
		}
		?>
	</select></p>
	<?php endif; ?>

	<?php # si les permissions de groupe sont activées
	if ($okt->module('News')->config->enable_group_perms) : ?>
	<div class="col">
		<p><?php _e('m_news_post_permissions_group')?></p>

		<ul class="checklist">
			<?php foreach ($aGroups as $g_id=>$g_title) : ?>
			<li><label><?php echo form::checkbox(array('perms[]','perm_g_'.$g_id), $g_id, in_array($g_id, (array)$aPostData['perms'])) ?> <?php echo $g_title ?></label></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
</div>

<div class="two-cols">

	<?php if (!empty($aPostData['post']['id'])) : ?>

		<?php if ($aPostData['post']['active'] == 3) : ?>
			<?php if ($aPermissions['bCanPublish']) : ?>
				<p class="field col"><label for="p_active"><?php _e('m_news_post_status') ?></label>
				<?php echo form::select('p_active', NewsHelpers::getPostsStatus(true), $aPostData['post']['active']) ?></p>
			<?php else : ?>
				<p class="field col"><span class="icon time"></span><?php _e('m_news_post_delayed_publication') ?></p>
			<?php endif; ?>

		<?php elseif ($aPostData['post']['active'] == 2) : ?>

			<?php if ($aPermissions['bCanPublish']) : ?>
				<p class="field col"><a href="<?php echo $view->generateUrl('News_post', array('post_id' => $aPostData['post']['id'])).'?publish=1' ?>"
				class="icon time"><?php _e('m_news_post_publish_post') ?></a></p>
			<?php else : ?>
				<p class="field col"><span class="icon time"></span> <?php _e('m_news_post_awaiting_validation') ?></p>
			<?php endif; ?>

		<?php else : ?>
			<?php if ($aPermissions['bCanPublish']) : ?>
				<p class="field col"><label for="p_active"><?php _e('m_news_post_status') ?></label>
				<?php echo form::select('p_active', NewsHelpers::getPostsStatus(true), $aPostData['post']['active']) ?></p>
			<?php else : ?>
				<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aPostData['post']['active']) ?> <?php _e('c_c_status_Online') ?></label></p>
			<?php endif; ?>
		<?php endif; ?>

	<?php elseif ($aPermissions['bCanPublish']) : ?>
		<p class="field col"><label for="p_active"><?php _e('m_news_post_status') ?></label>
		<?php echo form::select('p_active', NewsHelpers::getPostsStatus(true), $aPostData['post']['active']) ?></p>

	<?php endif; ?>

	<?php if (!empty($okt->module('News')->config->templates['item']['usables'])) : ?>
	<p class="field col"><label for="p_tpl"><?php _e('m_news_post_tpl') ?></label>
	<?php echo form::select('p_tpl', $aTplChoices, $aPostData['post']['tpl'])?></p>
	<?php endif; ?>

	<p class="field col"><label for="p_selected"><?php echo form::checkbox('p_selected', 1, $aPostData['post']['selected']) ?>
	<?php _e('m_news_post_selected') ?></label></p>
</div>
