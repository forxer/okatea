<?php
/**
 * @ingroup okt_module_news
 * @brief La liste des articles
 *
 */


# Accès direct interdit
if (!defined('ON_NEWS_MODULE')) die;


/* json posts list for autocomplete
----------------------------------------------------------*/

if (!empty($_REQUEST['json']))
{
	$rsPosts = $okt->news->getPostsRecordset(array(
		'language' => $okt->user->language,
		'search' => $_GET['term']
	));

	$aResults = array();
	while ($rsPosts->fetch()) {
		$aResults[] = $rsPosts->title;
	}

	header('Content-type: application/json');
	echo json_encode($aResults);

	exit;
}


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
l10n::set(dirname(__FILE__).'/../../locales/'.$okt->user->language.'/admin.list');

# initialisation des filtres
$okt->news->filtersStart('admin');


/* Traitements
----------------------------------------------------------*/

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->news->filters->initFilters();
	$okt->redirect('module.php?m=news&action=index');
}

# Switch statut
if (!empty($_GET['switch_status']))
{
	try
	{
		$okt->news->switchPostStatus($_GET['switch_status']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'news',
			'message' => 'post #'.$_GET['switch_status']
		));

		$okt->redirect('module.php?m=news&action=index&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Switch article selection
if (!empty($_GET['switch_selected']))
{
	$okt->news->switchPostSelected($_GET['switch_selected']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'news',
		'message' => 'post #'.$_GET['switch_selected']
	));

	$okt->redirect('module.php?m=news&action=index&selected=1');
}

# Sélectionne un article
if (!empty($_GET['select']))
{
	$okt->news->setPostSelected($_GET['select'], true);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'news',
		'message' => 'post #'.$_GET['select']
	));

	$okt->redirect('module.php?m=news&action=index&selected=1');
}

# Déselectionne un article
if (!empty($_GET['deselect']))
{
	$okt->news->setPostSelected($_GET['deselect'], false);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'news',
		'message' => 'post #'.$_GET['deselect']
	));

	$okt->redirect('module.php?m=news&action=index&deselected=1');
}

# Publication d'un article
if (!empty($_GET['publish']))
{
	$okt->news->publishPost($_GET['publish']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 30,
		'component' => 'news',
		'message' => 'post #'.$_GET['publish']
	));

	$okt->redirect('module.php?m=news&action=index&published=1');
}

# Traitements par lots
if (!empty($_POST['actions']) && !empty($_POST['posts']) && is_array($_POST['posts']))
{
	$aPostsId = array_map('intval',$_POST['posts']);

	try
	{
		if ($_POST['actions'] == 'show')
		{
			foreach ($aPostsId as $iPostId)
			{
				$okt->news->showPost($iPostId,1);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 30,
					'component' => 'news',
					'message' => 'post #'.$iPostId
				));
			}

			$okt->redirect('module.php?m=news&action=index&switcheds=1');
		}
		elseif ($_POST['actions'] == 'hide')
		{
			foreach ($aPostsId as $iPostId)
			{
				$okt->news->hidePost($iPostId);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 31,
					'component' => 'news',
					'message' => 'post #'.$iPostId
				));
			}

			$okt->redirect('module.php?m=news&action=index&switcheds=1');
		}
		elseif ($_POST['actions'] == 'publish')
		{
			foreach ($aPostsId as $iPostId)
			{
				$okt->news->publishPost($iPostId);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 30,
					'component' => 'news',
					'message' => 'post #'.$iPostId
				));
			}

			$okt->redirect('module.php?m=news&action=index&publisheds=1');
		}
		elseif ($_POST['actions'] == 'selected')
		{
			foreach ($aPostsId as $iPostId)
			{
				$okt->news->setPostSelected($iPostId,1);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'news',
					'message' => 'post #'.$iPostId
				));
			}

			$okt->redirect('module.php?m=news&action=index&selecteds=1');
		}
		elseif ($_POST['actions'] == 'unselected')
		{
			foreach ($aPostsId as $iPostId)
			{
				$okt->news->setPostSelected($iPostId,0);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'news',
					'message' => 'post #'.$iPostId
				));
			}

			$okt->redirect('module.php?m=news&action=index&unselecteds=1');
		}
		elseif ($_POST['actions'] == 'delete' && $okt->checkPerm('news_delete'))
		{
			foreach ($aPostsId as $iPostId)
			{
				$okt->news->deletePost($iPostId);

				# log admin
				$okt->logAdmin->warning(array(
					'code' => 42,
					'component' => 'news',
					'message' => 'post #'.$iPostId
				));
			}

			$okt->redirect('module.php?m=news&action=index&deleteds=1');
		}
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Publication des articles différés
$okt->news->publishScheduledPosts();


/* Affichage
----------------------------------------------------------*/

# Initialisation des filtres
$aParams = array();

if (!$okt->checkPerm('news_contentadmin') && !$okt->checkPerm('news_show_all')) {
	$aParams['user_id'] = $okt->user->id;
}

$sSearch = null;

if (!empty($_REQUEST['search']))
{
	$sSearch = trim($_REQUEST['search']);
	$aParams['search'] = $sSearch;
}
$okt->news->filters->setPostsParams($aParams);


# Création des filtres
$okt->news->filters->getFilters();


# Initialisation de la pagination
$iNumFilteredPosts = $okt->news->getPostsCount($aParams);

$oPager = new adminPager($okt->news->filters->params->page, $iNumFilteredPosts, $okt->news->filters->params->nb_per_page);

$iNumPages = $oPager->getNbPages();

$okt->news->filters->normalizePage($iNumPages);

$aParams['limit'] = (($okt->news->filters->params->page-1)*$okt->news->filters->params->nb_per_page).','.$okt->news->filters->params->nb_per_page;


# Récupération des articles
$rsPosts = $okt->news->getPosts($aParams);


# Liste des groupes si les permissions sont activées
if ($okt->news->canUsePerms()) {
	$aGroups = $okt->news->getUsersGroupsForPerms(true,true);
}


# Tableau de choix d'actions pour le traitement par lot
$aActionsChoices = array(
	'&nbsp;' => null,
	__('m_news_list_status') => array(
		__('c_c_action_display') => 'show',
		__('c_c_action_hide') => 'hide'
	)
);

if ($okt->checkPerm('news_publish') || $okt->checkPerm('news_contentadmin'))  {
	$aActionsChoices[__('m_news_list_status')][__('c_c_action_publish')] = 'publish';
}

$aActionsChoices[__('m_news_list_mark')] = array(
	__('c_c_action_select') => 'selected',
	__('c_c_action_deselect') => 'unselected'
);

if ($okt->checkPerm('news_delete') || $okt->checkPerm('news_contentadmin'))  {
	$aActionsChoices[__('c_c_action_Delete')][__('c_c_action_delete')] = 'delete';
}


# Autocomplétion du formulaire de recherche
$okt->page->js->addReady('
	$("#search").autocomplete({
		source: "module.php?search=&m=news&action=index&json=1",
		minLength: 2
	});
');

if (!empty($sSearch))
{
	$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
	$okt->page->js->addReady('
		$("#search").putCursorAtEnd();
	');
}


# Ajout de boutons
$okt->page->addButton('newsBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_display_filters'),
	'url' 			=> '#',
	'ui-icon' 		=> 'search',
	'active' 		=> $okt->news->filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));


# Bouton vers le module côté public
$okt->page->addButton('newsBtSt',array(
	'permission' 	=> $okt->news->config->enable_show_link,
	'title' 		=> __('c_c_action_show'),
	'url' 			=> html::escapeHTML($okt->news->config->url),
	'ui-icon' 		=> 'extlink'
));


# Filters control
if ($okt->news->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->news->filters->params->show_filters);
}
elseif ($okt->news->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title:'".html::escapeJS(__('c_c_display_filters'))."',
			autoOpen: false,
			modal: true,
			width: 500,
			height: 300
		});

		$('#filter-control').click(function() {
			$('#filters-form').dialog('open');
		})
	");
}


# Checkboxes helper
$okt->page->checkboxHelper('posts-list','checkboxHelper');


# Messages de confirmation
$okt->page->messages->success('published', __('m_news_list_post_published'));
$okt->page->messages->success('selected', __('m_news_list_post_selected'));
$okt->page->messages->success('deselected', __('m_news_list_post_deselected'));
$okt->page->messages->success('deleted', __('m_news_list_post_deleted'));

$okt->page->messages->success('publisheds', __('m_news_list_posts_published'));
$okt->page->messages->success('selecteds', __('m_news_list_posts_selected'));
$okt->page->messages->success('unselecteds', __('m_news_list_posts_deselected'));
$okt->page->messages->success('deleteds', __('m_news_list_posts_deleted'));


# Un peu de CSS
$okt->page->css->addCss('
.ui-autocomplete {
	max-height: 150px;
	overflow-y: auto;
	overflow-x: hidden;
}
.search_form p {
	margin: 0;
}
#search {
	background: transparent url('.OKT_COMMON_URL.'/img/admin/preview.png) no-repeat center right;
}
#post-count {
	margin-top: 0;
}
');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div class="double-buttonset">
	<div class="buttonsetA">
		<?php echo $okt->page->getButtonSet('newsBtSt'); ?>
	</div>
	<div class="buttonsetB">
		<form action="module.php" method="get" id="search_form" class="search_form">
			<p><label for="search"><?php _e('m_news_list_Search') ?></label>
			<?php echo form::text('search',20,255,html::escapeHTML((isset($sSearch) ? $sSearch : ''))); ?>

			<?php echo form::hidden('m','news') ?>
			<?php echo form::hidden('action','index') ?>
			<input type="submit" name="search_submit" id="search_submit" value="ok" /></p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_news_display_filters') ?></legend>

		<?php echo $okt->news->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','news') ?>
		<?php echo form::hidden('action','index') ?>
		<input type="submit" name="<?php echo $okt->news->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display') ?>" />
		<a href="module.php?m=news&amp;action=index&amp;init_filters=1"><?php _e('c_c_reset_filters') ?></a></p>

	</fieldset>
</form>

<div id="postsList">

<?php # Affichage du compte d'articles
if ($iNumFilteredPosts == 0) : ?>
<p id="post-count"><?php _e('m_news_list_no_post') ?></p>
<?php elseif ($iNumFilteredPosts == 1) : ?>
<p id="post-count"><?php _e('m_news_list_one_post') ?></p>
<?php else : ?>
	<?php if ($iNumPages > 1) : ?>
		<p id="post-count"><?php printf(__('m_news_list_%s_posts_on_%s_pages'), $iNumFilteredPosts, $iNumPages) ?></p>
	<?php else : ?>
		<p id="post-count"><?php printf(__('m_news_list_%s_posts'), $iNumFilteredPosts) ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php # Si on as des articles à afficher
if (!$rsPosts->isEmpty()) : ?>

<form action="module.php" method="post" id="posts-list">

	<table class="common">
		<caption><?php _e('m_news_list_table_caption') ?></caption>
		<thead><tr>
			<th scope="col"><?php _e('m_news_list_table_th_title') ?></th>
			<?php if ($okt->news->config->categories['enable']) : ?>
			<th scope="col"><?php _e('m_news_list_table_th_category') ?></th>
			<?php endif; ?>
			<?php if ($okt->news->canUsePerms()) : ?>
			<th scope="col"><?php _e('m_news_list_table_th_access') ?></th>
			<?php endif; ?>
			<th scope="col"><?php _e('m_news_list_table_th_dates') ?></th>
			<th scope="col"><?php _e('m_news_list_table_th_author') ?></th>
			<th scope="col"><?php _e('c_c_Actions') ?></th>
		</tr></thead>
		<tbody>
		<?php $count_line = 0;
		while ($rsPosts->fetch()) :
			$td_class = $count_line%2 == 0 ? 'even' : 'odd';
			$count_line++;
		?>
		<tr>
			<th class="<?php echo $td_class ?> fake-td">
				<?php echo form::checkbox(array('posts[]'),$rsPosts->id) ?>
				<?php if ($rsPosts->selected) : ?><span class="span_sprite ss_star"></span><?php endif; ?>
				<?php if ($rsPosts->active == 2) : ?><span class="span_sprite ss_time"></span><?php endif; ?>
				<?php if ($rsPosts->active == 3) : ?><span class="span_sprite ss_clock"></span><?php endif; ?>
				<a href="module.php?m=news&amp;action=edit&amp;post_id=<?php echo $rsPosts->id ?>"><?php
				echo html::escapeHTML($rsPosts->title) ?></a>
			</th>

			<?php if ($okt->news->config->categories['enable']) : ?>
			<td class="<?php echo $td_class ?>"><?php echo html::escapeHTML($rsPosts->category_title) ?></td>
			<?php endif; ?>

			<?php # droits d'accès
			if ($okt->news->canUsePerms()) :

				$aGroupsAccess = array();
				$aPerms = $okt->news->getPostPermissions($rsPosts->id);
				foreach ($aPerms as $iPerm) {
					$aGroupsAccess[] = html::escapeHTML($aGroups[$iPerm]);
				}
				unset($aPerms);
			?>
			<td class="<?php echo $td_class ?>">
				<?php if (!empty($aGroupsAccess)) : ?>
				<ul>
					<li><?php echo implode('</li><li>',$aGroupsAccess) ?></li>
				</ul>
				<?php endif; ?>
			</td>
			<?php endif; ?>

			<td class="<?php echo $td_class ?>">
			<?php if ($rsPosts->active == 3) : ?>
				<p><?php printf(__('m_news_list_sheduled_%s'), dt::dt2str(__('%Y-%m-%d %H:%M'),$rsPosts->created_at)) ?>
			<?php else : ?>
				<p><?php printf(($rsPosts->active == 2 ? __('m_news_list_added_%s') : __('m_news_list_published_%s')), dt::dt2str(__('%Y-%m-%d %H:%M'),$rsPosts->created_at)) ?>
				<?php if ($rsPosts->updated_at > $rsPosts->created_at) : ?>
				<span class="note"><?php printf(__('m_news_list_edited_%s'), dt::dt2str(__('%Y-%m-%d %H:%M'),$rsPosts->updated_at)) ?></span>
				<?php endif; ?>
				</p>
			<?php endif; ?>
			</td>

			<td class="<?php echo $td_class ?>">
				<?php echo html::escapeHTML(oktAuth::getUserCN($rsPosts->username, $rsPosts->lastname, $rsPosts->firstname)) ?>
			</td>

			<td class="<?php echo $td_class ?> small nowrap">
				<ul class="actions">
				<?php if ($rsPosts->active == 0) : ?>
					<li><a href="module.php?m=news&amp;action=index&amp;switch_status=<?php echo $rsPosts->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_switch_visibility_%s'), $rsPosts->title)) ?>"
					class="link_sprite ss_cross"><?php _e('c_c_action_Hidden') ?></a></li>

				<?php elseif ($rsPosts->active == 1) : ?>
					<li><a href="module.php?m=news&amp;action=index&amp;switch_status=<?php echo $rsPosts->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_switch_visibility_%s'), $rsPosts->title)) ?>"
					class="link_sprite ss_tick"><?php _e('c_c_action_Visible') ?></a></li>

				<?php elseif ($rsPosts->active == 2) : ?>
					<?php if ($rsPosts->isPublishable()) : ?>
					<li><a href="module.php?m=news&amp;action=index&amp;publish=<?php echo $rsPosts->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_publish_%s'), $rsPosts->title)) ?>"
					class="link_sprite ss_time"><?php _e('c_c_action_Publish') ?></a></li>
					<?php else : ?>
					<li><span class="link_sprite ss_time"></span> <?php _e('m_news_list_awaiting_validation') ?></li>
					<?php endif; ?>

				<?php elseif ($rsPosts->active == 3) : ?>
					<li><span class="link_sprite ss_clock"></span> <?php _e('m_news_list_delayed_publication') ?></li>
				<?php endif; ?>

				<?php if ($rsPosts->selected) : ?>
					<li><a href="module.php?m=news&amp;action=index&amp;deselect=<?php echo $rsPosts->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_deselect_%s'), $rsPosts->title))?>"
					class="link_sprite ss_award_star_delete"><?php _e('c_c_action_Deselect')?></a></li>
				<?php else : ?>
					<li><a href="module.php?m=news&amp;action=index&amp;select=<?php echo $rsPosts->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_select_%s'), $rsPosts->title)) ?>"
					class="link_sprite ss_award_star_add"><?php _e('c_c_action_Select')?></a></li>
				<?php endif; ?>

				<?php if ($rsPosts->isEditable()) : ?>
					<li><a href="module.php?m=news&amp;action=edit&amp;post_id=<?php echo $rsPosts->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_edit_%s'), $rsPosts->title)) ?>"
					class="link_sprite ss_pencil"><?php _e('c_c_action_Edit') ?></a></li>
				<?php else : ?>
					<li><a href="module.php?m=news&amp;action=edit&amp;post_id=<?php echo $rsPosts->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_show_%s'), $rsPosts->title)) ?>"
					class="link_sprite ss_application_form"><?php _e('c_c_action_Show') ?></a></li>
				<?php endif; ?>

				<?php if ($rsPosts->isDeletable()) : ?>
					<li><a href="module.php?m=news&amp;action=delete&amp;post_id=<?php echo $rsPosts->id ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('m_news_list_post_delete_confirm')) ?>')"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_list_delete_%s'), $rsPosts->title)) ?>"
					class="link_sprite ss_delete"><?php _e('c_c_action_Delete') ?></a></li>
				<?php endif; ?>
				</ul>
			</td>
		</tr>
		<?php endwhile; ?>
		</tbody>
	</table>

	<div class="two-cols">
		<div class="col">
			<p id="checkboxHelper"></p>
		</div>
		<div class="col right"><p><?php _e('m_news_list_posts_action')?>
		<?php echo form::select('actions',$aActionsChoices) ?>
		<?php echo form::hidden('m','news'); ?>
		<?php echo form::hidden('action','index'); ?>
		<?php echo form::hidden('sended',1); ?>
		<?php echo adminPage::formtoken(); ?>
		<input type="submit" value="<?php echo 'ok'; ?>" /></p></div>
	</div>
</form>

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>

</div><!-- #postsList -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
