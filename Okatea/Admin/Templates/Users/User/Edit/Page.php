<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateAdminUrl('Users_index'));

$okt->page->addGlobalTitle(sprintf(__('c_a_users_user_%s'), $aPageData['user']['username']));

if ($aPageData['bWaitingValidation'])
{
	$okt->page->warnings->set(__('c_a_users_user_in_wait_of_validation'));
}

# button set
$okt->page->setButtonset('users', array(
	'id' => 'users-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateAdminUrl('Users_index'),
			'ui-icon' => 'arrowreturnthick-1-w'
		),
		array(
			'permission' => true,
			'title' => __('c_a_users_Add_user'),
			'url' => $view->generateAdminUrl('Users_add'),
			'ui-icon' => 'plusthick'
		),
		array(
			'permission' => $aPageData['bWaitingValidation'],
			'title' => __('c_a_users_validate_this_user'),
			'url' => $view->generateAdminUrl('Users_edit', array(
				'user_id' => $aPageData['user']['id']
			)) . '?validate=1',
			'ui-icon' => 'check'
		),
		array(
			'permission' => $okt['visitor']->checkPerm('users_delete'),
			'title' => __('c_c_action_Delete'),
			'url' => $view->generateAdminUrl('Users_index') . '?delete=' . $aPageData['user']['id'],
			'ui-icon' => 'closethick',
			'onclick' => 'return window.confirm(\'' . $view->escapeJS(__('c_a_users_confirm_user_deletion')) . '\')'
		)
	)
));

# Tabs
$okt->page->tabs();

?>

<?php
# buttons set
echo $okt->page->getButtonSet('users');
?>

<div id="tabered">
	<ul>
		<?php foreach ($aPageData['tabs'] as $aTabInfos) : ?>
		<li><a href="#<?php
			
			echo $aTabInfos['id']?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
		<?php endforeach; ?>
	</ul>

	<?php foreach ($aPageData['tabs'] as $sTabUrl => $aTabInfos) : ?>
	<div id="<?php echo $aTabInfos['id'] ?>">
		<?php echo $aTabInfos['content']?>
	</div>
	<!-- #<?php echo $aTabInfos['id'] ?> -->
	<?php endforeach; ?>

</div>
<!-- #tabered -->
