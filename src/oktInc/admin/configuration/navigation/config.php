<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/


/**
 * Page de configuration des menus de navigation
 *
 * @addtogroup Okatea
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;
use Tao\Themes\TemplatesSet;


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Gestionnaire de templates
$oTemplates = new TemplatesSet($okt,
	$okt->config->navigation_tpl,
	'navigation',
	'navigation',
	'configuration.php?action=navigation&amp;do=config&amp;'
);


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['sended']))
{
	$p_tpl = $oTemplates->getPostConfig();

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'navigation_tpl' => $p_tpl
		);

		try
		{
			$okt->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('configuration.php?action=navigation&do=config');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}



/* Affichage
----------------------------------------------------------*/

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'configuration.php?action=navigation&amp;do=index',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));


$okt->page->addGlobalTitle(__('c_a_config_navigation_config'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<form action="configuration.php" method="post">

	<?php echo $oTemplates->getHtmlConfigUsablesTemplates(); ?>

	<p><?php echo form::hidden('action', 'navigation') ?>
	<?php echo form::hidden('do', 'config'); ?>
	<?php echo form::hidden('sended', 1) ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
