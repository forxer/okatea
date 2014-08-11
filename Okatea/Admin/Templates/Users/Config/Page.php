<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateAdminUrl('Users_index'));

# infos page
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (!$okt['languages']->hasUniqueLanguage())
{
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

?>

<form action="<?php echo $view->generateAdminUrl('Users_config') ?>"
	method="post">
	<div id="tabered">
		<ul>
		<?php foreach ($aPageData['Tabs'] as $aTabInfos) : ?>
			<li><a href="#<?php
			
			echo $aTabInfos['id']?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
		<?php endforeach; ?>
		</ul>

		<?php foreach ($aPageData['Tabs'] as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content']?>
		</div>
		<!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>

	</div>
	<!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save') ?>" />
	</p>
</form>
