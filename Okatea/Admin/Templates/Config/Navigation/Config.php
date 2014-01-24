<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> $view->generateUrl('config_navigation').'?do=index',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));


$okt->page->addGlobalTitle(__('c_a_config_navigation_config'));

?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<form action="<?php echo $view->generateUrl('config_navigation') ?>" method="post">

	<?php echo $oTemplates->getHtmlConfigUsablesTemplates(); ?>

	<p><?php echo form::hidden('do', 'config'); ?>
	<?php echo form::hidden('sended', 1) ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

