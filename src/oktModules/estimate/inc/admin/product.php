<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration d'un produit
 *
 */

# Accès direct interdit
if (!defined('ON_ESTIMATE_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# chargement des locales
l10n::set(__DIR__.'/../../locales/'.$okt->user->language.'/admin.products');

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

		$rsAccessories = $okt->estimate->accessories->getAccessories(array(
			'product_id' => $rsProduct->id
		));
	}
}


/* Traitements
----------------------------------------------------------*/

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

			$okt->redirect('module.php?m=estimate&action=product&product_id='.$iProductId.'&updated=1');
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

			$okt->redirect('module.php?m=estimate&action=product&product_id='.$iProductId.'&added=1');
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
	# bouton add cat
	$okt->page->addButton('estimateProductBtSt', array(
		'permission' => true,
		'title' => __('m_estimate_add_product'),
		'url' => 'module.php?m=estimate&amp;action=product',
		'ui-icon' => 'plusthick'
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

# Confirmations
$okt->page->messages->success('added', __('m_estimate_product_added'));
$okt->page->messages->success('edited', __('m_estimate_product_modified'));


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
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php echo (!empty($iProductId) ? __('c_c_action_edit') : __('c_c_action_add')); ?>" /></p>
</form>


<?php if (!empty($iProductId)) : ?>

	<h3><?php _e('m_estimate_product_accessories') ?></h3>

	<?php if ($rsAccessories->isEmpty()) : ?>
	<p><?php _e('m_estimate_product_no_accessory') ?></p>
	<?php else : ?>

	<?php endif; ?>

<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
