<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Users\Groups;

# Tabs
$okt->page->tabs();

# Lang switcher
if (! $okt['languages']->unique)
{
	$okt->page->langSwitcher('#group-form', '.lang-switcher-buttons');
}

?>

<div id="tabered">
	<ul>
		<li><a href="#tab-definition"><span><?php _e('c_a_users_groups_definition') ?></span></a></li>
		<li><a href="#tab-permissions"><span><?php _e('c_a_users_groups_permissions') ?></span></a></li>
	</ul>

	<div id="tab-definition">
		<h3><?php _e('c_a_users_groups_definition') ?></h3>

	<?php foreach ($okt['languages']->list as $aLanguage) : ?>
		<p class="field" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_title_<?php echo $aLanguage['code'] ?>"
				title="<?php _e('c_c_required_field') ?>" class="required"><?php
		$okt['languages']->unique ? _e('c_a_users_groups_title') : printf(__('c_a_users_groups_title_in_%s'), $aLanguage['title'])?> <span
				class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, $view->escape($aGroupData['locales'][$aLanguage['code']]['title'])) ?></p>

		<p class="field" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_description_<?php echo $aLanguage['code'] ?>"><?php
		$okt['languages']->unique ? _e('c_a_users_groups_description') : printf(__('c_a_users_groups_description_in_%s'), $aLanguage['title'])?> <span
				class="lang-switcher-buttons"></span></label>
		<?php echo form::textarea(array('p_description['.$aLanguage['code'].']','p_description_'.$aLanguage['code']), 58, 4, $view->escape($aGroupData['locales'][$aLanguage['code']]['description'])) ?></p>

	<?php endforeach; ?>
	</div>
	<!-- #tab-definition -->

	<div id="tab-permissions">
		<h3><?php _e('c_a_users_groups_permissions') ?></h3>

		<?php if ($iGroupId === Groups::SUPERADMIN) : ?>
			<p>
			<em><?php printf(__('c_a_users_groups_error_permissions_sudo'), $aGroupData['locales'][$okt->user->language]['title'], $iGroupId) ?></em>
		</p>

		<?php elseif ($iGroupId === Groups::GUEST) : ?>
			<p>
			<em><?php printf(__('c_a_users_groups_error_permissions_guest'), $aGroupData['locales'][$okt->user->language]['title'], $iGroupId) ?></em>
		</p>

		<?php else : ?>

		<?php
			
			foreach ($aPermissions as $aPermsGroup)
			:
				
				if (empty($aPermsGroup['perms']))
					continue;
				?>

			<?php if (!empty($aPermsGroup['libelle'])) : ?>
			<h4><?php echo $aPermsGroup['libelle'] ?></h4>
			<?php endif; ?>

			<ul class="checklist">
				<?php foreach ($aPermsGroup['perms'] as $perm => $libelle) : ?>
				<li><label for="perms_<?php echo $perm ?>"><?php
					echo form::checkbox(array(
						'perms[' . $perm . ']',
						'perms_' . $perm
					), 1, in_array($perm, $aGroupData['perms']))?>
				<?php echo $libelle ?></label></li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>

		<?php endif; ?>

	</div>
	<!-- #tab-permissions -->

</div>
<!-- #tabered -->
