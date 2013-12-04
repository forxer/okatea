<?php
/**
 * @ingroup okt_module_faq
 * @brief La page d'accueil du module
 *
 */


# Accès direct interdit
if (!defined('ON_FAQ_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# initialisation des filtres
$okt->faq->filtersStart('admin');


/* Traitements
----------------------------------------------------------*/

# switch question statut
if (!empty($_GET['switch_status']))
{
	if ($okt->faq->setQuestionStatus($_GET['switch_status'])) {
		http::redirect('module.php?m=faq&action=index&switched=1');
	}
}

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->faq->filters->initFilters();
	http::redirect('module.php?m=faq&action=index');
}


/* Affichage
----------------------------------------------------------*/

# initialisation des filtres
$params = array('language' => $okt->user->language, 'active' => 2);
$okt->faq->filters->setQuestionsParams($params);

# création des filtres
$okt->faq->filters->getFilters();

# initialisation de la pagination
$num_filtered_questions = $okt->faq->getQuestions($params,true);

$pager = new adminPager($okt->faq->filters->params->page, $num_filtered_questions, $okt->faq->filters->params->nb_per_page);

$num_pages = $pager->getNbPages();

$okt->faq->filters->normalizePage($num_pages);

$params['limit'] = (($okt->faq->filters->params->page-1)*$okt->faq->filters->params->nb_per_page).','.$okt->faq->filters->params->nb_per_page;


# récupération des questions
$list = $okt->faq->getQuestions($params);


# ajout de boutons
$okt->page->addButton('faqBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_display_filters'),
	'url' 			=> '#',
	'ui-icon' 		=> 'search',
	'active' 		=> $okt->faq->filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));


# Filters control
if ($okt->faq->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->faq->filters->params->show_filters);
}
elseif ($okt->faq->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title: '".html::escapeJS(__('m_faq_question_display_filters'))."',
			autoOpen: false,
			modal: true,
			width: 500,
			height: 280
		});

		$('#filter-control').click(function() {
			$('#filters-form').dialog('open');
		})
	");
}


# Confirmation
$okt->page->messages->success('deleted',__('m_faq_question_deleted'));

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('faqBtSt'); ?>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_faq_question_display_filters')?></legend>

		<?php echo $okt->faq->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','faq') ?>
		<?php echo form::hidden('action','index') ?>
		<input type="submit" name="<?php echo $okt->faq->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display')?>" />
		<a href="module.php?m=faq&amp;action=index&amp;init_filters=1"><?php _e('c_c_reset_filters')?></a>
		</p>
	</fieldset>
</form>

<?php if ($list->isEmpty()) : ?>
<p><?php _e('m_faq_no_question') ?></p>
<?php else : ?>

<table class="common">
	<caption><?php _e('m_faq_list_news')?></caption>
	<thead><tr>
		<th scope="col"><?php _e('c_c_Title') ?></th>
		<?php if ($okt->faq->config->enable_categories) : ?>
		<th scope="col"><?php _e('m_faq_section') ?></th>
		<?php endif; ?>
		<th scope="col"><?php _e('c_c_Actions') ?></th>
	</tr></thead>
	<tbody>
	<?php $count_line = 0;
	while ($list->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd';
		$count_line++;
	?>
	<tr>
		<th scope="row" class="<?php echo $td_class ?> fake-td"><a href="module.php?m=faq&amp;action=edit&amp;questions_id=<?php echo $list->id ?>"><?php echo html::escapeHTML($list->title) ?></a></th>
		<?php if ($okt->faq->config->enable_categories) : ?>
		<td class="<?php echo $td_class ?>"><?php echo html::escapeHTML($list->category) ?></td>
		<?php endif; ?>
		<td class="<?php echo $td_class ?> small">
			<ul class="actions">
				<li><a href="module.php?m=faq&amp;action=index&amp;switch_status=<?php echo $list->id ?>"
				title="<?php printf(__('m_faq_switch_visibility_%s'), html::escapeHTML($list->title)) ?>"
					<?php if ($list->active == 0) : ?>
					class="icon cross"><?php _e('c_c_status_Offline')?></a>
					<?php else : ?>
					class="icon tick"><?php _e('c_c_status_Online')?></a>
					<?php endif; ?>
				</li>

				<li><a href="module.php?m=faq&amp;action=edit&amp;questions_id=<?php echo $list->id ?>"
				title="<?php printf(__('m_faq_edit_question_%s'), html::escapeHTML($list->title)) ?>"
				class="icon pencil"><?php _e('c_c_action_Edit')?></a></li>

				<?php if ($okt->checkPerm('faq_remove')) : ?>
				<li><a href="module.php?m=faq&amp;action=delete&amp;questions_id=<?php echo $list->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(__('m_faq_delete_confirm')) ?>')"
				title="<?php printf(__('m_faq_delete_question_%s'), html::escapeHTML($list->title)) ?>"
				class="icon delete"><?php _e('c_c_action_Delete')?></a></li>
				<?php endif; ?>
			</ul>
		</td>
	</tr>
	<?php endwhile; ?>
	</tbody>
</table>

<?php if ($num_pages > 1) : ?>
<ul class="pagination"><?php echo $pager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>