<?php
/**
 * @ingroup okt_module_catalog
 * @brief Page de gestion des categories
 *
 */

use Okatea\Admin\Page;
use Tao\Forms\Statics\FormElements as form;
use Tao\Forms\Statics\SelectOption;
use Tao\Misc\Utilities;

# Accès direct interdit
if (!defined('ON_MODULE')) die;

# récupération de la liste complète des catégories
$categories_list = $okt->catalog->getCategories(array('active'=>2,'with_count'=>true));

# initialisations ajout
$add_category_name = '';
$add_category_parent = 0;

# initialisation modification
$category_id = !empty($_REQUEST['category_id']) ? intval($_REQUEST['category_id']) : null;
if ($category_id)
{
	# infos de la catégorie à modifier
	$category = $okt->catalog->getCategory($category_id);

	if ($category->isEmpty()) {
		$okt->error->set('La catégorie est introuvable.');
	}

	$edit_category_active = $category->active;
	$edit_category_name = $category->name;
	$edit_category_parent = $category->parent_id;

	# liste des parents possibles
	$childrens = $okt->catalog->getDescendants($category_id,true);
	$edit_allowed_parents = array('Premier niveau'=>0);

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
	$path = $okt->catalog->getPath($category_id,true);
	$edit_category_path = array();
	while ($path->fetch()) {
		$edit_category_path[] = html::escapeHTML($path->name);
	}
	$edit_category_path = implode(' › ',$edit_category_path);
	unset($path);

	# voisines
	$siblings = $okt->catalog->getChildren($edit_category_parent);
}


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	if ($okt->catalog->switchCategoryStatus($_GET['switch_status'])) {
		http::redirect('module.php?m=catalog&action=categories&switched=1');
	}
}

# suppression d’une catégorie
if (!empty($_GET['delete']))
{
	if ($okt->catalog->delCategory(intval($_GET['delete'])))
	{
		$okt->page->flash->success(__('Catégorie supprimée.'));

		http::redirect('module.php?m=catalog&action=categories');
	}
}

# ajout d’une catégorie
if (!empty($_POST['add_category']))
{
	$add_category_name = !empty($_POST['add_category_name']) ? $_POST['add_category_name'] : '';
	$add_category_parent = !empty($_POST['add_category_parent']) ? intval($_POST['add_category_parent']) : 0;

	if (empty($add_category_name)) {
		$okt->error->set('Vous devez saisir un nom.');
	}

	if ($okt->error->isEmpty())
	{
		$add_category_slug = Utilities::strToSlug($add_category_name, false);

		if (($neo_id = $okt->catalog->addCategory(1,$add_category_name,$add_category_slug,$add_category_parent)) !== false)
		{
			$okt->page->flash->success(__('La catégorie a été ajoutée.'));

			http::redirect('module.php?m=catalog&action=categories');
		}
		else {
			$okt->error->set('Impossible d’ajouter la catégorie.');
		}
	}
}

# modification d’une catégorie
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
		$edit_category_slug = Utilities::strToSlug($edit_category_name, false);

		if ($okt->catalog->updCategory($category_id, $edit_category_active, $edit_category_name, $edit_category_slug, $edit_category_parent) !== false)
		{
			$okt->page->flash->success(__('La catégorie a été  mise à jour.'));

			http::redirect('module.php?m=catalog&action=categories');
		}
		else {
			$okt->error->set('Impossible de mettre à jour la catégorie.');
		}
	}
}

# changement de l’ordre des categories voisines
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
		$okt->catalog->updCategoryOrder($id,$ord);
	}

	$okt->catalog->rebuildTree();

	$okt->page->flash->success(__('L’ordre des catégories a été mis à jour.'));

	http::redirect('module.php?m=catalog&action=categories&category_id='.$category_id);
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle('Catégories');

# Validation javascript
$okt->page->validate('add-category-form',array(
	array(
		'id' => 'add_category_name',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	)
));
$okt->page->validate('edit-category-form',array(
	array(
		'id' => 'edit_categorie_name',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	)
));

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
				url: "'.$okt->options->modules_url.'/catalog/service_ordering_categories.php",
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
		title: 'Ajouter une catégorie',
		autoOpen: false,
		width: 450,
		modal: true,
			buttons: {
				'".html::escapeJS(__('c_c_action_add'))."': function() {
					$('#add-category-form').submit();
				},
				'".html::escapeJS(__('c_c_action_cancel'))."': function() {
					$(this).dialog('close');
				}
			}
	});

	$('#categories_forms').dialog({
		title: 'Modifier une catégorie',
		width: 450,
		modal: true,
			buttons: {
				'".html::escapeJS(__('c_c_action_edit'))."': function() {
					$('#edit-category-form').submit();
				},
				'".html::escapeJS(__('c_c_action_cancel'))."': function() {
					$(this).dialog('close');
					window.location = 'module.php?m=catalog&action=categories'
				}
			}
	});
");

# CSS
$okt->page->css->addCSS('
	#ajaxloader {
		float: right;
		display: none;
		background: transparent url('.$okt->options->public_url.'/img/ajax-loader/indicator.gif) no-repeat 0 0;
		width: 16px;
		height: 16px;
	}
');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<h3 id="add_category_button">Ajouter une catégorie</h3>

<div id="add_category">
	<form action="module.php" method="post" id="add-category-form">

		<p class="field"><label for="add_category_name" title="<?php _e('c_c_required_field') ?>" class="required">Nom</label>
		<?php echo form::text('add_category_name', 50, 255, html::escapeHTML($add_category_name)) ?></p>

		<p class="field"><label for="add_category_parent">Parent</label>
		<select id="add_category_parent" name="add_category_parent">
			<option value="0">Premier niveau</option>
			<?php while ($categories_list->fetch()) {
				echo '<option value="'.$categories_list->id.'">'.str_repeat('&nbsp;&nbsp;',$categories_list->level).'&bull; '.html::escapeHTML($categories_list->name).'</option>';
			}
			?>
		</select></p>

		<p><?php echo form::hidden('m','catalog'); ?>
		<?php echo form::hidden('action','categories'); ?>
		<?php echo form::hidden('add_category',1); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="ajouter" /></p>
	</form>
</div><!-- #add_category -->


<h3>Liste des catégories</h3>

<?php if ($categories_list->isEmpty()) : ?>

<p>Il n’y a aucune catégorie pour le moment.</p>

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

	if ($categories_list->num_products > 1) {
		$num_prod = $categories_list->num_products.' produits';
	}
	elseif ($categories_list->num_products == 1) {
		$num_prod = 'un produit';
	}
	else {
		$num_prod = 'aucun produit';
	}

	if ($categories_list->num_total > 0) {
		$num_prod .= ', total : '.$categories_list->num_total;
	}

	if ($categories_list->num_products == 0)
	{
		$delete_link = ' - <a href="module.php?m=catalog&amp;action=categories&amp;delete='.$categories_list->id.'" '.
		'class="icon delete" '.
		'onclick="return window.confirm(\''.html::escapeJS('Etes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.').'\')">Supprimer</a></p>';
	}
	else {
		$delete_link = ' - <span class="disabled icon delete">Supprimer</span>';
	}

	echo '<p><strong>'.html::escapeHTML($categories_list->name).'</strong> - '.$num_prod.'</p>';

	echo '<p>';

	if ($categories_list->active)
	{
		echo '<a href="module.php?m=catalog&amp;action=categories&amp;switch_status='.$categories_list->id.'" '.
		'class="icon tick">visible</a>';
	}
	else {
		echo '<a href="module.php?m=catalog&amp;action=categories&amp;switch_status='.$categories_list->id.'" '.
		'class="icon cross">masquée</a>';
	}

	echo
	' - <a href="module.php?m=catalog&amp;action=categories&amp;category_id='.$categories_list->id.'" '.
	'title="Modifier la catégorie '.html::escapeHTML($categories_list->name).'" '.
	'class="icon pencil">Modifier</a>';

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

		<p class="field"><label for="edit_category_name" title="<?php _e('c_c_required_field') ?>" class="required">Nom</label>
		<?php echo form::text('edit_category_name', 50, 255, html::escapeHTML($edit_category_name)) ?></p>

		<p class="field"><label for="edit_category_parent">Parent</label>
		<?php echo form::select('edit_category_parent',$edit_allowed_parents,$edit_category_parent) ?></p>

		<p class="field"><label><?php echo form::checkbox('edit_category_active', 1, $edit_category_active) ?> Active</label></p>

		<p><?php echo form::hidden('m','catalog'); ?>
		<?php echo form::hidden('edit_category',1); ?>
		<?php echo form::hidden(array('action'),'categories'); ?>
		<?php echo form::hidden(array('category_id'),$category_id); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="modifier" /></p>
	</form>

	<?php if (!$siblings->isEmpty()) : ?>
	<div id="ajaxloader"></div>
	<h3>Ordre des catégories voisines</h3>
	<form action="module.php" method="post" id="ordering">
		<ul id="sortable" class="ui-sortable">
		<?php $i = 1;
		while ($siblings->fetch()) : ?>
		<li id="ord_<?php echo $siblings->id; ?>" class="ui-state-default"><label for="order_<?php echo $siblings->id ?>">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
		<?php echo html::escapeHTML($siblings->name) ?></label>
		<?php echo form::text(array('order['.$siblings->id.']','order_'.$siblings->id),5,10,$i++) ?></li>
		<?php endwhile; ?>
		</ul>
		<p><?php echo form::hidden('m','catalog'); ?>
		<?php echo form::hidden('ordered',1); ?>
		<?php echo form::hidden(array('action'),'categories'); ?>
		<?php echo form::hidden(array('category_id'),$category_id); ?>
		<?php echo form::hidden('categories_order',''); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="enregistrer l’ordre" id="save_order" /></p>
	</form>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

