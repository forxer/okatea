<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('m_pages_page_tab_title_options')?></h3>

<div class="two-cols">
	<?php if ($okt->module('Pages')->config->categories['enable']) : ?>
	<p class="field col">
		<label for="p_category_id"><?php _e('m_pages_page_category')?></label>
		<select id="p_category_id" name="p_category_id">
			<option value="0"><?php _e('m_pages_page_category_first_level') ?></option>
		<?php
		while ($rsCategories->fetch())
		{
			echo '<option value="' . $rsCategories->id . '"' . ($aPageData['post']['category_id'] == $rsCategories->id ? ' selected="selected"' : '') . '>' . str_repeat('&nbsp;&nbsp;&nbsp;', $rsCategories->level) . '&bull; ' . $view->escape($rsCategories->title) . '</option>';
		}
		?>
	</select>
	</p>
	<?php endif; ?>

	<p class="field col">
		<label><?php echo form::checkbox('p_active', 1, $aPageData['post']['active']) ?> <?php _e('c_c_status_Online') ?></label>
	</p>

	<?php if (!empty($okt->module('Pages')->config->templates['item']['usables'])) : ?>
	<p class="field col">
		<label for="p_tpl"><?php _e('m_pages_page_tpl') ?></label>
	<?php echo form::select('p_tpl', $aTplChoices, $aPageData['post']['tpl'])?></p>
	<?php endif; ?>

	<?php
	# si les permissions de groupe sont activées
	if ($okt->module('Pages')->config->enable_group_perms)
	:
		?>
	<div class="col">
		<p><?php _e('m_pages_page_permissions_group')?></p>

		<ul class="checklist">
			<?php foreach ($aGroups as $g_id=>$g_title) : ?>
			<li><label><?php echo form::checkbox(array('perms[]','perm_g_'.$g_id), $g_id, in_array($g_id, (array)$aPageData['perms'])) ?> <?php echo $g_title ?></label></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
</div>
