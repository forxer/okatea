<?php
##header##

use Okatea\Admin\Page;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$iItemId = null;

$aItemData = array(
	'visibility' => 1,
	'title' => '',
	'slug' => '',
	'description' => '',
	'created_at' => '',
	'updated_at' => '',
	'title_tag' => '',
	'meta_description' => '',
	'meta_keywords' => ''
);


# update item ?
if (!empty($_REQUEST['item_id']))
{
	$iItemId = intval($_REQUEST['item_id']);

	$rsItem = $okt->##module_id##->getItem($iItemId);

	if ($rsItem->isEmpty())
	{
		$okt->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$iItemId));
		$iItemId = null;
	}
	else
	{
		$aItemData = array(
			'id' => $iItemId,
			'visibility' => $rsItem->visibility,
			'title' => $rsItem->title,
			'slug' => $rsItem->slug,
			'description' => $rsItem->description,
			'created_at' => $rsItem->created_at,
			'updated_at' => $rsItem->updated_at,
			'title_tag' => $rsItem->title_tag,
			'meta_description' => $rsItem->meta_description,
			'meta_keywords' => $rsItem->meta_keywords
		);

		if ($okt->##module_id##->config->images['enable']) {
			$aItemImages = $rsItem->getImagesInfo();
		}

		if ($okt->##module_id##->config->files['enable']) {
			$aItemFiles = $rsItem->getFilesInfo();
		}

		$sItemUrl = $rsItem->getItemUrl();
	}
	unset($rsItem);
}


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']) && !empty($iItemId))
{
	$okt->##module_id##->switchItemStatus($iItemId);
	http::redirect('module.php?m=##module_id##&action=edit&item_id='.$iItemId.'&switched=1');
}

# suppression d'une image
if (!empty($_GET['delete_image']) && !empty($iItemId))
{
	$okt->##module_id##->deleteImage($iItemId,$_GET['delete_image']);
	http::redirect('module.php?m=##module_id##&action=edit&item_id='.$iItemId.'&edited=1');
}

# suppression d'un fichier
if (!empty($_GET['delete_file']) && !empty($iItemId))
{
	$okt->##module_id##->deleteFile($iItemId,$_GET['delete_file']);
	http::redirect('module.php?m=##module_id##&action=edit&item_id='.$iItemId.'&edited=1');
}

#  ajout / modifications d'un élément
if (!empty($_POST['sended']))
{
	$aItemData = array(
		'id' => $iItemId,
		'visibility' => (!empty($_POST['p_visibility']) ? 1 : 0),
		'title' => (!empty($_POST['p_title']) ? $_POST['p_title'] : ''),
		'slug' => (!empty($_POST['p_slug']) ? $_POST['p_slug'] : ''),
		'description' => (!empty($_POST['p_description']) ? $_POST['p_description'] : ''),
		'created_at' => $aItemData['created_at'],
		'updated_at' => $aItemData['updated_at'],
		'title_tag' => (!empty($_POST['p_title_tag']) ? $_POST['p_title_tag'] : ''),
		'meta_description' => (!empty($_POST['p_meta_description']) ? $_POST['p_meta_description'] : ''),
		'meta_keywords' => (!empty($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : '')
	);

	# vérification des données avant modification dans la BDD
	if ($okt->##module_id##->checkPostData($aItemData))
	{
		$cursor = $okt->##module_id##->openCursor($aItemData);

		# update item
		if (!empty($iItemId))
		{
			# -- CORE TRIGGER : module##module_camel_case_id##BeforeItemUpdate
			$okt->triggers->callTrigger('module##module_camel_case_id##BeforeItemUpdate', $cursor, $aItemData, $iItemId);

			if ($okt->##module_id##->updItem($iItemId, $cursor, $aItemData))
			{
				# -- CORE TRIGGER : module##module_camel_case_id##AfterItemUpdate
				$okt->triggers->callTrigger('module##module_camel_case_id##AfterItemUpdate', $cursor, $aItemData, $iItemId);

				$okt->flash->success(__('m_##module_id##_confirm_edited'));

				http::redirect('module.php?m=##module_id##&action=edit&item_id='.$iItemId);
			}
		}

		# add item
		else
		{
			# -- CORE TRIGGER : module##module_camel_case_id##BeforeItemCreate
			$okt->triggers->callTrigger('module##module_camel_case_id##BeforeItemCreate',$cursor, $aItemData);

			if (($iItemId = $okt->##module_id##->addItem($cursor, $aItemData)) !== false)
			{
				# -- CORE TRIGGER : module##module_camel_case_id##AfterItemCreate
				$okt->triggers->callTrigger('module##module_camel_case_id##AfterItemCreate', $cursor, $aItemData, $iItemId);

				$okt->flash->success(__('m_##module_id##_confirm_added'));

				http::redirect('module.php?m=##module_id##&action=edit&item_id='.$iItemId);
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# ajout bouton retour
$okt->page->addButton('##module_id##BtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> 'module.php?m=##module_id##&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# boutons modification élément
if (!empty($iItemId))
{
	$okt->page->addGlobalTitle(__('m_##module_id##_edit_item'));

	# bouton switch statut
	$okt->page->addButton('##module_id##BtSt',array(
		'permission' 	=> true,
		'title' 		=> ($aItemData['visibility'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=##module_id##&amp;action=edit&amp;item_id='.$iItemId.'&amp;switch_status=1',
		'ui-icon' 		=> ($aItemData['visibility'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aItemData['visibility'],
	));

	# bouton de suppression si autorisé
	$okt->page->addButton('##module_id##BtSt',array(
		'permission' 	=> $okt->checkPerm('##module_id##_remove'),
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> 'module.php?m=##module_id##&amp;action=delete&amp;item_id='.$iItemId,
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_##module_id##_confirm_deleting')).'\')',
	));

	# bouton vers l'élément côté public si publié
	$okt->page->addButton('##module_id##BtSt',array(
		'permission' 	=> ($aItemData['visibility'] ? true : false),
		'title' 		=> __('c_c_action_Show'),
		'url' 			=> $sItemUrl,
		'ui-icon' 		=> 'extlink'
	));
}

# boutons ajout élément
else {
	$okt->page->addGlobalTitle(__('m_##module_id##_add_item'));
}


# Lockable
$okt->page->lockable();


# Tabs
$okt->page->tabs();


# Modal
$okt->page->applyLbl($okt->##module_id##->config->lightbox_type);


# RTE
$okt->page->applyRte($okt->##module_id##->config->enable_rte,'#p_description');


# Validation javascript
$okt->page->validate('item-form',array(
	array(
		'id' => 'p_title',
		'rules' => array(
			'required: true',
			'minlength: 3'
		)
	),
	array(
		'id' => 'p_description',
		'rules' => array(
			'required: true'
		)
	)
));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('##module_id##BtSt'); ?>

<?php if (!empty($iItemId)) : ?>
<p><?php printf(__('m_##module_id##_item_added_on_%s'),'<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aItemData['created_at']).'</em>') ?>
	<?php if ($aItemData['updated_at'] > $aItemData['created_at']) : ?>
	<span class="note"><?php printf(__('m_##module_id##_item_updated_%s'),'<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aItemData['updated_at']).'</em>') ?></span>
	<?php endif; ?>
</p>
<?php endif; ?>


<form id="item-form" action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab-content"><span><?php _e('m_##module_id##_content') ?></span></a></li>
			<?php if ($okt->##module_id##->config->images['enable']) : ?>
			<li><a href="#tab_images"><span><?php _e('m_##module_id##_images') ?></span></a></li>
			<?php endif; ?>
			<?php if ($okt->##module_id##->config->files['enable']) : ?>
			<li><a href="#tab_files"><span><?php _e('m_##module_id##_files') ?></span></a></li>
			<?php endif; ?>
			<li><a href="#tab-options"><span><?php _e('m_##module_id##_options') ?></span></a></li>
			<?php if ($okt->##module_id##->config->enable_metas) : ?>
			<li><a href="#tab-seo"><span><?php _e('m_##module_id##_seo') ?></span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab-content">
			<h3><?php _e('m_##module_id##_item_content') ?></h3>

			<p class="field"><label for="p_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_##module_id##_title') ?></label>
			<?php echo form::text('p_title', 60, 255, html::escapeHTML($aItemData['title'])) ?></p>

			<p class="field"><label for="p_description" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_##module_id##_description') ?></label>
			<?php echo form::textarea('p_description', 57, 10, $aItemData['description']) ?></p>
		</div><!-- #tab-content -->


		<?php if ($okt->##module_id##->config->images['enable']) : ?>
		<div id="tab_images">
			<h3><?php _e('m_##module_id##_item_images') ?></h3>
			<div class="two-cols modal-box">
			<?php for ($i=1; $i<=$okt->##module_id##->config->images['number']; $i++) : ?>
				<div class="col">

					<p class="field"><label for="p_images_<?php echo $i ?>"><?php printf(__('m_##module_id##_image_n'), $i) ?></label>
					<?php echo form::file('p_images_'.$i) ?></p>

					<?php # il y a une image ?
					if (!empty($aItemImages[$i])) :

						# affichage square ou icon ?
						if (isset($aItemImages[$i]['square_url'])) {
							$cur_image_url = $aItemImages[$i]['square_url'];
							$cur_image_attr = $aItemImages[$i]['square_attr'];
						}
						else {
							$cur_image_url = $okt->options->public_url.'/img/media/image.png';
							$cur_image_attr = ' width="48" height="48" ';
						}

						$cur_image_alt = isset($aItemImages[$i]['alt']) ? $aItemImages[$i]['alt'] : '';

						?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>"><?php printf(__('m_##module_id##_alt_image_n'), $i) ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,html::escapeHTML($cur_image_alt)) ?></p>

						<p><a href="<?php echo $aItemImages[$i]['img_url']?>"
						rel="item_images" title="<?php echo html::escapeHTML($aItemData['title']) ?>, <?php printf(__('m_##module_id##_image_n'), $i) ?>" class="modal"><img src="<?php echo $cur_image_url ?>"
						<?php echo $cur_image_attr ?> alt="" /></a></p>

						<p><a href="module.php?m=##module_id##&amp;action=edit&amp;item_id=<?php
						echo $iItemId ?>&amp;delete_image=<?php echo $i ?>"
						onclick="return window.confirm('<?php echo html::escapeJS(__('m_##module_id##_confirm_image_deleting')) ?>')"
						class="icon delete"><?php _e('m_##module_id##_delete_this_image') ?></a></p>

					<?php else : ?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>"><?php printf(__('m_##module_id##_alt_image_n'), $i) ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,'') ?></p>

					<?php endif; ?>
				</div>
			<?php endfor; ?>
			</div>
			<p class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></p>
		</div><!-- #tab_images -->
		<?php endif; ?>

		<?php if ($okt->##module_id##->config->files['enable']) : ?>
		<div id="tab_files">
			<h3><?php _e('m_##module_id##_item_files') ?></h3>

			<div class="two-cols">
			<?php for ($i=1; $i<=$okt->##module_id##->config->files['number']; $i++) : ?>
				<div class="col">
				<p class="field"><label for="p_files_<?php echo $i ?>"><?php printf(__('m_##module_id##_file_n'), $i) ?></label>
				<?php echo form::file('p_files_'.$i) ?></p>

				<?php # il y a un fichier ?
				if (!empty($aItemFiles[$i])) : ?>
					<p><a href="<?php echo $aItemFiles[$i]['url'] ?>"><img src="<?php echo $okt->options->public_url.'/img/media/'.$aItemFiles[$i]['type'].'.png' ?>" alt="<?php echo $aItemFiles[$i]['filename'] ?>" /></a>
					<?php echo $aItemFiles[$i]['type'] ?> (<?php echo $aItemFiles[$i]['mime'] ?>)
					- <?php echo Utilities::l10nFileSize($aItemFiles[$i]['size']) ?></p>

					<p><a href="module.php?m=##module_id##&amp;action=edit&amp;item_id=<?php
					echo $iItemId ?>&amp;delete_file=<?php echo $i ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('m_##module_id##_confirm_file_deleting')) ?>')"
					class="icon delete"><?php _e('m_##module_id##_delete_this_file') ?></a></p>
				<?php endif; ?>
				</div>
			<?php endfor; ?>
			</div>
			<p class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></p>
		</div><!-- #tab_files -->
		<?php endif; ?>

		<div id="tab-options">
			<h3><?php _e('m_##module_id##_item_options') ?></h3>

			<p class="field col"><label for="p_visibility"><?php echo form::checkbox('p_visibility', 1, $aItemData['visibility']) ?> Visible</label></p>

		</div><!-- #tab-options -->

		<?php if ($okt->##module_id##->config->enable_metas) : ?>
		<div id="tab-seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<p class="field"><label for="p_title_tag"><?php _e('m_##module_id##_title_element') ?></label>
			<?php echo form::text('p_title_tag', 60, 255, html::escapeHTML($aItemData['title_tag'])) ?></p>

			<p class="field"><label for="p_meta_description"><?php _e('c_c_seo_meta_desc') ?></label>
			<?php echo form::text('p_meta_description', 60, 255, html::escapeHTML($aItemData['meta_description'])) ?></p>

			<p class="field"><label for="p_meta_keywords"><?php _e('c_c_seo_meta_keywords') ?></label>
			<?php echo form::textarea('p_meta_keywords', 57, 5, html::escapeHTML($aItemData['meta_keywords'])) ?></p>

			<div class="lockable">
				<p class="field"><label for="p_slug"><?php _e('m_##module_id##_url_element') ?></label>
				<?php echo form::text('p_slug', 60, 255, html::escapeHTML($aItemData['slug'])) ?>
				<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
			</div>
		</div><!-- #tab-seo -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','##module_id##'); ?>
	<?php echo form::hidden('action',!empty($iItemId) ? 'edit' : 'add'); ?>
	<?php echo !empty($iItemId) ? form::hidden('item_id',$iItemId) : ''; ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($iItemId) ? __('c_c_action_edit') : __('c_c_action_add'); ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
