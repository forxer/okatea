<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Module title tag
$okt->page->addTitleTag($okt->module('News')->getTitle());

# Module start breadcrumb
$okt->page->addAriane($okt->module('News')->getName(), $view->generateUrl('News_index'));


# button set
$okt->page->setButtonset('newsBtSt', array(
	'id' => 'news-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	 => true,
			'title' 		 => __('c_c_action_Go_back'),
			'url' 			 => $view->generateUrl('News_index'),
			'ui-icon' 		 => 'arrowreturnthick-1-w'
		),
		array(
			'permission'     => true,
			'title'          => __('m_news_menu_add_post'),
			'url'            => $view->generateUrl('News_post_add'),
			'ui-icon'        => 'plusthick',
			'active'         => empty($aPostData['post']['id'])
		)
	)
));


# boutons update post
if (!empty($aPostData['post']['id']))
{
	$okt->page->addGlobalTitle(__('m_news_post_edit_a_post'));

	# bouton switch statut
	$okt->page->addButton('newsBtSt', array(
		'permission' 	=> ($aPostData['post']['active'] <= 1 && $aPermissions['bCanEditPost']),
		'title' 		=> ($aPostData['post']['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> $view->generateUrl('News_post', array('post_id' => $aPostData['post']['id'])).'?switch_status=1',
		'ui-icon' 		=> ($aPostData['post']['active'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aPostData['post']['active'],
	));
	# bouton publier si autorisé
	$okt->page->addButton('newsBtSt', array(
		'permission' 	=> ($aPostData['post']['active'] == 2 && $aPermissions['bCanPublish']),
		'title' 		=> __('c_c_action_Publish'),
		'url' 			=> $view->generateUrl('News_post', array('post_id' => $aPostData['post']['id'])).'?publish=1',
		'ui-icon' 		=> 'clock'
	));
	# bouton de suppression si autorisé
	$okt->page->addButton('newsBtSt', array(
		'permission' 	=> $aPermissions['bCanDelete'],
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> $view->generateUrl('News_index').'?delete='.$aPostData['post']['id'],
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.$view->escapeJs(__('m_news_post_delete_confirm')).'\')',
	));
	# bouton vers l'article côté public si publié
	if (!empty($aPostData['locales'][$okt->user->language]['slug']))
	{
		$okt->page->addButton('newsBtSt', array(
			'permission' 	=> ($aPostData['post']['active'] ? true : false),
			'title' 		=> __('c_c_action_Show'),
			'url' 			=> $okt->router->generateFromAdmin('newsItem', array('slug' => $aPostData['locales'][$okt->user->language]['slug']), null, true),
			'ui-icon' 		=> 'extlink'
		));
	}
}
# boutons add post
else {
	$okt->page->addGlobalTitle(__('m_news_post_add_a_post'));
}

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Datepicker
$okt->page->datePicker();

# Modal
$okt->page->applyLbl($okt->module('News')->config->lightbox_type);

# RTE
$okt->page->applyRte($okt->module('News')->config->enable_rte,'textarea.richTextEditor');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Permission checkboxes
$okt->page->updatePermissionsCheckboxes('perm_g_');

?>

<?php echo $okt->page->getButtonSet('newsBtSt'); ?>

<?php if (!empty($aPostData['post']['id'])) : ?>
	<?php if ($aPostData['post']['active'] == 3) : ?>
	<p><?php printf(__('m_news_post_sheduled_%s'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPostData['post']['created_at']).'</em>') ?></p>

	<?php else : ?>
	<p><?php printf(($aPostData['post']['active'] == 2 ? __('m_news_post_added_on') : __('m_news_post_published_on')), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPostData['post']['created_at']).'</em>') ?>

		<?php if ($aPostData['post']['updated_at'] > $aPostData['post']['created_at']) : ?>
		<span class="note"><?php printf(__('m_news_post_last_edit'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPostData['post']['updated_at']).'</em>') ?></span>
		<?php endif; ?>
	</p>
	<?php endif; ?>
<?php endif; ?>


<form id="post-form" action="<?php echo !empty($aPostData['post']['id']) ? $view->generateUrl('News_post', array('post_id' => $aPostData['post']['id'])) : $view->generateUrl('News_post_add'); ?>" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<?php foreach ($aPostData['tabs'] as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($aPostData['tabs'] as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>
	</div><!-- #tabered -->

	<?php if ($aPermissions['bCanEditPost']) : ?>
	<p><?php echo form::hidden('action',!empty($aPostData['post']['id']) ? 'edit' : 'add'); ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php echo !empty($aPostData['post']['id']) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
	<?php endif; ?>
</form>
