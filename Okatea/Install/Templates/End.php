<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Symfony\Component\Filesystem\Filesystem;

$view->extend('Layout');

?>

<p><?php _e('i_end_'.$okt['session']->get('okt_install_process_type').'_congrat') ?></p>

<p><?php printf(__('i_end_connect'), './../admin/login?user_id='.$user.'&amp;user_pwd='.$password) ?></p>


<?php

# destroy session data
$okt['session']->clear();
$okt['session']->invalidate();

# remove install dir
if ($okt['env'] === 'prod')
{
	(new Filesystem())->remove($okt['app_path'] . '/install/');
}

