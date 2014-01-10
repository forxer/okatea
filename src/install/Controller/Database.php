<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install\Controller;

use Okatea\Install\Controller;
use Tao\Database\XmlSql;
use Tao\Html\CheckList;

class Database extends Controller
{
	public function page()
	{
		$db = $this->okt->getDb();

		$oChecklist = new CheckList();

		foreach (new \DirectoryIterator($this->okt->options->get('inc_dir').'/sql_schema/') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || !$oFileInfo->isFile() || $oFileInfo->getExtension() !== 'xml') {
				continue;
			}

			$xsql = new XmlSql($db, file_get_contents($oFileInfo->getPathname()), $oChecklist, $this->session->get('okt_install_process_type'));
			$xsql->replace('{{PREFIX}}',$db->prefix);
			$xsql->execute();
		}


		return $this->render('Database', array(
			'oChecklist' => $oChecklist
		));
	}
}
