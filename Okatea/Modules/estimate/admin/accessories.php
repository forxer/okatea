<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration des accessoires
 *
 */

use Okatea\Tao\Misc\Utilities;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


# chargement des locales
$okt->l10n->loadFile(__DIR__.'/../Locales/'.$okt->user->language.'/admin.accessories');


/* Initialisations
----------------------------------------------------------*/

$iProductId = !empty($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : null;

$sRedirectUrl = 'module.php?m=estimate&amp;action=accessories';

if (!empty($iProductId))
{
	if (!$okt->estimate->products->productExists($iProductId))
	{
		$okt->error->set(sprintf(__('m_estimate_product_%s_not_exists'), $iProductId));
		$iProductId = null;
	}
	else {
		$sRedirectUrl = 'module.php?m=estimate&amp;action=product&amp;product_id='.$iProductId;
	}
}


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	if ($okt->estimate->accessories->switchAccessoryStatus($_GET['switch_status']) !== false)
	{
		http::redirect($sRedirectUrl);
	}
}

# suppression d'un accessoire
if (!empty($_GET['delete_accessory']))
{
	if ($okt->estimate->accessories->delAccessory($_GET['delete_accessory']) !== false)
	{
		$okt->page->flash->success(__('m_estimate_accessory_deleted'));

		http::redirect($sRedirectUrl);
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

# button set
$okt->page->setButtonset('estimateAccessoriesBtSt',array(
	'id' => 'estimate-accessories-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('m_estimate_add_accessory'),
			'url' 			=> 'module.php?m=estimate&amp;action=accessory',
			'ui-icon' 		=> 'plusthick',
		)
	)
));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('estimateAccessoriesBtSt'); ?>

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
		<th class="<?php echo $td_class ?> fake-td" scope="row"><a href="module.php?m=estimate&amp;action=accessory&amp;accessory_id=<?php echo $rsAccessories->id ?>"><?php
		echo html::escapeHTML($rsAccessories->title) ?></a></th>

		<td class="<?php echo $td_class ?>">
			<a href="module.php?m=estimate&action=product&amp;product_id=<?php echo $rsAccessories->product_id ?>"><?php echo html::escapeHTML($rsAccessories->product_title) ?></a>
		</td>

		<td class="<?php echo $td_class ?> small">
			<ul class="actions">
				<li>
				<?php if ($rsAccessories->active) : ?>
				<a href="module.php?m=estimate&amp;action=accessories&amp;switch_status=<?php echo $rsAccessories->id ?>"
				title="<?php printf(__('c_c_action_Hide_%s'), $view->escapeHtmlAttr($rsAccessories->title)) ?>"
				class="icon tick"><?php _e('c_c_action_visible')?></a>
				<?php else : ?>
				<a href="module.php?m=estimate&amp;action=accessories&amp;switch_status=<?php echo $rsAccessories->id ?>"
				title="<?php printf(__('c_c_action_Display_%s'), $view->escapeHtmlAttr($rsAccessories->title)) ?>"
				class="icon cross"><?php _e('c_c_action_hidden')?></a>
				<?php endif; ?>
				</li>
				<li>
				<a href="module.php?m=estimate&amp;action=accessory&amp;accessory_id=<?php echo $rsAccessories->id ?>"
				title="<?php printf(__('c_c_action_Edit_%s'), $view->escapeHtmlAttr($rsAccessories->title)) ?>"
				class="icon pencil"><?php _e('c_c_action_edit')?></a>
				</li>
				<li>
				<a href="module.php?m=estimate&amp;action=accessories&amp;delete_accessory=<?php echo $rsAccessories->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(__('m_estimate_estimate_accessory_delete_confirm')) ?>')"
				title="<?php printf(__('c_c_action_Delete_%s'), $view->escapeHtmlAttr($rsAccessories->title)) ?>"
				class="icon delete"><?php _e('c_c_action_delete')?></a>
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
