<?php
/**
 * @ingroup okt_module_antispam
 * @brief La page d'administration du module antispam
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

if (! $okt['visitor']->checkPerm('antispam'))
{
	http::redirect('index.php');
}

/*
 * Propriétés de base de la page
 */
$okt->page->addGlobalTitle(__('Antispam'), 'module.php?m=antispam');

/*
 * Initialisation des filtres
 */
oktAntispam::initFilters();
$filters = oktAntispam::$filters->getFilters();
$filter_gui = false;

try
{
	# Show filter configuration GUI
	if (! empty($_GET['f']))
	{
		if (! isset($filters[$_GET['f']]))
		{
			throw new Exception(__('m_antispam_Filter_does_not_exists'));
		}
		
		if (! $filters[$_GET['f']]->hasGUI())
		{
			throw new Exception(__('m_antispam_Filter_no_user_interface'));
		}
		
		$filter = $filters[$_GET['f']];
		
		$filter_gui = $filter->gui($filter->guiURL());
		
		$okt->page->addTitleTag(sprintf(__('m_antispam_%s_configuration'), $filter->name));
		$okt->page->addAriane($filter->name, $filter->guiURL());
		
		# button set
		$okt->page->setButtonset('antispam_filter', array(
			'id' => 'antispam-filter-buttonset',
			'type' => '', #  buttonset-single | buttonset-multi | ''
			'buttons' => array(
				array(
					'permission' => true,
					'title' => __('m_antispam_Return_to_filters'),
					'url' => 'module.php?m=antispam',
					'ui-icon' => 'arrowreturnthick-1-w'
				)
			)
		));
	}
	
	# Remove all spam
	if (! empty($_POST['delete_all']))
	{
		oktAntispam::delAllSpam($okt);
		http::redirect($p_url . '&del=1');
	}
	
	# Update filters
	if (isset($_POST['filters_upd']))
	{
		$filters_opt = array();
		$i = 0;
		foreach ($filters as $fid => $f)
		{
			$filters_opt[$fid] = array(
				false,
				$i
			);
			$i ++;
		}
		
		# Enable active filters
		if (isset($_POST['filters_active']) && is_array($_POST['filters_active']))
		{
			foreach ($_POST['filters_active'] as $v)
			{
				$filters_opt[$v][0] = true;
			}
		}
		
		# Order filters
		if (! empty($_POST['f_order']) && empty($_POST['filters_order']))
		{
			$order = $_POST['f_order'];
			asort($order);
			$order = array_keys($order);
		}
		elseif (! empty($_POST['filters_order']))
		{
			$order = explode(',', trim($_POST['filters_order'], ','));
		}
		
		if (isset($order))
		{
			foreach ($order as $i => $f)
			{
				$filters_opt[$f][1] = $i;
			}
		}
		
		# Set auto delete flag
		if (isset($_POST['filters_auto_del']) && is_array($_POST['filters_auto_del']))
		{
			foreach ($_POST['filters_auto_del'] as $v)
			{
				$filters_opt[$v][2] = true;
			}
		}
		
		oktAntispam::$filters->saveFilterOpts($filters_opt);
		
		$okt['flashMessages']->success(__('m_antispam_Filters_configuration_successfully_saved'));
		
		http::redirect('module.php?m=antispam');
	}
}
catch (\Exception $e)
{
	$okt->error->set($e->getMessage());
}

/* Affichage
----------------------------------------------------------*/

# En-tête
include OKT_ADMIN_HEADER_FILE;

# affichage de la configuration d’un filtre
if ($filter_gui !== false)
:
	
	echo $okt->page->getButtonSet('antispam_filter');
	
	echo $filter_gui;


# sinon affichage de la liste des filtres
else
:
	?>

<p><?php _e('m_antispam_filters_description')?></p>
<p><?php _e('m_antispam_admin_page_description')?></p>

<form action="module.php" method="post">
	<fieldset>
		<legend><?php _e('m_antispam_Available_spam_filters') ?></legend>

		<table class="common">
			<caption><?php _e('m_antispam_Antispam_filters_list')?></caption>
			<thead>
				<tr>
					<th scope="col"><?php _e('m_antispam_Filter_name') ?></th>
					<th scope="col"><?php _e('c_c_Description') ?></th>
					<th scope="col" class="center"><?php _e('m_antispam_Active') ?></th>
					<th scope="col" class="center"><?php _e('m_antispam_Auto_Del') ?></th>
					<th scope="col" class="center"><?php _e('m_antispam_Order') ?></th>
					<th scope="col">&nbsp;</th>
				</tr>
			</thead>
			<tbody id="filters-list">

	<?php
	$i = 0;
	foreach ($filters as $fid => $f)
	:
		$td_class = $i % 2 == 0 ? 'even' : 'odd';
		?>
		<tr id="f_<?php echo $fid ?>">
					<th scope="row" class="<?php echo $td_class ?> fake-td"><?php echo $f->name ?></th>
					<td class="<?php echo $td_class ?>"><?php echo $f->description ?></td>
					<td class="<?php echo $td_class ?> center"><?php echo form::checkbox(array('filters_active[]'),$fid,$f->active) ?></td>
					<td class="<?php echo $td_class ?> center"><?php echo form::checkbox(array('filters_auto_del[]'),$fid,$f->auto_delete) ?></td>
					<td class="<?php echo $td_class ?> center"><?php echo form::text(array('f_order['.$fid.']'),2,5,(string) $i) ?></td>
					<td class="<?php echo $td_class ?>"><?php echo ($f->hasGUI() ? '<a href="'.html::escapeHTML($f->guiURL()).'">'.__('m_antispam_Filter_configuration').'</a>' : '&nbsp;') ?></td>
				</tr>
	<?php
		
$i ++;
	endforeach
	;
	?>

	</tbody>
		</table>
		<p><?php
	
echo form::hidden('filters_order', '') . form::hidden('m', 'antispam') . Page::formtoken();
	?>
	<input type="submit" name="filters_upd"
				value="<?php _e('c_c_action_Save') ?>" />
		</p>
	</fieldset>
</form>

<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
