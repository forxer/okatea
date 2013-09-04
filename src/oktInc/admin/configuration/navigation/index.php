<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration des menus de navigation
 *
 * @addtogroup Okatea
 *
 */

# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	if ($okt->navigation->switchMenuStatus($_GET['switch_status']) !== false) {
		$okt->redirect('configuration.php?action=navigation&do=index&switched=1');
	}
}

# suppression d'un menu
if (!empty($_GET['delete_menu']))
{
	if ($okt->navigation->delMenu($_GET['delete_menu']) !== false) {
		$okt->redirect('configuration.php?action=navigation&do=index&deleted=1');
	}
}


/* Affichage
----------------------------------------------------------*/

$rsMenus = $okt->navigation->getMenus(array('active'=>2));

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'menu-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_a_config_navigation_add_menu'),
			'url' 			=> 'configuration.php?action=navigation&amp;do=menu',
			'ui-icon' 		=> 'plusthick',
		)
	)
));

# Confirmations
$okt->page->messages->success('switched',__('c_a_config_navigation_menu_switched'));
$okt->page->messages->success('deleted',__('c_a_config_navigation_menu_deleted'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<?php if ($rsMenus->isEmpty()) : ?>
<p><?php _e('c_a_config_navigation_no_menu') ?></p>

<?php else : ?>

<table class="common">
	<caption><?php _e('c_a_config_navigation_menus_list') ?></caption>
	<thead><tr>
		<th scope="col"><?php _e('c_a_config_navigation_menu_title') ?></th>
		<th scope="col"><?php _e('c_a_config_navigation_menu_items') ?></th>
		<th scope="col"><?php _e('c_c_Actions') ?></th>
	</tr></thead>
	<tbody>
	<?php $count_line = 0;
	while ($rsMenus->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd';
		$count_line++;

		if (!$rsMenus->active) {
			$td_class = ' disabled';
		}
	?>
	<tr>
		<th class="<?php echo $td_class ?> fake-td" scope="row"><a href="configuration.php?action=navigation&amp;do=menu&amp;menu_id=<?php echo $rsMenus->id ?>"><?php
		echo html::escapeHTML($rsMenus->title) ?></a></th>

		<td class="<?php echo $td_class ?>">
		
		</td>

		<td class="<?php echo $td_class ?> small nowrap">
			<ul class="actions">
				<li>
				<a href="configuration.php?action=navigation&amp;do=items&amp;menu_id=<?php echo $rsMenus->id ?>"
				title="<?php printf(__('c_a_config_navigation_manage_items_menu_%s'), util::escapeAttrHTML($rsMenus->title)) ?>"
				class="link_sprite ss_application_view_list"><?php _e('c_a_config_navigation_manage_items')?></a>
				</li>
				<li>
				<?php if ($rsMenus->active) : ?>
				<a href="configuration.php?action=navigation&amp;do=index&amp;switch_status=<?php echo $rsMenus->id ?>"
				title="<?php printf(__('c_c_action_Hide_%s'), util::escapeAttrHTML($rsMenus->title)) ?>"
				class="link_sprite ss_tick"><?php _e('c_c_action_visible')?></a>
				<?php else : ?>
				<a href="configuration.php?action=navigation&amp;do=index&amp;switch_status=<?php echo $rsMenus->id ?>"
				title="<?php printf(__('c_c_action_Display_%s'), util::escapeAttrHTML($rsMenus->title)) ?>"
				class="link_sprite ss_cross"><?php _e('c_c_action_hidden')?></a>
				<?php endif; ?>
				</li>
				<li>
				<a href="configuration.php?action=navigation&amp;do=menu&amp;menu_id=<?php echo $rsMenus->id ?>"
				title="<?php printf(__('c_c_action_Edit_%s'), util::escapeAttrHTML($rsMenus->title)) ?>"
				class="link_sprite ss_pencil"><?php _e('c_c_action_edit')?></a>
				</li>
				<li>
				<a href="configuration.php?action=navigation&amp;do=index&amp;delete_menu=<?php echo $rsMenus->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_config_navigation_menu_delete_confirm')) ?>')"
				title="<?php printf(__('c_c_action_Delete_%s'), util::escapeAttrHTML($rsMenus->title)) ?>"
				class="link_sprite ss_delete"><?php _e('c_c_action_delete')?></a>
				</li>
			</ul>
		</td>
	</tr>
	<?php endwhile; ?>
	</tbody>
</table>
<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
