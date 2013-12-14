<?php
/**
 * @ingroup okt_module_antispam
 * @brief La classe d'installation du module Antispam.
 *
 */

use Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_antispam extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'antispam',
		));

		require_once __DIR__.'/../inc/class.spamfilter.php';
		require_once __DIR__.'/../filters/class.filter.words.php';

		$_o = new oktFilterWords($this->okt);

		try {
			$_o->defaultWordsList();
			$done = true;
		} catch (Exception $e) {
			$done = null;
		}

		# liste de mots par défaut
		$this->checklist->addItem(
			'default_words_list',
			$done,
			'Create default words list',
			'Cannot create default words list'
		);

		unset($_o);
	}

} # class
