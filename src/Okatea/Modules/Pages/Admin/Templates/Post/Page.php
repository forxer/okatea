<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Module title tag
$okt->page->addTitleTag($okt->Pages->getTitle());

# Start breadcrumb
$okt->page->addAriane($okt->Pages->getName(), $view->generateUrl('Pages_index'));


# button set
$okt->page->setButtonset('pagesBtSt',array(
	'id' => 'pages-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add') && $okt->checkPerm('pages_add'),
			'title' => __('m_pages_menu_add_page'),
			'url' => $view->generateUrl('Pages_post_add'),
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add')
		)
	)
));

# ajout bouton retour
$okt->page->addButton('pagesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> $view->generateUrl('Pages_index'),
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# boutons update page
if (!empty($aPageData['post']['id']))
{
	$okt->page->addGlobalTitle(__('m_pages_page_edit_a_page'));

	# bouton switch statut
	$okt->page->addButton('pagesBtSt',array(
		'permission' 	=> true,
		'title' 		=> ($aPageData['post']['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> $view->generateUrl('Pages_post', array('page_id' => $aPageData['post']['id'])).'?switch_status=1',
		'ui-icon' 		=> ($aPageData['post']['active'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aPageData['post']['active'],
	));
	# bouton de suppression si autorisé
	$okt->page->addButton('pagesBtSt',array(
		'permission' 	=> $okt->checkPerm('pages_remove'),
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> $view->generateUrl('Pages_index').'?delete='.$aPageData['post']['id'],
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.$view->escapeJs(__('m_pages_page_delete_confirm')).'\')',
	));
	# bouton vers la page côté public si publié
	$okt->page->addButton('pagesBtSt',array(
		'permission' 	=> ($aPageData['post']['active'] ? true : false),
		'title' 		=> __('c_c_action_Show'),
		'url' 			=> $okt->router->generateFromAdmin('pagesItem', array('slug' => $aPageData['locales'][$okt->user->language]['slug']), null, true),
		'ui-icon' 		=> 'extlink'
	));
}

# boutons add page
else {
	$okt->page->addGlobalTitle(__('m_pages_page_add_a_page'));
}

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->Pages->config->lightbox_type);

# RTE
$okt->page->applyRte($okt->Pages->config->enable_rte,'textarea.richTextEditor');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Validation javascript
$okt->page->validate('page-form',array(
	array(
		'id' => 'p_title',
		'rules' => array(
			'required: true',
			'minlength: 3'
		)
	),
	array(
		'id' => 'p_content',
		'rules' => array(
			'required: true',
		)
	)
));

# Permission checkboxes
$okt->page->updatePermissionsCheckboxes('perm_g_');
?>

<?php echo $okt->page->getButtonSet('pagesBtSt'); ?>

<?php if (!empty($aPageData['post']['id'])) : ?>
<p><?php printf(__('m_pages_page_added_on'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPageData['post']['created_at']).'</em>') ?>

<?php if ($aPageData['post']['updated_at'] > $aPageData['post']['created_at']) : ?>
<span class="note"><?php printf(__('m_pages_page_last_edit'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPageData['post']['updated_at']).'</em>') ?></span>
<?php endif; ?>
</p>
<?php endif; ?>


<form id="page-form" action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<?php foreach ($aPageData['tabs'] as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($aPageData['tabs'] as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>
	</div><!-- #tabered -->

	<p><?php echo form::hidden('sended',1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php echo !empty($aPageData['post']['id']) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
</form>
