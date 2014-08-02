<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Controller;

use DirectoryIterator;
use Okatea\Tao\Database\XmlSql;
use Okatea\Install\Controller;
use Okatea\Tao\Html\Checklister;

class Database extends Controller
{
	public function page()
	{
		$this->okt->startDatabase();

		$oChecklist = new Checklister();

		foreach (new DirectoryIterator($this->okt['okt_dir'] . '/Install/SqlSchema/') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || ! $oFileInfo->isFile() || $oFileInfo->getExtension() !== 'xml')
			{
				continue;
			}

			$xsql = new XmlSql($this->okt->db, file_get_contents($oFileInfo->getPathname()), $oChecklist, $this->okt['session']->get('okt_install_process_type'));
			$xsql->replace('{{PREFIX}}', $this->okt->db->prefix);
			$xsql->execute();
		}

		return $this->render('Database', [
			'title' => __('i_db_tables_title'),
			'oChecklist' => $oChecklist
		]);
	}
}
