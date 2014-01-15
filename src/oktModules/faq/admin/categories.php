<?php
/**
 * @ingroup okt_module_faq
 * @brief La page de gestion des catégories
 *
 */

use Okatea\Admin\Page;
use Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$do = !empty($_REQUEST['do']) ? $_REQUEST['do'] : null;
$iCategoryId = !empty($_REQUEST['category_id']) ? intval($_REQUEST['category_id']) : null;

$add_title = array();

foreach ($okt->languages->list as $aLanguage) {
	$add_title[$aLanguage['code']] = '';
}

$add_active = 1;


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	$okt->faq->switchCategoryStatus($_GET['switch_status']);
	http::redirect('module.php?m=faq&action=categories&switched=1');
}

# ajout d'une catégorie
if ($do == 'add')
{
	$add_title = !empty($_POST['add_title']) ? array_map('trim',$_POST['add_title']) : array();
	$add_active = !empty($_POST['add_active']) ? 1 : 0;

	$add_params = array(
		'title' => $add_title,
		'active' => $add_active
	);

	$iCategoryId = $okt->faq->addCategory($add_params);

	$okt->page->flash->success(__('m_faq_section_added'));

	http::redirect('module.php?m=faq&action=categories');
}

# modification d'une catégorie
if ($do == 'edit' && $iCategoryId > 0)
{
	$rsCategory = $okt->faq->getCategory($iCategoryId);
	$rsCategoryI18n = $okt->faq->getCategoryI18n($iCategoryId);

	foreach ($okt->languages->list as $aLanguage)
	{
		$edit_title[$aLanguage['code']] = '';

		while ($rsCategoryI18n->fetch())
		{
			if ($rsCategoryI18n->language == $aLanguage['code']) {
				$edit_title[$rsCategoryI18n->language] = $rsCategoryI18n->title;
			}
		}
	}

	$edit_active = $rsCategory->active;

	if (!empty($_POST['form_sent']))
	{
		$edit_title = !empty($_POST['edit_title']) ? array_map('trim',$_POST['edit_title']) : array();
		$edit_active = !empty($_POST['edit_active']) ? 1 : 0;

		$edit_params = array(
			'id' => $iCategoryId,
			'active' => $edit_active,
			'title' => $edit_title
		);

		$okt->faq->updCategory($edit_params);

		$okt->page->flash->success(__('m_faq_section_updated'));

		http::redirect('module.php?m=faq&action=categories');
	}
}

# suppression d'une catégorie
if ($do == 'delete' && $iCategoryId > 0)
{
	$okt->faq->delCategory($iCategoryId);

	$okt->page->flash->success(__('m_faq_section_deleted'));

	http::redirect('module.php?m=faq&action=categories');
}

# changement de l'ordre des langues
$order = array();
if (empty($_POST['categories_order']) && !empty($_POST['order']))
{
	$order = $_POST['order'];
	asort($order);
	$order = array_keys($order);
}
elseif (!empty($_POST['categories_order']))
{
	$order = explode(',',$_POST['categories_order']);
	foreach ($order as $k=>$v) {
		$order[$k] = str_replace('ord_','',$v);
	}
}


if (!empty($_POST['ordered']) && !empty($order))
{
	foreach ($order as $ord=>$id)
	{
		$ord = ((integer) $ord)+1;
		$okt->faq->updCategoryOrder($id,$ord);
	}

	http::redirect('module.php?m=faq&action=categories&neworder=1');
}


/* Affichage
----------------------------------------------------------*/

# Liste des catégories
$rsCategories = $okt->faq->getCategories(array('active'=>2,'language'=>$okt->user->language));

# Titre de la page
$okt->page->addGlobalTitle(__('m_faq_sections'));

# Tabs
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}



# Sortable


# Sortable
$okt->page->js->addReady("
	$('#sortable').sortable({
		placeholder: 'ui-state-highlight',
		axis: 'y',
		revert: true,
		cursor: 'move'
	});

	$('#sortable').find('input').hide();

	$('#save_order').click(function(){
		var result = $('#sortable').sortable('toArray');
		$('#categories_order').val(result);
	});
");


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div id="tabered">
	<ul>
		<?php if ($do == 'edit' && $iCategoryId > 0) : ?>
		<li><a href="#tab-edit"><span><?php _e('m_faq_edit_section') ?></span></a></li>
		<?php endif; ?>
		<li><a href="#tab-list"><span><?php _e('m_faq_section_list') ?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('m_faq_add_section') ?></span></a></li>
	</ul>

<?php if ($do == 'edit' && $iCategoryId > 0) : ?>
<div id="tab-edit">
<form id="edit-cat-form" action="module.php" method="post">
	<h3><?php _e('m_faq_edit_section')?></h3>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>
	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="edit_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_faq_section_intitle') ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('edit_title['.$aLanguage['code'].']','edit_title_'.$aLanguage['code']), 40, 255, html::escapeHTML($edit_title[$aLanguage['code']])) ?></p>

	<?php endforeach; ?>

	<p class="field"><label><?php echo form::checkbox('edit_active', 1, $edit_active) ?> <?php _e('c_c_action_visible') ?></label></p>


	<p><?php echo form::hidden('m','faq') ?>
	<?php echo form::hidden('action','categories') ?>
	<?php echo form::hidden('do','edit'); ?>
	<?php echo form::hidden('category_id',$iCategoryId) ?>
	<?php echo form::hidden('form_sent',1) ?>
	<?php echo Page::formtoken() ?>
	<input type="submit" value="<?php _e('c_c_action_edit') ?>" /></p>
</form>
</div><!-- #tab-edit -->
<?php endif; ?>


<div id="tab-list">
	<h3><?php _e('m_faq_section_list')?></h3>

	<?php if ($rsCategories->isEmpty()) : ?>
	<p><?php _e('m_faq_there_no_section')?></p>
	<?php else : ?>

	<form action="module.php" method="post" id="ordering">
		<ul id="sortable" class="ui-sortable">
		<?php $i = 1;
		while ($rsCategories->fetch()) : ?>
		<li id="ord_<?php echo $rsCategories->id ?>" class="ui-state-default"><label for="order_<?php echo $rsCategories->id ?>">

			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

			<?php echo html::escapeHTML($rsCategories->title) ?></label>

			<?php echo form::text(array('order['.$rsCategories->id.']','order_'.$rsCategories->id),5,10,$i++) ?>

			<?php if ($rsCategories->active) : ?>
			- <a href="module.php?m=faq&amp;action=categories&amp;switch_status=<?php echo $rsCategories->id ?>"
			title="Basculer la visibilité de la catégorie <?php echo html::escapeHTML($rsCategories->title) ?>"
			class="icon tick"><?php _e('c_c_action_visible')?></a>
			<?php else : ?>
			- <a href="module.php?m=faq&amp;action=categories&amp;switch_status=<?php echo $rsCategories->id ?>"
			title="Basculer la visibilité de la catégorie <?php echo html::escapeHTML($rsCategories->title) ?>"
			class="icon cross"><?php _e('c_c_action_hidden')?></a>
			<?php endif; ?>

			- <a href="module.php?m=faq&amp;action=categories&amp;do=edit&amp;category_id=<?php echo $rsCategories->id ?>"
			title="<?php _e('m_faq_edit_this_section')?> <?php echo html::escapeHTML($rsCategories->title) ?>"
			class="icon pencil"><?php _e('c_c_action_edit')?></a>

			- <a href="module.php?m=faq&amp;action=categories&amp;do=delete&amp;category_id=<?php echo $rsCategories->id ?>"
			onclick="return window.confirm('<?php echo html::escapeJS(__('m_faq_section_delete_confirm')) ?>')"
			title="<?php _e('m_faq_delete_section')?> <?php echo html::escapeHTML($rsCategories->title) ?>"
			class="icon delete"><?php _e('c_c_action_delete')?></a>

			</li>
		<?php endwhile; ?>
		</ul>
		<p><?php echo form::hidden('m','faq') ?>
		<?php echo form::hidden('action','categories') ?>
		<?php echo form::hidden('ordered',1); ?>
		<?php echo form::hidden('categories_order',''); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" /></p>
	</form>
	<?php endif; ?>
</div><!-- #tab-list -->

<div id="tab-add">
	<form id="add-cat-form" action="module.php" method="post">
		<h3><?php _e('m_faq_add_section')?></h3>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>
		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="add_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_faq_section_intitle') ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('add_title['.$aLanguage['code'].']','add_title_'.$aLanguage['code']), 40, 255, html::escapeHTML($add_title[$aLanguage['code']])) ?></p>

		<?php endforeach; ?>

		<p class="field"><label><?php echo form::checkbox('add_active', 1, $add_active) ?> <?php _e('c_c_action_visible') ?></label></p>

		<p><?php echo form::hidden('m','faq'); ?>
		<?php echo form::hidden('action','categories'); ?>
		<?php echo form::hidden('do','add'); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="<?php _e('c_c_action_add')?>" /></p>
	</form>
</div><!-- #tab-add -->

</div><!-- #tabered -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
