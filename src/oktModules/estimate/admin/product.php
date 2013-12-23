<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration d'un produit
 *
 */

use Tao\Admin\Page;
use Tao\Misc\Utilities as util;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# chargement des locales
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.products');
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.accessories');

$iProductId = null;

$aProductData = array(
	'active' => 1,
	'title' => ''
);

# update product ?
if (!empty($_REQUEST['product_id']))
{
	$iProductId = intval($_REQUEST['product_id']);

	$rsProduct = $okt->estimate->products->getProduct($iProductId);

	if ($rsProduct->isEmpty())
	{
		$okt->error->set(sprintf(__('m_estimate_product_%s_not_exists'), $iProductId));
		$iProductId = null;
	}
	else
	{
		$aProductData = array(
			'active' => $rsProduct->active,
			'title' => $rsProduct->title
		);

		if ($okt->estimate->config->enable_accessories)
		{
			$rsAccessories = $okt->estimate->accessories->getAccessories(array(
				'active' => 2,
				'product_id' => $rsProduct->id,
			));
		}
	}
}


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']) && !empty($iProductId))
{
	if ($okt->estimate->products->switchProductStatus($iProductId) !== false)
	{
		http::redirect('module.php?m=estimate&action=product&product_id='.$iProductId);
	}
}

#  ajout / modifications d'un produit
if (!empty($_POST['form_sent']))
{
	$aProductData = array(
		'title' => !empty($_POST['p_title']) ? $_POST['p_title'] : '',
		'active' => !empty($_POST['p_active']) ? 1 : 0
	);

	# update product
	if (!empty($iProductId))
	{
		$aProductData['id'] = $iProductId;

		if ($okt->estimate->products->updProduct($aProductData) !== false)
		{
			# log admin
			$okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'estimate',
				'message' => 'product #'.$iProductId
			));

			$okt->page->flash->success(__('m_estimate_product_modified'));

			http::redirect('module.php?m=estimate&action=product&product_id='.$iProductId);
		}
	}

	# add product
	else
	{
		if (($iProductId = $okt->estimate->products->addProduct($aProductData)) !== false)
		{
			# log admin
			$okt->logAdmin->info(array(
				'code' => 40,
				'component' => 'estimate',
				'message' => 'product #'.$iProductId
			));

			$okt->page->flash->success(__('m_estimate_product_added'));

			http::redirect('module.php?m=estimate&action=product&product_id='.$iProductId);
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_estimate_products'), 'module.php?m=estimate&amp;action=products');

if (!empty($iProductId)) {
	$okt->page->addGlobalTitle(__('m_estimate_edit_product'));
}
else {
	$okt->page->addGlobalTitle(__('m_estimate_add_product'));
}

# button set
$okt->page->setButtonset('estimateProductBtSt', array(
	'id' => 'estimate-product-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'module.php?m=estimate&amp;action=products',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));

if ($iProductId)
{
	$okt->page->addButton('estimateProductBtSt', array(
		'permission' => true,
		'title' => __('m_estimate_add_product'),
		'url' => 'module.php?m=estimate&amp;action=product',
		'ui-icon' => 'plusthick'
	));
	$okt->page->addButton('estimateProductBtSt', array(
		'permission' => $okt->estimate->config->enable_accessories,
		'title' => __('m_estimate_add_accessory'),
		'url' => 'module.php?m=estimate&amp;action=accessory&amp;product_id='.$iProductId,
		'ui-icon' => 'plus'
	));
	$okt->page->addButton('estimateProductBtSt', array(
		'permission' 	=> true,
		'title' 		=> ($aProductData['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=estimate&amp;action=product&amp;product_id='.$iProductId.'&amp;switch_status=1',
		'ui-icon' 		=> ($aProductData['active'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aProductData['active'],
	));
	$okt->page->addButton('estimateProductBtSt',array(
		'permission' 	=> true,
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> 'module.php?m=estimate&amp;action=products&amp;delete_product='.$iProductId,
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_estimate_estimate_product_delete_confirm')).'\')',
	));
}


# Validation javascript
$okt->page->validate('add-product-form',array(
	array(
		'id' => 'p_title',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	)
));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('estimateProductBtSt'); ?>

<form id="product-form" action="module.php" method="post" enctype="multipart/form-data">

	<div class="two-cols">
		<p class="field col"><label for="p_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_product_title')?></label>
		<?php echo form::text('p_title', 40, 255, html::escapeHTML($aProductData['title'])) ?></p>

		<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aProductData['active']) ?> <?php _e('c_c_action_visible')?></label></p>
	</div>

	<p><?php echo form::hidden('m', 'estimate'); ?>
	<?php echo form::hidden('action', 'product'); ?>
	<?php echo !empty($iProductId) ? form::hidden('product_id', $iProductId) : ''; ?>
	<?php echo form::hidden('form_sent', 1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo (!empty($iProductId) ? __('c_c_action_edit') : __('c_c_action_add')); ?>" /></p>
</form>


<?php if ($okt->estimate->config->enable_accessories && !empty($iProductId)) : ?>

	<h3><?php _e('m_estimate_product_accessories') ?></h3>

	<?php if ($rsAccessories->isEmpty()) : ?>
	<p><?php _e('m_estimate_product_no_accessory') ?></p>
	<?php else : ?>

		<table class="common">
			<caption><?php _e('m_estimate_accessories_list') ?></caption>
			<thead><tr>
				<th scope="col"><?php _e('m_estimate_accessory_title') ?></th>
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
				<th class="<?php echo $td_class ?> fake-td" scope="row"><a href="module.php?m=estimate&amp;action=accessory&amp;accessory_id=<?php echo $rsAccessories->id ?>&amp;product_id=<?php echo $iProductId ?>"><?php
				echo html::escapeHTML($rsAccessories->title) ?></a></th>

				<td class="<?php echo $td_class ?> small">
					<ul class="actions">
						<li>
						<?php if ($rsAccessories->active) : ?>
						<a href="module.php?m=estimate&amp;action=accessories&amp;switch_status=<?php echo $rsAccessories->id ?>&amp;product_id=<?php echo $iProductId ?>"
						title="<?php printf(__('c_c_action_Hide_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="icon tick"><?php _e('c_c_action_visible')?></a>
						<?php else : ?>
						<a href="module.php?m=estimate&amp;action=accessories&amp;switch_status=<?php echo $rsAccessories->id ?>&amp;product_id=<?php echo $iProductId ?>"
						title="<?php printf(__('c_c_action_Display_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="icon cross"><?php _e('c_c_action_hidden')?></a>
						<?php endif; ?>
						</li>
						<li>
						<a href="module.php?m=estimate&amp;action=accessory&amp;accessory_id=<?php echo $rsAccessories->id ?>&amp;product_id=<?php echo $iProductId ?>"
						title="<?php printf(__('c_c_action_Edit_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="icon pencil"><?php _e('c_c_action_edit')?></a>
						</li>
						<li>
						<a href="module.php?m=estimate&amp;action=accessories&amp;delete_accessory=<?php echo $rsAccessories->id ?>&amp;product_id=<?php echo $iProductId ?>"
						onclick="return window.confirm('<?php echo html::escapeJS(__('m_estimate_estimate_accessory_delete_confirm')) ?>')"
						title="<?php printf(__('c_c_action_Delete_%s'), util::escapeAttrHTML($rsAccessories->title)) ?>"
						class="icon delete"><?php _e('c_c_action_delete')?></a>
						</li>
					</ul>
				</td>
			</tr>
			<?php endwhile; ?>
			</tbody>
		</table>

	<?php endif; ?>

<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
