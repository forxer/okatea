<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */



# Accès direct interdit
if (!defined('ON_DIARY_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$iEventId = null;

$aEventData = array(
	'visibility' => 1,
	'title' => '',
	'date' => '',
	'date_end' => '',
	'slug' => '',
	'description' => '',
	'disponibility' => 0,
	'color' => '',
	'created_at' => '',
	'updated_at' => '',
	'title_seo' => '',
	'title_tag' => '',
	'meta_description' => '',
	'meta_keywords' => ''
);


# update event ?
if (!empty($_REQUEST['event_id']))
{
	$iEventId = intval($_REQUEST['event_id']);

	$rsEvent = $okt->diary->getEvent($iEventId);

	if ($rsEvent->isEmpty())
	{
		$okt->error->set(sprintf(__('m_diary_event_%s_not_exists'),$iEventId));
		$iEventId = null;
	}
	else
	{
		$aEventData = array(
			'id' => $iEventId,
			'visibility' => $rsEvent->visibility,
			'title' => $rsEvent->title,
			'date' => $rsEvent->date,
			'date_end' => $rsEvent->date_end,
			'slug' => $rsEvent->slug,
			'description' => $rsEvent->description,
			'disponibility' => $rsEvent->disponibility,
			'color' => $rsEvent->color,
			'created_at' => $rsEvent->created_at,
			'updated_at' => $rsEvent->updated_at,
			'title_seo' => $rsEvent->title_seo,
			'title_tag' => $rsEvent->title_tag,
			'meta_description' => $rsEvent->meta_description,
			'meta_keywords' => $rsEvent->meta_keywords
		);

		if ($okt->diary->config->images['enable']) {
			$aEventImages = $rsEvent->getImagesInfo();
		}

		if ($okt->diary->config->files['enable']) {
			$aEventFiles = $rsEvent->getFilesInfo();
		}

		$sEventUrl = $rsEvent->getEventUrl();
	}
	unset($rsEvent);
}


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']) && !empty($iEventId))
{
	$okt->diary->switchEventStatus($iEventId);
	$okt->redirect('module.php?m=diary&action=edit&event_id='.$iEventId.'&switched=1');
}

# suppression d'une image
if (!empty($_GET['delete_image']) && !empty($iEventId))
{
	$okt->diary->deleteImage($iEventId, $_GET['delete_image']);
	$okt->redirect('module.php?m=diary&action=edit&event_id='.$iEventId.'&edited=1');
}

# suppression d'un fichier
if (!empty($_GET['delete_file']) && !empty($iEventId))
{
	$okt->diary->deleteFile($iEventId, $_GET['delete_file']);
	$okt->redirect('module.php?m=diary&action=edit&event_id='.$iEventId.'&edited=1');
}

#  ajout / modifications d'un élément
if (!empty($_POST['sended']))
{
	$aEventData = array(
		'id' => $iEventId,
		'visibility' => (!empty($_POST['p_visibility']) ? 1 : 0),
		'title' => (!empty($_POST['p_title']) ? $_POST['p_title'] : ''),
		'date' => (!empty($_POST['p_date']) ? $_POST['p_date'] : ''),
		'date_end' => (!empty($_POST['p_date_end']) ? $_POST['p_date_end'] : ''),
		'slug' => (!empty($_POST['p_slug']) ? $_POST['p_slug'] : ''),
		'description' => (!empty($_POST['p_description']) ? $_POST['p_description'] : ''),
		'disponibility' => (!empty($_POST['p_disponibility']) ? $_POST['p_disponibility'] : ''),
		'color' => (!empty($_POST['p_color']) ? $_POST['p_color'] : ''),
		'created_at' => $aEventData['created_at'],
		'updated_at' => $aEventData['updated_at'],
		'title_seo' => (!empty($_POST['p_title_seo']) ? $_POST['p_title_seo'] : ''),
		'title_tag' => (!empty($_POST['p_title_tag']) ? $_POST['p_title_tag'] : ''),
		'meta_description' => (!empty($_POST['p_meta_description']) ? $_POST['p_meta_description'] : ''),
		'meta_keywords' => (!empty($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : '')
	);

	# vérification des données avant modification dans la BDD
	if ($okt->diary->checkPostData($aEventData))
	{
		$cursor = $okt->diary->openCursor($aEventData);

		# update event
		if (!empty($iEventId))
		{
			# -- CORE TRIGGER : moduleDiaryBeforeEventUpdate
			$okt->triggers->callTrigger('moduleDiaryBeforeEventUpdate', $cursor, $aEventData, $iEventId);

			if ($okt->diary->updEvent($iEventId, $cursor, $aEventData))
			{
				# -- CORE TRIGGER : moduleDiaryAfterEventUpdate
				$okt->triggers->callTrigger('moduleDiaryAfterEventUpdate', $cursor, $aEventData, $iEventId);

				$okt->redirect('module.php?m=diary&action=edit&event_id='.$iEventId.'&updated=1');
			}
		}

		# add event
		else
		{
			# -- CORE TRIGGER : moduleDiaryBeforeEventCreate
			$okt->triggers->callTrigger('moduleDiaryBeforeEventCreate',$cursor, $aEventData);

			if (($iEventId = $okt->diary->addEvent($cursor, $aEventData)) !== false)
			{
				# -- CORE TRIGGER : moduleDiaryAfterEventCreate
				$okt->triggers->callTrigger('moduleDiaryAfterEventCreate', $cursor, $aEventData, $iEventId);

				$okt->redirect('module.php?m=diary&action=edit&event_id='.$iEventId.'&added=1');
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# ajout bouton retour
$okt->page->addButton('diaryBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> 'module.php?m=diary&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# boutons modification élément
if (!empty($iEventId))
{
	$okt->page->addGlobalTitle(__('m_diary_edit_event'));

	# bouton switch statut
	$okt->page->addButton('diaryBtSt',array(
		'permission' 	=> true,
		'title' 		=> ($aEventData['visibility'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=diary&amp;action=edit&amp;event_id='.$iEventId.'&amp;switch_status=1',
		'ui-icon' 		=> ($aEventData['visibility'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aEventData['visibility'],
	));

	# bouton de suppression si autorisé
	$okt->page->addButton('diaryBtSt',array(
		'permission' 	=> $okt->checkPerm('diary_remove'),
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> 'module.php?m=diary&amp;action=delete&amp;event_id='.$iEventId,
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_diary_confirm_deleting')).'\')',
	));

	# bouton vers l'élément côté public si publié
	$okt->page->addButton('diaryBtSt',array(
		'permission' 	=> ($aEventData['visibility'] ? true : false),
		'title' 		=> __('c_c_action_Show'),
		'url' 			=> $sEventUrl,
		'ui-icon' 		=> 'extlink'
	));

	$okt->page->messages->success('added',__('m_diary_confirm_added'));
	$okt->page->messages->success('updated',__('m_diary_confirm_edited'));
}

# boutons ajout élément
else {
	$okt->page->addGlobalTitle(__('m_diary_add_event'));
}


# Lockable
$okt->page->lockable();


# Tabs
$okt->page->tabs();


# Modal
$okt->page->applyLbl($okt->diary->config->lightbox_type);


# RTE
$okt->page->applyRte($okt->diary->config->enable_rte, '#p_description');


# Réglages de la validation javascript
$aValidateFieldsJs = array();

$aValidateFieldsJs[] = array(
	'id' => 'p_title',
	'rules' => array(
		'required: true',
		'minlength: 3'
	)
);

$aValidateFieldsJs[] = array(
	'id' => 'p_date',
	'rules' => array(
			'required: true'
	)
);

if ($okt->diary->config->fields['color'])
{
	$aValidateFieldsJs[] = array(
		'id' => 'p_color',
		'rules' => 'required: '.($okt->diary->config->fields['color'] == 2 ? 'true' : 'false')
	);
}

if ($okt->diary->config->fields['disponibility'])
{
	$aValidateFieldsJs[] = array(
		'id' => 'p_disponibility',
		'rules' => 'required: '.($okt->diary->config->fields['disponibility'] == 2 ? 'true' : 'false'),
		'messages' => 'required: "'.__('m_diary_please_choose_disponibility').'"'
	);
}

# Validation javascript
$okt->page->validate('event-form',$aValidateFieldsJs);


# Datepicker
$okt->page->datePicker();


# Color picker
$okt->page->colorpicker('.colorpicker');
$okt->page->js->addReady('
	$("#tabered").bind("tabsshow", function() {
		$(".jPicker.Container").css({"top":"300px"});
	});
');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('diaryBtSt'); ?>

<?php if (!empty($iEventId)) : ?>
<p><?php printf(__('m_diary_event_added_on_%s'),'<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aEventData['created_at']).'</em>') ?>
	<?php if ($aEventData['updated_at'] > $aEventData['created_at']) : ?>
	<span class="note"><?php printf(__('m_diary_event_updated_%s'),'<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aEventData['updated_at']).'</em>') ?></span>
	<?php endif; ?>
</p>
<?php endif; ?>


<form id="event-form" action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab-content"><span><?php _e('m_diary_content') ?></span></a></li>
			<?php if ($okt->diary->config->images['enable']) : ?>
			<li><a href="#tab_images"><span><?php _e('m_diary_images') ?></span></a></li>
			<?php endif; ?>
			<?php if ($okt->diary->config->files['enable']) : ?>
			<li><a href="#tab_files"><span><?php _e('m_diary_files') ?></span></a></li>
			<?php endif; ?>
			<li><a href="#tab-options"><span><?php _e('m_diary_options') ?></span></a></li>
			<?php if ($okt->diary->config->enable_metas) : ?>
			<li><a href="#tab-seo"><span><?php _e('m_diary_seo') ?></span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab-content">
			<h3><?php _e('m_diary_event_content') ?></h3>

			<p class="field"><label for="p_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_diary_title') ?></label>
			<?php echo form::text('p_title', 60, 255, html::escapeHTML($aEventData['title'])) ?></p>

			<p class="field"><label for="p_description"><?php _e('m_diary_description') ?></label>
			<?php echo form::textarea('p_description', 57, 10, $aEventData['description']) ?></p>

			<div class="two-cols">
				<p class="field col"><label for="p_date" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_diary_date') ?></label>
				<?php echo form::text('p_date', 20, 255, ($aEventData['date'] ? dt::dt2str('%d-%m-%Y',$aEventData['date']) : ''), 'datepicker') ?></p>

				<p class="field col"><label for="p_date_end"><?php _e('m_diary_date_end')?></label>
				<?php echo form::text('p_date_end', 20, 255, (!empty($aEventData['date_end']) ? dt::dt2str('%d-%m-%Y',$aEventData['date_end']) : ''), 'datepicker') ?></p>
			</div><!-- .two-cols -->

		</div><!-- #tab-content -->


		<?php if ($okt->diary->config->images['enable']) : ?>
		<div id="tab_images">
			<h3><?php _e('m_diary_event_images') ?></h3>
			<div class="two-cols modal-box">
			<?php for ($i=1; $i<=$okt->diary->config->images['number']; $i++) : ?>
				<div class="col">

					<p class="field"><label for="p_images_<?php echo $i ?>"><?php printf(__('m_diary_image_n'), $i) ?></label>
					<?php echo form::file('p_images_'.$i) ?></p>

					<?php # il y a une image ?
					if (!empty($aEventImages[$i])) :

						# affichage square ou icon ?
						if (isset($aEventImages[$i]['square_url'])) {
							$cur_image_url = $aEventImages[$i]['square_url'];
							$cur_image_attr = $aEventImages[$i]['square_attr'];
						}
						else {
							$cur_image_url = OKT_PUBLIC_URL.'/img/media/image.png';
							$cur_image_attr = ' width="48" height="48" ';
						}

						$cur_image_alt = isset($aEventImages[$i]['alt']) ? $aEventImages[$i]['alt'] : '';

						?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>"><?php printf(__('m_diary_alt_image_n'), $i) ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,html::escapeHTML($cur_image_alt)) ?></p>

						<p><a href="<?php echo $aEventImages[$i]['img_url']?>"
						rel="event_images" title="<?php echo html::escapeHTML($aEventData['title']) ?>, <?php printf(__('m_diary_image_n'), $i) ?>" class="modal"><img src="<?php echo $cur_image_url ?>"
						<?php echo $cur_image_attr ?> alt="" /></a></p>

						<p><a href="module.php?m=diary&amp;action=edit&amp;event_id=<?php
						echo $iEventId ?>&amp;delete_image=<?php echo $i ?>"
						onclick="return window.confirm('<?php echo html::escapeJS(__('m_diary_confirm_image_deleting')) ?>')"
						class="link_sprite ss_delete"><?php _e('m_diary_delete_this_image') ?></a></p>

					<?php else : ?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>"><?php printf(__('m_diary_alt_image_n'), $i) ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,'') ?></p>

					<?php endif; ?>
				</div>
			<?php endfor; ?>
			</div>
			<p class="note"><?php printf(__('c_c_maximum_file_size_%s'),util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>
		</div><!-- #tab_images -->
		<?php endif; ?>

		<?php if ($okt->diary->config->files['enable']) : ?>
		<div id="tab_files">
			<h3><?php _e('m_diary_event_files') ?></h3>

			<div class="two-cols">
			<?php for ($i=1; $i<=$okt->diary->config->files['number']; $i++) : ?>
				<div class="col">
				<p class="field"><label for="p_files_<?php echo $i ?>"><?php printf(__('m_diary_file_n'), $i) ?></label>
				<?php echo form::file('p_files_'.$i) ?></p>

				<?php # il y a un fichier ?
				if (!empty($aEventFiles[$i])) : ?>
					<p><a href="<?php echo $aEventFiles[$i]['url'] ?>"><img src="<?php echo OKT_PUBLIC_URL.'/img/media/'.$aEventFiles[$i]['type'].'.png' ?>" alt="<?php echo $aEventFiles[$i]['filename'] ?>" /></a>
					<?php echo $aEventFiles[$i]['type'] ?> (<?php echo $aEventFiles[$i]['mime'] ?>)
					- <?php echo util::l10nFileSize($aEventFiles[$i]['size']) ?></p>

					<p><a href="module.php?m=diary&amp;action=edit&amp;event_id=<?php
					echo $iEventId ?>&amp;delete_file=<?php echo $i ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('m_diary_confirm_file_deleting')) ?>')"
					class="link_sprite ss_delete"><?php _e('m_diary_delete_this_file') ?></a></p>
				<?php endif; ?>
				</div>
			<?php endfor; ?>
			</div>
			<p class="note"><?php printf(__('c_c_maximum_file_size_%s'),util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>
		</div><!-- #tab_files -->
		<?php endif; ?>

		<div id="tab-options">
			<h3><?php _e('m_diary_event_options') ?></h3>

			<div class="two-cols">
				<p class="field col"><label><?php echo form::checkbox('p_visibility', 1, $aEventData['visibility']) ?> Visible</label></p>

				<?php if ($okt->diary->config->fields['disponibility']) : ?>
					<p class="field col"><label for="p_disponibility"<?php if ($okt->diary->config->fields['disponibility'] == 2) { echo ' title="'.__('c_c_required_field').'" class="required"'; } ?>><?php _e('m_diary_disponibility') ?></label>
					<?php echo form::select('p_disponibility', array_merge(array('&nbsp;'=>''), module_diary::getDisponibility(true)),$aEventData['disponibility']); ?></p>
				<?php endif; ?>

				<?php if ($okt->diary->config->fields['color']) : ?>
					<p class="field col"><label for="p_color"<?php if ($okt->diary->config->fields['color'] == 2) { echo ' title="'.__('c_c_required_field').'" class="required"'; } ?>><?php _e('m_diary_color') ?></label>
					<?php echo form::text('p_color', 6, 6, $aEventData['color'], 'colorpicker') ?></p>
				<?php endif; ?>
			</div>

		</div><!-- #tab-options -->

		<?php if ($okt->diary->config->enable_metas) : ?>
		<div id="tab-seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<p class="field"><label for="p_title_tag"><?php _e('m_diary_title_element') ?></label>
			<?php echo form::text('p_title_tag', 60, 255, html::escapeHTML($aEventData['title_tag'])) ?></p>

			<p class="field"><label for="p_meta_description"><?php _e('c_c_seo_meta_desc') ?></label>
			<?php echo form::text('p_meta_description', 60, 255, html::escapeHTML($aEventData['meta_description'])) ?></p>

			<p class="field"><label for="p_title_seo"><?php _e('c_c_seo_title_seo')?></label>
			<?php echo form::text('p_title_seo', 60, 255, html::escapeHTML($aEventData['title_seo'])) ?></p>
			
			<p class="field"><label for="p_meta_keywords"><?php _e('c_c_seo_meta_keywords') ?></label>
			<?php echo form::textarea('p_meta_keywords', 57, 5, html::escapeHTML($aEventData['meta_keywords'])) ?></p>

			<div class="lockable">
				<p class="field"><label for="p_slug"><?php _e('m_diary_url_element') ?></label>
				<?php echo form::text('p_slug', 60, 255, html::escapeHTML($aEventData['slug'])) ?>
				<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
			</div>
		</div><!-- #tab-seo -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','diary'); ?>
	<?php echo form::hidden('action',!empty($iEventId) ? 'edit' : 'add'); ?>
	<?php echo !empty($iEventId) ? form::hidden('event_id',$iEventId) : ''; ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($iEventId) ? __('c_c_action_edit') : __('c_c_action_add'); ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
