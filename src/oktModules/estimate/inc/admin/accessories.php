<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration des accessoires
 *
 */

# Accès direct interdit
if (!defined('ON_ESTIMATE_MODULE')) die;


# chargement des locales
l10n::set(dirname(__FILE__).'/../../locales/'.$okt->user->language.'/admin.accessories');


/* Initialisations
----------------------------------------------------------*/

$sDo = !empty($_REQUEST['do']) ? $_REQUEST['do'] : null;
$iAccessoryId = !empty($_REQUEST['accessory_id']) ? intval($_REQUEST['accessory_id']) : null;

$aAddParams = $aEditParams = array(
	'active' => 1,
	'title' => '',
	'product_id' => null
);


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	if ($okt->estimate->accessories->switchAccessoryStatus($_GET['switch_status']) !== false) {
		$okt->redirect('module.php?m=estimate&action=accessories&switched=1#tab-list');
	}
}

# ajout d'un accessoire
if ($sDo == 'add')
{
	$aAddParams = array(
		'active' => !empty($_POST['add_active']) ? 1 : 0,
		'title' => !empty($_POST['add_title']) ? $_POST['add_title'] : '',
		'product_id' => !empty($_POST['add_product_id']) ? $_POST['add_product_id'] : null
	);

	if (($iAccessoryId = $okt->estimate->accessories->addAccessory($aAddParams)) !== false) {
		$okt->redirect('module.php?m=estimate&action=accessories&do=edit&accessory_id='.$iAccessoryId.'&added=1#tab-edit');
	}
}

# modification d'un accessoire
if ($sDo == 'edit' && !empty($iAccessoryId))
{
	$rsAccessory = $okt->estimate->accessories->getAccessory($iAccessoryId);

	$aEditParams = array(
		'active' => $rsAccessory->active,
		'title' => $rsAccessory->title,
		'product_id' => $rsAccessory->product_id
	);

	unset($rsAccessory);

	if (!empty($_POST['form_sent']))
	{
		$aEditParams = array(
			'id' => $iAccessoryId,
			'active' => !empty($_POST['edit_active']) ? 1 : 0,
			'title' => !empty($_POST['edit_title']) ? $_POST['edit_title'] : '',
			'product_id' => !empty($_POST['edit_product_id']) ? $_POST['edit_product_id'] : null
		);

		if ($okt->estimate->accessories->updAccessory($aEditParams) !== false) {
			$okt->redirect('module.php?m=estimate&action=accessories&do=edit&accessory_id='.$iAccessoryId.'&edited=1#tab-edit');
		}
	}
}

# suppression d'un accessoire
if ($sDo == 'delete' && $iAccessoryId > 0)
{
	if ($okt->estimate->accessories->delAccessory($iAccessoryId) !== false) {
		$okt->redirect('module.php?m=estimate&action=accessories&deleted=1#tab-list');
	}
}


/* Affichage
----------------------------------------------------------*/

# Liste des accessoires
$rsAccessories = $okt->estimate->accessories->getAccessories(array('active'=>2));

# Liste des produits
$rsProducts = $okt->estimate->products->getProducts(array('active'=>2));
$aProducts = array('&nbsp;'=>null);
while ($rsProducts->fetch()) {
	$aProducts[html::escapeHTML($rsProducts->title)] = $rsProducts->id;
}
unset($rsProducts);

# Titre de la page
$okt->page->addGlobalTitle(__('m_estimate_accessories'));

# Tabs
$okt->page->tabs();

# Validation javascript
$okt->page->validate('add-accessory-form',array(
	array(
		'id' => 'add_title',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	),
	array(
		'id' => 'add_product_id',
		'rules' => array(
			'required: true'
		)
	)
));
$okt->page->validate('edit-accessory-form',array(
	array(
		'id' => 'edit_title',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	),
	array(
		'id' => 'edit_product_id',
		'rules' => array(
			'required: true'
		)
	)
));


# Confirmations
$okt->page->messages->success('added',__('m_estimate_accessory_added'));
$okt->page->messages->success('edited',__('m_estimate_accessory_modified'));
$okt->page->messages->success('deleted',__('m_estimate_accessory_deleted'));



# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div id="tabered">
	<ul>
		<?php if ($sDo == 'edit' && $iAccessoryId > 0) : ?>
		<li><a href="#tab-edit"><span><?php _e('m_estimate_edit_accessory')?></span></a></li>
		<?php endif; ?>
		<li><a href="#tab-list"><span><?php _e('m_estimate_accessories_list')?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('m_estimate_add_accessory')?></span></a></li>
	</ul>

	<?php if ($sDo == 'edit' && $iAccessoryId > 0) : ?>
	<div id="tab-edit">
		<form id="edit-accessory-form" action="module.php" method="post" enctype="multipart/form-data">
			<h3><?php _e('m_estimate_edit_accessory')?></h3>

			<div class="two-cols">
				<p class="field col"><label for="edit_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_accessory_title')?></label>
				<?php echo form::text('edit_title', 40, 255, html::escapeHTML($aEditParams['title'])) ?></p>

				<p class="field col"><label for="edit_product_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_accessory_product')?></label>
				<?php echo form::select('edit_product_id',$aProducts,$aEditParams['product_id'])?></p>

				<p class="field col"><label><?php echo form::checkbox('edit_active', 1, $aEditParams['active']) ?> <?php _e('c_c_action_visible')?></label></p>
			</div>

			<p><?php echo form::hidden('m','estimate'); ?>
			<?php echo form::hidden('action','accessories'); ?>
			<?php echo form::hidden('do','edit'); ?>
			<?php echo form::hidden('accessory_id',$iAccessoryId); ?>
			<?php echo form::hidden('form_sent',1); ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_edit')?>" /></p>
		</form>
	</div><!-- #tab-edit -->
	<?php endif; ?>

	<div id="tab-list">
		<h3><?php _e('m_estimate_accessories_list')?></h3>

		<?php if ($rsAccessories->isEmpty()) : ?>
		<p><?php _e('m_estimate_no_accessory') ?></p>
		<?php else : ?>

		<table class="common">
			<caption><?php _e('m_estimate_accessories_list') ?></caption>
			<thead><tr>
				<th scope="col"><?php _e('m_estimate_accessory_title') ?></th>
				<th scope="col"><?php _e('m_estimate_accessory_product_title') ?></th>
				<th scope="col"><?php _e('c_c_Actions') ?></th>
			</tr></thead>
			<tbody>
			<?php $count_line = 0;
			while ($rsAccessories->fetch()) :
				$td_class = $count_line%2 == 0 ? 'even' : 'odd';
				$count_line++;

				if (!$rsAccessories->active) {
					$td_class = ' disabled';
				}
			?>
			<tr>
				<th class="<?php echo $td_class ?> fake-td" scope="row"><a href="module.php?m=estimate&amp;action=accessories&amp;do=edit&amp;accessory_id=<?php echo $rsAccessories->id ?>#tab-edit"><?php
				echo html::escapeHTML($rsAccessories->title) ?></a></th>

				<td class="<?php echo $td_class ?>">
					<?php echo html::escapeHTML($rsAccessories->product_title) ?>
				</td>

				<td class="<?php echo $td_class ?> small">
					<ul class="actions">
						<li>
						<?php if ($rsAccessories->active) : ?>
						<a href="module.php?m=estimate&amp;action=accessories&amp;switch_status=<?php echo $rsAccessories->id ?>"
						title="<?php printf(__('c_c_action_Hide_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="link_sprite ss_tick"><?php _e('c_c_action_visible')?></a>
						<?php else : ?>
						<a href="module.php?m=estimate&amp;action=accessories&amp;switch_status=<?php echo $rsAccessories->id ?>"
						title="<?php printf(__('c_c_action_Display_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="link_sprite ss_cross"><?php _e('c_c_action_hidden')?></a>
						<?php endif; ?>
						</li>
						<li>
						<a href="module.php?m=estimate&amp;action=accessories&amp;do=edit&amp;accessory_id=<?php echo $rsAccessories->id ?>"
						title="<?php printf(__('c_c_action_Edit_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="link_sprite ss_pencil"><?php _e('c_c_action_edit')?></a>
						</li>
						<li>
						<a href="module.php?m=estimate&amp;action=accessories&amp;do=delete&amp;accessory_id=<?php echo $rsAccessories->id ?>"
						onclick="return window.confirm('<?php echo html::escapeJS(__('m_estimate_estimate_accessory_delete_confirm')) ?>')"
						title="<?php printf(__('c_c_action_Delete_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="link_sprite ss_delete"><?php _e('c_c_action_delete')?></a>
						</li>
					</ul>
				</td>
			</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div><!-- #tab-list -->

	<div id="tab-add">
		<form id="add-accessory-form" action="module.php" method="post" enctype="multipart/form-data">
			<h3><?php _e('m_estimate_add_accessory')?></h3>

			<div class="two-cols">
				<p class="field col"><label for="add_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_accessory_title')?></label>
				<?php echo form::text('add_title', 40, 255, html::escapeHTML($aAddParams['title'])) ?></p>

				<p class="field col"><label for="add_product_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_accessory_product')?></label>
				<?php echo form::select('add_product_id',$aProducts,$aAddParams['product_id'])?></p>

				<p class="field col"><label><?php echo form::checkbox('add_active', 1, $aAddParams['active']) ?> <?php _e('c_c_action_visible')?></label></p>
			</div>

			<p><?php echo form::hidden('m','estimate') ?>
			<?php echo form::hidden('action','accessories') ?>
			<?php echo form::hidden('do','add') ?>
			<?php echo adminPage::formtoken() ?>
			<input type="submit" value="<?php _e('c_c_action_add')?>" /></p>
		</form>
	</div><!-- #tab-add -->

</div><!-- #tabered -->


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
