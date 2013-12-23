<?php
/**
 * @ingroup okt_module_partners
 * @brief Page de gestion des categories
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;
use Tao\Forms\Statics\SelectOption;

# Accès direct interdit
if (!defined('ON_MODULE')) die;

# récupération de la liste complète des catégories
$categories_list = $okt->partners->getCategories(array('active'=>2, 'language' => $okt->user->language,'with_count'=>true));

$aCategories = array();
while ($categories_list->fetch())
{
	$id = $categories_list->id;
	$aCategories[$id]['active'] = $categories_list->active;
	$aCategories[$id]['ord'] = $categories_list->ord;
	$aCategories[$id]['parent_id'] = $categories_list->parent_id;
	$aCategories[$id]['level'] = $categories_list->level;
	$aCategories[$id]['parent_id'] = $categories_list->parent_id;
	$aCategories[$id]['num_items'] = $categories_list->num_items;
	$aCategories[$id]['num_total'] = $categories_list->num_total;

	$aCategories[$id]['name'] = null;

	$rsCategoryLocales = $okt->partners->getCategoryLocales($id);
	while($rsCategoryLocales->fetch()) {
		if($rsCategoryLocales->language == $okt->user->language) {
			$aCategories[$id]['name'] = $rsCategoryLocales->name;
			break;
		}
	}
	if($aCategories[$id]['name'] == null) {
		$aCategories[$id]['name'] = $categories_list->name;
	}
}

# initialisations ajout
$add_category_name = '';
$add_category_parent = 0;

# initialisation modification
$category_id = !empty($_REQUEST['category_id']) ? intval($_REQUEST['category_id']) : null;
if ($category_id)
{
	# infos de la catégorie à modifier
	foreach ($okt->languages->list as $aLanguage)
	{
		$category = $okt->partners->getCategories(array('id' => $category_id, 'language' => $aLanguage['code']));
		$edit_category_name[$aLanguage['code']] = $category->name;
	}

	$category = $okt->partners->getCategory($category_id);
	$edit_category_active = $category->active;
	$edit_category_parent = $category->parent_id;
	# liste des parents possibles
	$childrens = $okt->partners->getDescendants($category_id,true);
	$edit_allowed_parents = array(__('m_partners_First_level') => 0);

	$p = array();
	while ($childrens->fetch()) {
		$p[$childrens->id] = 1;
	}
	unset($children);

	while ($categories_list->fetch())
	{
		if (!isset($p[$categories_list->id]))
		{
			$edit_allowed_parents[] = new SelectOption(
				str_repeat('&nbsp;&nbsp;',$categories_list->level-1).'&bull; '.html::escapeHTML($categories_list->name),
				$categories_list->id
			);
		}
	}
	unset($p);

	# hiérarchie
	$path = $okt->partners->getPath($category_id,true);
	$edit_category_path = array();
	while ($path->fetch()) {
		$edit_category_path[] = html::escapeHTML($aCategories[$path->id]['name']);
	}
	$edit_category_path = implode(' › ',$edit_category_path);
	unset($path);

	# voisines
	$siblings = $okt->partners->getChildren($edit_category_parent);
}


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	if ($okt->partners->switchCategoryStatus($_GET['switch_status'])) {
		http::redirect('module.php?m=partners&action=categories&switched=1');
	}
}

# suppression d'une catégorie
if (!empty($_GET['delete']))
{
	if ($okt->partners->delCategory(intval($_GET['delete'])))
	{
		$okt->page->flash->success(__('m_partners_category_deleted'));

		http::redirect('module.php?m=partners&action=categories');
	}
}

# ajout d'une catégorie
if (!empty($_POST['add_category']))
{
	$add_category_name = !empty($_POST['add_category_name']) ? $_POST['add_category_name'] : '';
	$add_category_parent = !empty($_POST['add_category_parent']) ? intval($_POST['add_category_parent']) : 0;

	if (empty($add_category_name)) {
		$okt->error->set(__('Vous devez saisir un nom.'));
	}

	if ($okt->error->isEmpty())
	{
		if (($neo_id = $okt->partners->addCategory(1,$add_category_name,$add_category_parent)) !== false)
		{
			$okt->page->flash->success(__('m_partners_category_added'));

			http::redirect('module.php?m=partners&action=categories');
		}
		else {
			$okt->error->set(__('Impossible d’ajouter la catégorie.'));
		}
	}
}

# modification d'une catégorie
if (!empty($_POST['edit_category']) && $category_id)
{
	$edit_category_active = !empty($_POST['edit_category_active']) ? 1 : 0;
	$edit_category_name = !empty($_POST['edit_category_name']) ? $_POST['edit_category_name'] : '';
	$edit_category_parent = !empty($_POST['edit_category_parent']) ? intval($_POST['edit_category_parent']) : 0;

	if (empty($edit_category_name)) {
		$okt->error->set('Vous devez saisir un nom.');
	}

	if ($okt->error->isEmpty())
	{

		if ($okt->partners->updCategory($category_id, $edit_category_active, $edit_category_name, $edit_category_parent) !== false)
		{
			$okt->page->flash->success(__('m_partners_category_edited'));

			http::redirect('module.php?m=partners&action=categories');
		}
		else {
			$okt->error->set('Impossible de mettre à jour la catégorie.');
		}
	}
}

# changement de l'ordre des categories voisines
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
		$okt->partners->updCategoryOrder($id,$ord);
	}

	$okt->partners->rebuildTree();

	$okt->page->flash->success(__('m_partners_category_order_edited'));

	http::redirect('module.php?m=partners&action=categories&category_id='.$category_id);
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_partners_Categories'));

# Validation javascript
$okt->page->validate('add-category-form',array(
	array(
		'id' => '\'add_category_name['.$okt->user->language.']\'',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	)
));
$okt->page->validate('edit-category-form',array(
	array(
		'id' => '\'edit_category_name['.$okt->user->language.']\'',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	)
));

# Tabs
$okt->page->tabs(array(), '.tabered');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('.tabered','.lang-switcher-buttons');
}


# Sortable JS
$okt->page->js->addReady('
	$("#sortable").sortable({
		placeholder: "ui-state-highlight",
		axis: "y",
		revert: true,
		cursor: "move",
		update: function(event, ui) {
			var result = $("#sortable").sortable("serialize");

			$("#ajaxloader").show();

			$.ajax({
				data: result,
				url: "'.OKT_MODULES_URL.'/partners/service_ordering_categories.php",
				success: function(data) {
					$("#ajaxloader").fadeOut(400);
				},
				error: function(data) {
					$("#ajaxloader").fadeOut(400);
				}
			});
		}
	});

	$("#ordering").find("input").hide();
');



# Dialog JS
$okt->page->js->addReady("
	$('#add-category-form :submit').hide();
	$('#edit-category-form :submit').hide();

	$('#add_category_button').button({ icons: {primary:'ui-icon-plusthick'} }).click(function() {
		$('#add_category').dialog('open');
	});

	$('#add_category').dialog({
		title: '".__('m_partners_Add_a_category')."',
		autoOpen: false,
		width: 450,
		modal: true,
			buttons: {
				'".html::escapeJS(__('c_c_action_Add'))."': function() {
					$('#add-category-form').submit();
				},
				'".html::escapeJS(__('c_c_action_Cancel'))."': function() {
					$(this).dialog('close');
				}
			}
	});

	$('#categories_forms').dialog({
		title: '".__('m_partners_Edit_a_category')."',
		width: 450,
		modal: true,
			buttons: {
				'".html::escapeJS(__('c_c_action_Edit'))."': function() {
					$('#edit-category-form').submit();
				},
				'".html::escapeJS(__('c_c_action_Cancel'))."': function() {
					$(this).dialog('close');
					window.location = 'module.php?m=partners&action=categories'
				}
			}
	});
");

# CSS
$okt->page->css->addCSS('
	#ajaxloader {
		float: right;
		display: none;
		background: transparent url('.OKT_PUBLIC_URL.'/img/ajax-loader/indicator.gif) no-repeat 0 0;
		width: 16px;
		height: 16px;
	}
');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<h3 id="add_category_button"><?php _e('m_partners_Add_a_category') ?></h3>

<div id="add_category">
	<form action="module.php" method="post" id="add-category-form">
		<div class="tabered">
			<ul>
				<li><a href="#tab-name"><?php _e('c_c_Name') ?></a></li>
				<li><a href="#tab-parent"><?php _e('m_partners_Relative')?></a></li>
			</ul>
			<div id="tab-name">
				<?php foreach ($okt->languages->list as $aLanguage) :?>
				<p class="field" lang="<?php echo $aLanguage['code']?>"><label for="add_category_name" title="<?php _e('c_c_required_field') ?>" <?php if($aLanguage['code'] == $okt->user->language) :?>class="required" <?php endif;?>><?php _e('c_c_Name') ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('add_category_name['.$aLanguage['code'].']','add_category_name'.$aLanguage['code']), 60, 255) ?></p>
				<?php endforeach;?>
			</div>
			<div id="tab-parent">
				<p class="field"><label for="add_category_parent"><?php __('m_partners_Relative') ?></label>
				<select id="add_category_parent" name="add_category_parent">
					<option value="0"><?php _e('m_partners_First_level') ?></option>
					<?php while ($categories_list->fetch()) {
						echo '<option value="'.$categories_list->id.'">'.str_repeat('&nbsp;&nbsp;',$aCategories[$categories_list->id]['level']).'&bull; '.html::escapeHTML($aCategories[$categories_list->id]['name']).'</option>';
					}
					?>
				</select></p>
			</div>
		</div>
		<p><?php echo form::hidden('m','partners'); ?>
		<?php echo form::hidden('action','categories'); ?>
		<?php echo form::hidden('add_category',1); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="<?php _e('c_c_action_Add') ?>" /></p>
	</form>
</div><!-- #add_category -->


<h3><?php _e('m_partners_Categories_list') ?></h3>

<?php if ($categories_list->isEmpty()) : ?>

<p><?php _e('m_partners_No_categories') ?></p>

<?php else : ?>

<div id="categories_lists">
<?php

$ref_level = $level = $categories_list->level-1;

while ($categories_list->fetch())
{
	$attr = ' id="rub'.$categories_list->id.'"';

	if (!$categories_list->active) {
		$attr .= ' class="disabled"';
	}

	# ouverture niveau
	if ($categories_list->level > $level) {
		echo str_repeat('<ul><li'.$attr.'>', $categories_list->level - $level);
	}
	# fermeture niveau
	elseif ($categories_list->level < $level) {
		echo str_repeat('</li></ul>', -($categories_list->level - $level));
	}

	# nouvelle ligne
	if ($categories_list->level <= $level) {
		echo '</li><li'.$attr.'>';
	}

	if ($categories_list->num_items > 1) {
		$num_items = $categories_list->num_items.' '.__('m_partners_items');
	}
	elseif ($categories_list->num_items == 1) {
		$num_items = __('m_partners_one_item');
	}
	else {
		$num_items = __('m_partners_No_items');
	}

	if ($categories_list->num_total > 0) {
		$num_items .= ', total : '.$categories_list->num_total;
	}

	if ($categories_list->num_items == 0)
	{
		$delete_link = ' - <a href="module.php?m=partners&amp;action=categories&amp;delete='.$categories_list->id.'" '.
		'class="icon delete" '.
		'onclick="return window.confirm(\''.html::escapeJS(__('m_partners_Confirm_category_deletion')).'\')">Supprimer</a></p>';
	}
	else {
		$delete_link = ' - <span class="disabled icon delete">'.__('c_c_action_Delete').'</span>';
	}

	echo '<p><strong>'.html::escapeHTML($aCategories[$categories_list->id]['name']).'</strong> - '.$num_items.'</p>';

	echo '<p>';

	if ($categories_list->active)
	{
		echo '<a href="module.php?m=partners&amp;action=categories&amp;switch_status='.$categories_list->id.'" '.
		'class="icon tick">'.__('m_partners_Visible').'</a>';
	}
	else {
		echo '<a href="module.php?m=partners&amp;action=categories&amp;switch_status='.$categories_list->id.'" '.
		'class="icon cross">'.__('m_partners_Hidden').'</a>';
	}

	echo
	' - <a href="module.php?m=partners&amp;action=categories&amp;category_id='.$categories_list->id.'" '.
	'title="'.__('c_c_action_Edit').' '.html::escapeHTML($categories_list->name).'" '.
	'class="icon pencil">'.__('c_c_action_Edit').'</a>';

	echo $delete_link.'</p>';

	$level = $categories_list->level;
}

if ($ref_level - $level < 0) {
	echo str_repeat('</li></ul>', -($ref_level - $level));
}

?>
</div><!-- #categories_lists  -->
<?php endif; ?>

<?php if ($category_id) : ?>
<div id="categories_forms">

	<h3><?php echo $edit_category_path ?></h3>
	<form action="module.php" method="post" id="edit-category-form">
		<div class="tabered">
			<ul>
				<li><a href="#tab-edit-name"><?php _e('c_c_Name')?></a></li>
				<li><a href="#tab-options"><?php _e('m_partners_Options')?></a></li>
			</ul>
			<div id="tab-edit-name">
				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code']?>"><label for="edit_category_name" title="<?php _e('c_c_required_field') ?>" <?php if($aLanguage['code'] == $okt->user->language) :?>class="required" <?php endif;?>><?php _e('c_c_Name')?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('edit_category_name['.$aLanguage['code'].']', 'edit_category_name'.$aLanguage['code']), 50, 255, html::escapeHTML($edit_category_name[$aLanguage['code']])) ?></p>
				<?php endforeach;?>
			</div>
			<div id="tab-options">
				<p class="field"><label for="edit_category_parent"><?php _e('m_partners_Relative')?></label>
				<?php echo form::select('edit_category_parent',$edit_allowed_parents,$edit_category_parent) ?></p>

				<p class="field"><label><?php echo form::checkbox('edit_category_active', 1, $edit_category_active) ?> Active</label></p>
			</div>
		</div>
		<p><?php echo form::hidden('m','partners'); ?>
		<?php echo form::hidden('edit_category',1); ?>
		<?php echo form::hidden(array('action'),'categories'); ?>
		<?php echo form::hidden(array('category_id'),$category_id); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="<?php _e('c_c_action_Edit')?>" /></p>
	</form>

	<?php if (!$siblings->isEmpty()) : ?>
	<div id="ajaxloader"></div>
	<h3><?php _e('m_partners_Close_categories_order') ?></h3>
	<form action="module.php" method="post" id="ordering">
		<ul id="sortable" class="ui-sortable">
		<?php $i = 1;
		while ($siblings->fetch()) : ?>
		<li id="ord_<?php echo $siblings->id; ?>" class="ui-state-default"><label for="order_<?php echo $siblings->id ?>">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
		<?php echo html::escapeHTML($aCategories[$siblings->id]['name']) ?></label>
		<?php echo form::text(array('order['.$siblings->id.']','order_'.$siblings->id),5,10,$i++) ?></li>
		<?php endwhile; ?>
		</ul>
		<p><?php echo form::hidden('m','partners'); ?>
		<?php echo form::hidden('ordered',1); ?>
		<?php echo form::hidden(array('action'),'categories'); ?>
		<?php echo form::hidden(array('category_id'),$category_id); ?>
		<?php echo form::hidden('categories_order',''); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="<?php _e('c_c_action_save_order') ?>" id="save_order" /></p>
	</form>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
