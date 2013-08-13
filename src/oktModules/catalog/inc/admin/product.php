<?php
/**
 * @ingroup okt_module_catalog
 * @brief Ajout ou modification d'un produit
 *
 */


# Accès direct interdit
if (!defined('ON_CATALOG_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$product_id = null;

$can_delete = false;

$product_data = array(
	'category_id' => 0,
	'visibility' => 1,
	'title' => '',
	'subtitle' => '',
	'content' => '',
	'content_short' => '',
	'price' => '',
	'price_promo' => '',
	'updated_at' => '',
	'created_at' => '',


	# statut du produit
	'promo' => 0,
	'promo_start' => '',
	'promo_end' => '',

	'nouvo' => 0,
	'nouvo_start' => '',
	'nouvo_end' => '',

	'favo' => 0,
	'favo_start' => '',
	'favo_end' => ''
);

if ($okt->catalog->config->seo_enable)
{
	$product_data['title_tag'] = '';
	$product_data['slug'] = '';
	$product_data['meta_description'] = '';
	$product_data['meta_keywords'] = '';
}


# update product ?
if (!empty($_REQUEST['product_id']))
{
	$product_id = intval($_REQUEST['product_id']);

	$rs_product = $okt->catalog->getProd($product_id);

	if ($rs_product->isEmpty()) {
		$okt->error->set('Le produit #'.$product_id.' n’existe pas.');
	}
	else {
		$product_data = array(
			'category_id' => $rs_product->category_id,
			'visibility' => $rs_product->visibility,
			'title' => $rs_product->title,
			'subtitle' => $rs_product->subtitle,
			'content' => $rs_product->content,
			'content_short' => $rs_product->content_short,
			'price' => $rs_product->price,
			'price_promo' => $rs_product->price_promo,
			'updated_at' => $rs_product->updated_at,
			'created_at' => $rs_product->created_at,

			# statut du produit
			'promo' => $rs_product->promo,
			'promo_start' => $rs_product->promo_start,
			'promo_end' => $rs_product->promo_end,

			'nouvo' => $rs_product->nouvo,
			'nouvo_start' => $rs_product->nouvo_start,
			'nouvo_end' => $rs_product->nouvo_end,

			'favo' => $rs_product->favo,
			'favo_start' => $rs_product->favo_start,
			'favo_end' => $rs_product->favo_end
		);

		if ($okt->catalog->config->seo_enable)
		{
			$product_data['title_tag'] = $rs_product->title_tag;
			$product_data['slug'] = $rs_product->slug;
			$product_data['meta_description'] = $rs_product->meta_description;
			$product_data['meta_keywords'] = $rs_product->meta_keywords;
		}

		if ($okt->catalog->config->images['enable']) {
			$post_images = $rs_product->getImagesInfo();
		}

		if ($okt->catalog->config->files['enable']) {
			$post_files = $rs_product->getFilesInfo();
		}

		$prod_url = $rs_product->getProductUrl();

		$can_edit_product = $rs_product->isEditable();
		$can_delete = $rs_product->isDeletable();
	}

	unset($rs_product);
}


/* Traitements
----------------------------------------------------------*/

# switch product status
if (!empty($_GET['switch_status']) && !empty($product_id))
{
	$okt->catalog->switchProdStatus($product_id);
	$okt->redirect('module.php?m=catalog&action=edit&product_id='.$product_id.'&switched=1');
}

# suppression d'une image
if (!empty($_GET['delete_image']) && !empty($product_id) && $can_edit_product)
{
	$okt->catalog->deleteImage($product_id,$_GET['delete_image']);
	$okt->redirect('module.php?m=catalog&action=edit&product_id='.$product_id.'&edited=1');
}

# suppression d'un fichier
if (!empty($_GET['delete_file']) && !empty($product_id) && $can_edit_product)
{
	$okt->catalog->deleteFile($product_id,$_GET['delete_file']);
	$okt->redirect('module.php?m=catalog&action=edit&product_id='.$product_id.'&edited=1');
}

# add/update product
if (!empty($_POST['sended']))
{
	$product_data = array(
		'id' => $product_id,
		'category_id' => (!empty($_POST['p_category_id']) ? intval($_POST['p_category_id']) : 0),
		'visibility' => (!empty($_POST['p_visibility']) ? intval($_POST['p_visibility']) : $product_data['visibility']),
		'title' => (!empty($_POST['p_title']) ? $_POST['p_title'] : ''),
		'subtitle' => (!empty($_POST['p_subtitle']) ? $_POST['p_subtitle'] : ''),
		'content' => (!empty($_POST['p_content']) ? $_POST['p_content'] : ''),
		'content_short' => (!empty($_POST['p_content_short']) ? $_POST['p_content_short'] : ''),
		'price' => (!empty($_POST['p_price']) ? util::sysNumber($_POST['p_price']) : null),
		'price_promo' => (!empty($_POST['p_price_promo']) ? util::sysNumber($_POST['p_price_promo']) : null),

		# statut du product
		'promo' => (!empty($_POST['p_promo']) ? intval($_POST['p_promo']) : null),
		'promo_start' => (!empty($_POST['p_promo_start']) ? mysql::formatDateTime($_POST['p_promo_start']) : null),
		'promo_end' => (!empty($_POST['p_promo_end']) ? mysql::formatDateTime($_POST['p_promo_end']) : null),

		'nouvo' => (!empty($_POST['p_nouvo']) ? intval($_POST['p_nouvo']) : null),
		'nouvo_start' => (!empty($_POST['p_nouvo_start']) ? mysql::formatDateTime($_POST['p_nouvo_start']) : null),
		'nouvo_end' => (!empty($_POST['p_nouvo_end']) ? mysql::formatDateTime($_POST['p_nouvo_end']) : null),

		'favo' => (!empty($_POST['p_favo']) ? intval($_POST['p_favo']) : null),
		'favo_start' => (!empty($_POST['p_favo_start']) ? mysql::formatDateTime($_POST['p_favo_start']) : null),
		'favo_end' => (!empty($_POST['p_favo_end']) ? mysql::formatDateTime($_POST['p_favo_end']) : null)
	);

	if ($okt->catalog->config->seo_enable)
	{
		$product_data['title_tag'] = (!empty($_POST['p_title_tag']) ? $_POST['p_title_tag'] : '');
		$product_data['slug'] = (!empty($_POST['p_slug']) ? $_POST['p_slug'] : '');
		$product_data['meta_description'] = (!empty($_POST['p_meta_description']) ? $_POST['p_meta_description'] : '');
		$product_data['meta_keywords'] = (!empty($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : '');
	}

	# add or update post
	if ($okt->catalog->checkProdData($product_data))
	{
		$cursor = $okt->catalog->openCursor($product_data);

		# update product
		if (!empty($product_id))
		{
			# -- CORE TRIGGER : moduleNewsBeforeProdUpdate
			$okt->triggers->callTrigger('moduleNewsBeforeProdUpdate',$cursor,$product_id);

			if ($okt->catalog->updProd($product_id, $cursor) !== false)
			{
				# -- CORE TRIGGER : moduleNewsAfterProdUpdate
				$okt->triggers->callTrigger('moduleNewsAfterProdUpdate',$cursor,$product_id);

				$okt->redirect('module.php?m=catalog&action=edit&product_id='.$product_id.'&updated=1');
			}
		}
		# add product
		else
		{
			# -- CORE TRIGGER : moduleNewsBeforeProdCreate
			$okt->triggers->callTrigger('moduleNewsBeforeProdCreate',$cursor);

			if (($product_id = $okt->catalog->addProd($cursor)) !== false)
			{
				# -- CORE TRIGGER : moduleNewsAfterProdCreate
				$okt->triggers->callTrigger('moduleNewsAfterProdCreate',$cursor);

				$okt->redirect('module.php?m=catalog&action=edit&product_id='.$product_id.'&added=1');
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# bouton retour
$okt->page->addButton('catalogBtSt',array(
	'permission' 	=> true,
	'title' 		=> 'Retour',
	'url' 			=> 'module.php?m=catalog&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');


# récupération de la liste complète des catégories
if ($okt->catalog->config->categories_enable) {
	$categories_list = $okt->catalog->getCategories(array('active'=>2));
}

# add product
if (empty($product_id)) {
	$okt->page->addGlobalTitle('Ajouter un produit');
}
# update product
else
{
	$okt->page->addGlobalTitle('Modifier un produit');

	# bouton switch statut si publié
	$okt->page->addButton('catalogBtSt',array(
		'permission' 	=> ($product_data['visibility'] <= 1 ? true : false),
		'title' 		=> ($product_data['visibility'] ? 'En-ligne' : 'Hors-ligne'),
		'url' 			=> 'module.php?m=catalog&amp;action=edit&amp;product_id='.$product_id.'&amp;switch_status=1',
		'ui-icon' 		=> ($product_data['visibility'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $product_data['visibility'],
	));
	# bouton publier si autorisé
	$okt->page->addButton('catalogBtSt',array(
		'permission' 	=> ($product_data['visibility'] == 2 ? true : false),
		'title' 		=> 'Publier',
		'url' 			=> 'module.php?m=catalog&amp;action=edit&amp;product_id='.$product_id.'&amp;publish=1',
		'ui-icon' 		=> 'clock'
	));
	# bouton de suppression si autorisé
	$okt->page->addButton('catalogBtSt',array(
		'permission' 	=> $can_delete,
		'title' 		=> 'Supprimer',
		'url' 			=> 'module.php?m=catalog&amp;action=delete&amp;product_id='.$product_id,
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS('Etes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.').'\')',
	));
	# bouton vers le produit si visible
	$okt->page->addButton('catalogBtSt',array(
		'permission' 	=> ($okt->catalog->config->enable_show_link && $product_data['visibility'] == 1 ? true : false),
		'title' 		=> 'Voir',
		'url' 			=> $prod_url,
		'ui-icon' 		=> 'extlink'
	));


	$okt->page->messages->success('added','Le produit a été ajouté.');
	$okt->page->messages->success('updated','Le produit a été mis à jour.');
}


# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->catalog->config->lightbox_type);

# Datepicker
$okt->page->datePicker();

# RTE
$okt->page->applyRte($okt->catalog->config->rte_enable,'#p_content');

# Réglages de la validation javascript
$aValidateFieldsJs = array();

$aValidateFieldsJs[] = array(
	'id' => 'p_title',
	'rules' => array(
		'required: true',
		'minlength: 3'
	),
	'messages' => 'required: "'.__('m_catalog_please_choose_title').'."'
);

$aValidateFieldsJs[] = array(
	'id' => 'p_content',
	'rules' => array(
		'required: true'
	),
	'messages' => 'required: "'.__('m_catalog_please_choose_content').'."'
);

if ($okt->catalog->config->fields['price']) {
	$aValidateFieldsJs[] = array(
		'id' => 'p_price',
		'rules' => array(
			'required: '.($okt->catalog->config->fields['price'] == 2 ? 'true' : 'false'),
			'number: true'
		),
		'messages' => 'required: "'.__('m_catalog_please_choose_price').'."'
	);
}

if ($okt->catalog->config->fields['subtitle']) {
	$aValidateFieldsJs[] = array(
		'id' => 'p_subtitle',
		'rules' => array(
			'required: '.($okt->catalog->config->fields['subtitle'] == 2 ? 'true' : 'false'),
			'minlength: 3'
		),
		'messages' => 'required: "'.__('m_catalog_please_choose_subtitle').'."'
	);
}

if ($okt->catalog->config->fields['content_short']) {
	$aValidateFieldsJs[] = array(
		'id' => 'p_content_short',
		'rules' => array(
			'required: '.($okt->catalog->config->fields['content_short'] == 2 ? 'true' : 'false')
		),
		'messages' => 'required: "'.__('m_catalog_please_choose_content_short').'."'
	);
}

# Validation javascript
$okt->page->validate('catalog-form',$aValidateFieldsJs);



# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php # buttons set
echo $okt->page->getButtonSet('catalogBtSt'); ?>

<?php if (!empty($product_id)) : ?>
<p>Produit ajouté le <em><?php echo dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$product_data['created_at']) ?></em>.
	<?php if ($product_data['updated_at'] > $product_data['created_at']) : ?>
	<span class="note">Dernière modification le <em><?php echo dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$product_data['updated_at']) ?></em>.</span>
	<?php endif; ?>
</p>
<?php endif; ?>

<form id="catalog-form" action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab-content"><span>Description</span></a></li>
			<li><a href="#tab-options"><span>Options</span></a></li>
			<?php if ($okt->catalog->config->images['enable']) : ?>
			<li><a href="#tab_images"><span>Images</span></a></li>
			<?php endif; ?>
			<?php if ($okt->catalog->config->files['enable']) : ?>
			<li><a href="#tab_files"><span>Fichiers</span></a></li>
			<?php endif; ?>
			<?php if ($okt->catalog->config->seo_enable) : ?>
			<li><a href="#tab-seo"><span>Référencement</span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab-content">
			<h3>Description du produit</h3>

			<p class="field"><label for="p_title" title="<?php _e('c_c_required_field') ?>" class="required">Intitulé</label>
			<?php echo form::text('p_title', 60, 255, html::escapeHTML($product_data['title'])) ?></p>

			<?php if ($okt->catalog->config->fields['subtitle']) : ?>
				<p class="col field"><label for="p_subtitle" <?php if ($okt->catalog->config->fields['subtitle'] == 2) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif; ?>>Sous-titre</label>
				<?php echo form::text('p_subtitle', 60, 255, html::escapeHTML($product_data['subtitle'])) ?></p>
			<?php endif; ?>

			<?php if ($okt->catalog->config->fields['content_short']) : ?>
				<p class="col field"><label for="p_content_short" <?php if ($okt->catalog->config->fields['content_short'] == 2) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif; ?>>Description rapide</label>
			   <?php echo form::textarea('p_content_short', 57, 5, html::escapeHTML($product_data['content_short'])) ?></p>
			<?php endif; ?>

			<p class="field"><label for="p_content" title="<?php _e('c_c_required_field') ?>" class="required">Description détaillée</label>
			<?php echo form::textarea('p_content', 57, 10, html::escapeHTML($product_data['content'])) ?></p>

			<?php if ($okt->catalog->config->fields['price']) : ?>
				<p class="col field"><label for="p_price" <?php if ($okt->catalog->config->fields['price'] == 2) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif; ?>>Prix (en euros)</label>
				<?php echo form::text('p_price', 10, 255, $product_data['price']) ?></p>
			<?php endif; ?>

		</div><!-- #tab-content -->

		<div id="tab-options">
			<h3>Options du produit</h3>

			<div class="three-cols">
				<?php if ($okt->catalog->config->categories_enable) : ?>
				<p class="field col"><label for="p_category_id">Catégorie</label>
				<select id="p_category_id" name="p_category_id">
					<option value="0">Premier niveau</option>
					<?php
					while ($categories_list->fetch())
					{
						echo '<option value="'.$categories_list->id.'"'.
						($categories_list->id == $product_data['category_id'] ? ' selected="selected"' : '').
						'>'.str_repeat('&nbsp;&nbsp;',$categories_list->level).
						'&bull; '.html::escapeHTML($categories_list->name).
						'</option>';
					}
					?>
				</select></p>
				<?php endif; ?>

			<?php if (!empty($product_id)) : ?>
				<p class="field col"><label for="p_visibility">État</label>
				<?php echo form::select('p_visibility',module_catalog::getProdsStatus(true),$product_data['visibility']) ?></p>
			<?php else : ?>
				<p class="field col"><label for="p_visibility">État</label>
				<?php echo form::select('p_visibility',module_catalog::getProdsStatus(true),$product_data['visibility']) ?></p>
			<?php endif; ?>
			</div>

			<?php if ($okt->catalog->config->fields['promo']) : ?>
			<div class="three-cols">
				<?php if ($okt->catalog->config->fields['promo'] == 1) : ?>
					<p class="col field"><label><?php echo form::checkbox('p_promo', 1, $product_data['promo']) ?>
					<?php _e('m_catalog_action_promo') ?></label></p>

				<?php elseif ($okt->catalog->config->fields['promo'] == 2) : ?>
					<p class="col field"><label for="p_promo_start"><?php _e('m_catalog_tab_status_start_promo') ?></label>
					<?php echo form::text('p_promo_start', 20, 255, ($product_data['promo_start'] ? dt::dt2str('%d-%m-%Y',$product_data['promo_start']) : ''), 'datepicker') ?></p>

					<p class="col field"><label for="p_promo_end"><?php _e('m_catalog_tab_status_end_promo') ?></label>
					<?php echo form::text('p_promo_end', 20, 255, ($product_data['promo_end'] ? dt::dt2str('%d-%m-%Y',$product_data['promo_end']) : ''), 'datepicker') ?></p>
				<?php endif; ?>

					<p class="col field"><label for="p_price_promo"><?php _e('m_catalog_tab_status_promo_price') ?></label>
					<?php echo form::text('p_price_promo', 10, 255, $product_data['price_promo']) ?></p>
			</div>
			<?php endif; ?>

			<?php if ($okt->catalog->config->fields['nouvo']) : ?>
			<div class="three-cols">
				<?php if ($okt->catalog->config->fields['nouvo'] == 1) : ?>
				<p class="col field"><label><?php echo form::checkbox('p_nouvo', 1, $product_data['nouvo']) ?>
				<?php _e('m_catalog_action_nouvo') ?></label></p>

				<?php elseif ($okt->catalog->config->fields['nouvo'] == 2) : ?>
				<p class="col field"><label for="p_nouvo_start"><?php _e('m_catalog_tab_status_start_novelty') ?></label>
				<?php echo form::text('p_nouvo_start', 20, 255, ($product_data['nouvo_start'] ? dt::dt2str('%d-%m-%Y',$product_data['nouvo_start']) : ''), 'datepicker') ?></p>

				<p class="col field"><label for="p_nouvo_end"><?php _e('m_catalog_tab_status_end_novelty') ?></label>
				<?php echo form::text('p_nouvo_end', 20, 255, ($product_data['nouvo_end'] ? dt::dt2str('%d-%m-%Y',$product_data['nouvo_end']) : ''), 'datepicker') ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ($okt->catalog->config->fields['favo']) : ?>
			<div class="three-cols">
				<?php if ($okt->catalog->config->fields['favo'] == 1) : ?>
				<p class="col field"><label><?php echo form::checkbox('p_favo', 1, $product_data['favo']) ?>
				<?php _e('m_catalog_action_favo') ?></label></p>

				<?php elseif ($okt->catalog->config->fields['favo'] == 2) : ?>
				<p class="col field"><label for="p_favo_start"><?php _e('m_catalog_tab_status_start_favo') ?></label>
				<?php echo form::text('p_favo_start', 20, 255, ($product_data['favo_start'] ? dt::dt2str('%d-%m-%Y',$product_data['favo_start']) : ''), 'datepicker') ?></p>

				<p class="col field"><label for="p_favo_end"><?php _e('m_catalog_tab_status_end_favo') ?></label>
				<?php echo form::text('p_favo_end', 20, 255, ($product_data['favo_end'] ? dt::dt2str('%d-%m-%Y',$product_data['favo_end']) : ''), 'datepicker') ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

		</div><!-- #tab-options -->

		<?php if ($okt->catalog->config->images['enable']) : ?>
		<div id="tab_images">
			<h3>Images du produit</h3>
			<div class="two-cols modal-box">
			<?php for ($i=1; $i<=$okt->catalog->config->images['number']; $i++) : ?>
				<div class="col">

					<p class="field"><label for="p_images_<?php echo $i ?>">Image <?php echo $i ?></label>
					<?php echo form::file('p_images_'.$i) ?></p>

					<?php # il y a une image ?
					if (!empty($post_images[$i])) :

						# affichage square ou icon ?
						if (isset($post_images[$i]['square_url'])) {
							$cur_image_url = $post_images[$i]['square_url'];
							$cur_image_attr = $post_images[$i]['square_attr'];
						}
						else {
							$cur_image_url = OKT_COMMON_URL.'/img/media/image.png';
							$cur_image_attr = ' width="48" height="48" ';
						}

						$cur_image_alt = isset($post_images[$i]['alt']) ? $post_images[$i]['alt'] : '';

						?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>">Texte alternatif de l’image <?php echo $i ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,html::escapeHTML($cur_image_alt)) ?></p>

						<p><a href="<?php echo $post_images[$i]['img_url']?>"
						title="<?php echo html::escapeHTML($product_data['title']) ?>, image <?php echo $i ?>"
						rel="catalog_images" class="modal"><img src="<?php echo $cur_image_url ?>"
						<?php echo $cur_image_attr ?> alt="" /></a></p>

						<?php if ($can_edit_product) : ?>
						<p><a href="module.php?m=catalog&amp;action=edit&amp;product_id=<?php
						echo $product_id ?>&amp;delete_image=<?php echo $i ?>"
						class="link_sprite ss_delete">supprimer cette image</a></p>
						<?php endif; ?>

					<?php else : ?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>">Texte alternatif de l’image <?php echo $i ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,'') ?></p>

					<?php endif; ?>
				</div>
			<?php endfor; ?>
			</div>
		</div><!-- #tab_images -->
		<?php endif; ?>

		<?php if ($okt->catalog->config->files['enable']) : ?>
		<div id="tab_files">
			<h3>Fichiers du produit</h3>

			<div class="two-cols">
			<?php for ($i=1; $i<=$okt->catalog->config->files['number']; $i++) : ?>
			<div class="col">
				<p class="field"><label for="p_files_<?php echo $i ?>">Fichier <?php echo $i ?></label>
				<?php echo form::file('p_files_'.$i) ?></p>

				<?php # il y a un fichier ?
				if (!empty($post_files[$i])) : ?>

					<p class="field"><label for="p_files_title_<?php echo $i ?>">Titre fichier <?php echo $i ?></label>
					<?php echo form::text('p_files_title_'.$i,40,255,html::escapeHTML($post_files[$i]['title'])) ?></p>

					<p><a href="<?php echo $post_files[$i]['url'] ?>"><img src="<?php echo OKT_COMMON_URL.'/img/media/'.$post_files[$i]['type'].'.png' ?>" alt="<?php echo html::escapeHTML($post_files[$i]['title']) ?>" /></a>
					<?php echo $post_files[$i]['type'] ?> (<?php echo $post_files[$i]['mime'] ?>)
					- <?php echo util::l10nFileSize($post_files[$i]['size']) ?></p>

					<?php if ($can_edit_product) : ?>
						<p><a href="module.php?m=catalog&amp;action=edit&amp;product_id=<?php
						echo $product_id ?>&amp;delete_file=<?php echo $i ?>"
						onclick="return window.confirm('<?php echo html::escapeJS('Etes-vous sûr de vouloir supprimer ce fichier ? Cette action est irréversible.') ?>')"
						class="link_sprite ss_delete">supprimer ce fichier</a></p>
					<?php endif; ?>

				<?php else : ?>

					<p class="field"><label for="p_files_title_<?php echo $i ?>">Titre fichier <?php echo $i ?></label>
					<?php echo form::text('p_files_title_'.$i, 40, 255) ?></p>

				<?php endif; ?>
			</div>
			<?php endfor; ?>
			</div>
		</div><!-- #tab_files -->
		<?php endif; ?>

		<?php if ($okt->catalog->config->seo_enable) : ?>
		<div id="tab-seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<p class="field"><label for="p_title_tag">Élément title du produit</label>
			<?php echo form::text('p_title_tag', 60, 255, html::escapeHTML($product_data['title_tag'])) ?></p>

			<p class="field"><label for="p_meta_description">Meta description</label>
			<?php echo form::text('p_meta_description', 60, 255, html::escapeHTML($product_data['meta_description'])) ?></p>

			<p class="field"><label for="p_meta_keywords">Meta mots clés</label>
			<?php echo form::textarea('p_meta_keywords', 57, 5, html::escapeHTML($product_data['meta_keywords'])) ?></p>

			<div class="lockable">
				<p class="field"><label for="p_slug">URL</label>
				<?php echo form::text('p_slug', 60, 255, html::escapeHTML($product_data['slug'])) ?>
				<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
			</div>

		</div><!-- #tab-seo -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','catalog') ?>
	<?php echo form::hidden('action', !empty($product_id) ? 'edit' : 'add') ?>
	<?php echo !empty($product_id) ? form::hidden('product_id',$product_id) : ''; ?>
	<?php echo form::hidden('sended',1) ?>
	<?php echo adminPage::formtoken() ?>
	<input type="submit" value="<?php echo !empty($product_id) ? 'modifier' : 'ajouter'; ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
