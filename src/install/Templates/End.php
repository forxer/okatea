
<?php $view->extend('layout'); ?>


<p><?php _e('i_end_'.$okt->session->get('okt_install_process_type').'_congrat') ?></p>

<p><?php printf(__('i_end_connect'),'./../admin/connexion?user_id='.$user.'&amp;user_pwd='.$password) ?></p>


<?php
# destroy session data

$okt->session->clear();
$okt->session->invalidate();


//	if ($okt->env === 'prod')  {
//		@files::deltree(__DIR__.'/../', true);
//	}

