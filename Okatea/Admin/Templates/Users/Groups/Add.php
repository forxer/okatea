<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateUrl('Users_index'));
$okt->page->addGlobalTitle(__('c_a_menu_users_groups'), $view->generateUrl('Users_groups'));
$okt->page->addGlobalTitle(__('c_a_users_add_group'));


# button set
$okt->page->setButtonset('usersGroups', array(
	'id' => 'users-groups-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title'     => __('c_c_action_Go_back'),
			'url'       => $view->generateUrl('Users_groups'),
			'ui-icon'   => 'arrowreturnthick-1-w'
		)
	)
));

# Tabs
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#group-form', '.lang-switcher-buttons');
}

?>

<?php echo $okt->page->getButtonSet('usersGroups'); ?>

<form action="<?php echo $view->generateUrl('Users_groups_add') ?>" method="post" id="group-form">
	<div id="tabered">
		<ul>
			<li><a href="#tab-definition"><span><?php _e('c_a_users_groups_definition') ?></span></a></li>
			<li><a href="#tab-permissions"><span><?php _e('c_a_users_groups_permissions') ?></span></a></li>
		</ul>

		<div id="tab-definition">
			<h3><?php _e('c_a_users_groups_definition') ?></h3>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>
			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_users_groups_title') : printf(__('c_a_users_groups_title_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, $view->escape($aGroupData['locales'][$aLanguage['code']]['title'])) ?></p>
		<?php endforeach; ?>
		</div><!-- #tab-definition -->

		<div id="tab-permissions">
			<h3><?php _e('c_a_users_groups_permissions') ?></h3>

			<?php foreach($aPermissions as $group) :
				if (empty($group['perms'])) continue; ?>

				<?php if (!empty($group['libelle'])) : ?>
				<h4><?php echo $group['libelle'] ?></h4>
				<?php endif; ?>

				<ul class="checklist">
					<?php foreach ($group['perms'] as $perm => $libelle) : ?>
					<li><label for="perms_<?php echo $perm ?>"><?php
					echo form::checkbox(array('perms['.$perm.']', 'perms_'.$perm), 1, in_array($perm, $aGroupData['perms'])) ?>
					<?php echo $libelle ?></label></li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>

		</div><!-- #tab-permissions -->

	</div><!-- #tabered -->

	<p><?php echo $okt->page->formtoken(); ?>
	<?php echo form::hidden('form_sent', 1)?>
	<input type="submit" value="<?php _e('c_c_action_Add') ?>" /></p>
</form>
