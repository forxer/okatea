<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Gestion des permissions utilisateurs
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.permissions');


/* Initialisations
----------------------------------------------------------*/

$aGroups = array();
$aPerms = array();
$sTgroups = $okt->db->prefix.'core_users_groups';

$rsGroups = $okt->db->select('SELECT group_id, title, perms FROM '.$sTgroups);

while ($rsGroups->fetch())
{
	if ($rsGroups->group_id == oktAuth::superadmin_group_id || $rsGroups->group_id == oktAuth::guest_group_id) {
		continue;
	}
	elseif (!$okt->user->is_superadmin && $rsGroups->group_id == oktAuth::admin_group_id) {
		continue;
	}

	$aGroups[$rsGroups->group_id] = $rsGroups->title;
	$aPerms[$rsGroups->group_id] = $rsGroups->perms ? unserialize($rsGroups->perms) : array();
}
unset($rsGroups);


$iNumGroup = count($aGroups);


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['sended_form']))
{
	$perms = $_POST['perms'];

	foreach ($aGroups as $group_id=>$group_title)
	{
		$group_perms = !empty($perms[$group_id]) ? array_keys($perms[$group_id]) : array();
		$group_perms = serialize($group_perms);

		$query =
		'UPDATE '.$sTgroups.' SET '.
			'perms=\''.$okt->db->escapeStr($group_perms).'\' '.
		'WHERE group_id='.(integer)$group_id;

		$okt->db->execute($query);
	}

	$okt->redirect('configuration.php?action=permissions&updated=1');
}


/* Affichage
----------------------------------------------------------*/


$aPermissions = array();

foreach ($okt->getPerms() as $k=>$v)
{
	if (!is_array($v))
	{
		if (!isset($aPermissions['others']))
		{
			$aPermissions['others'] = array(
				'libelle' => '',
				'perms' => array()
			);
		}

		if ($okt->checkPerm($k)) {
			$aPermissions['others']['perms'][$k] = $v;
		}
	}
	else {
		$aPermissions[$k] = array(
			'libelle' => $v['libelle'],
			'perms' => array()
		);

		foreach ($v['perms'] as $perm=>$libelle)
		{
			if ($okt->checkPerm($perm)) {
				$aPermissions[$k]['perms'][$perm] = $libelle;
			}
		}
	}
}

asort($aPermissions);


# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_permissions'));

# Confirmationss
$okt->page->messages->success('updated',__('c_a_config_permissions_updated'));

if ($iNumGroup > 1) {
	$okt->page->tabs();
}

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<h2 class="page_title"></h2>


<form action="configuration.php" method="post">
<div id="tabered">
	<?php if ($iNumGroup > 1) : ?>
	<ul>
		<?php foreach ($aGroups as $group_id=>$group_title) : ?>
		<li><a href="#tab-group-<?php echo $group_id ?>"><span><?php echo html::escapeHTML($group_title) ?></span></a></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>

	<?php foreach ($aGroups as $group_id=>$group_title) : ?>
	<div id="tab-group-<?php echo $group_id ?>">
		<h3><?php printf(__('c_a_config_permissions_group_%s'), html::escapeHTML($group_title)) ?></h3>

		<?php foreach($aPermissions as $group) :
				if (empty($group['perms'])) continue; ?>

			<?php if (!empty($group['libelle'])) : ?>
			<h4><?php echo $group['libelle'] ?></h4>
			<?php endif; ?>

			<ul class="actions">
				<?php foreach ($group['perms'] as $perm=>$libelle) : ?>
				<li><label for="perms_<?php echo $group_id ?>_<?php echo $perm ?>"><?php
				echo form::checkbox(array('perms['.$group_id.']['.$perm.']','perms_'.$group_id.'_'.$perm),1,in_array($perm,$aPerms[$group_id])) ?>
				<?php echo $libelle ?></label></li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>

</div><!-- #tabered -->

	<p><?php echo form::hidden('action','permissions') ?>
	<?php echo form::hidden('sended_form',1) ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_edit') ?>" /></p>
</form>
<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
