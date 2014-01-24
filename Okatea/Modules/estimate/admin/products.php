<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration des produits
 *
 */

use Okatea\Tao\Misc\Utilities;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# chargement des locales
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.products');


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	if ($okt->estimate->products->switchProductStatus($_GET['switch_status']) !== false)
	{
		http::redirect('module.php?m=estimate&action=products');
	}
}

# suppression d'un produit
if (!empty($_GET['delete_product']))
{
	if ($okt->estimate->products->delProduct($_GET['delete_product']) !== false)
	{
		$okt->page->flash->success(__('m_estimate_product_deleted'));

		http::redirect('module.php?m=estimate&action=products');
	}
}


/* Affichage
----------------------------------------------------------*/

# Liste des produits
$rsProducts = $okt->estimate->products->getProducts(array('active'=>2));

if ($okt->estimate->config->enable_accessories)
{
	while ($rsProducts->fetch())
	{
		$rsProducts->accessories = $okt->estimate->accessories->getAccessories(array(
			'product_id' => $rsProducts->id,
			'active' => 2
		));
	}
}

# Titre de la page
$okt->page->addGlobalTitle(__('m_estimate_products'));

# button set
$okt->page->setButtonset('estimateProductsBtSt',array(
	'id' => 'estimate-products-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('m_estimate_add_product'),
			'url' 			=> 'module.php?m=estimate&amp;action=product',
			'ui-icon' 		=> 'plusthick',
		)
	)
));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('estimateProductsBtSt'); ?>

<?php if ($rsProducts->isEmpty()) : ?>
<p><?php _e('m_estimate_no_product') ?></p>

<?php else : ?>

<table class="common">
	<caption><?php _e('m_estimate_products_list') ?></caption>
	<thead><tr>
		<th scope="col"><?php _e('m_estimate_product_title') ?></th>
		<?php if ($okt->estimate->config->enable_accessories) : ?>
		<th scope="col"><?php _e('m_estimate_product_accessories') ?></th>
		<?php endif; ?>
		<th scope="col"><?php _e('c_c_Actions') ?></th>
	</tr></thead>
	<tbody>
	<?php $count_line = 0;
	while ($rsProducts->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd';
		$count_line++;

		if (!$rsProducts->active) {
			$td_class = ' disabled';
		}
	?>
	<tr>
		<th class="<?php echo $td_class ?> fake-td" scope="row"><a href="module.php?m=estimate&amp;action=product&amp;product_id=<?php echo $rsProducts->id ?>"><?php
		echo html::escapeHTML($rsProducts->title) ?></a></th>

		<?php if ($okt->estimate->config->enable_accessories) : ?>
		<td class="<?php echo $td_class ?>">
			<ul>
			<?php while ($rsProducts->accessories->fetch()) : ?>
				<li><?php if ($rsProducts->accessories->active) : ?>
					<?php echo html::escapeHTML($rsProducts->accessories->title) ?>
					<?php else : ?>
					<span class="disabled"><?php echo html::escapeHTML($rsProducts->accessories->title) ?></span>
					<?php endif; ?>
				</li>
			<?php endwhile; ?>
			</ul>
		</td>
		<?php endif; ?>

		<td class="<?php echo $td_class ?> small">
			<ul class="actions">
				<li>
				<?php if ($rsProducts->active) : ?>
				<a href="module.php?m=estimate&amp;action=products&amp;switch_status=<?php echo $rsProducts->id ?>"
				title="<?php printf(__('c_c_action_Hide_%s'), $view->escapeHtmlAttr($rsProducts->title)) ?>"
				class="icon tick"><?php _e('c_c_action_visible')?></a>
				<?php else : ?>
				<a href="module.php?m=estimate&amp;action=products&amp;switch_status=<?php echo $rsProducts->id ?>"
				title="<?php printf(__('c_c_action_Display_%s'), $view->escapeHtmlAttr($rsProducts->title)) ?>"
				class="icon cross"><?php _e('c_c_action_hidden')?></a>
				<?php endif; ?>
				</li>
				<li>
				<a href="module.php?m=estimate&amp;action=product&amp;product_id=<?php echo $rsProducts->id ?>"
				title="<?php printf(__('c_c_action_Edit_%s'), $view->escapeHtmlAttr($rsProducts->title)) ?>"
				class="icon pencil"><?php _e('c_c_action_edit')?></a>
				</li>
				<li>
				<a href="module.php?m=estimate&amp;action=products&amp;delete_product=<?php echo $rsProducts->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(__('m_estimate_estimate_product_delete_confirm')) ?>')"
				title="<?php printf(__('c_c_action_Delete_%s'), $view->escapeHtmlAttr($rsProducts->title)) ?>"
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
