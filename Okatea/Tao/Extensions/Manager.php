<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions;

use Okatea\Tao\Database\Recordset;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Okatea\Tao\Application;

class Manager
{
	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The name of the extensions table.
	 *
	 * @var string
	 */
	protected $sExtensionsTable;

	/**
	 * The type of extensions.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The directory path extensions.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * List of all extensions in the file system.
	 *
	 * @var array
	 */
	protected $aAll;

	/**
	 * Extensions collection instance.
	 *
	 * @var Okatea\Tao\Extensions\Collection
	 */
	protected $collection;

	public function __construct(Application $okt, $sType, $sPath)
	{
		$this->okt = $okt;

		$this->sExtensionsTable = $okt['config']->database_prefix . 'core_extensions';

		$this->type = $sType;

		$this->path = $sPath;
	}

	/**
	 * Returns a list of extensions from the file system.
	 *
	 * @return array
	 */
	public function getFromFileSystem()
	{
		$finder = (new Finder())
			->files()
			->in($this->path)
			->depth('== 1')
			->name('_define.php');

		$aExstensions = [];

		foreach ($finder as $file)
		{
			$sId = $file->getRelativePath();

			$aInfos = require $file->getRealpath();

			$aExstensions[$sId] = [
				'id'            => $sId,
				'root'          => $this->path . '/' . $sId,
				'name'          => (!empty($aInfos['name']) ? $aInfos['name'] : $sId),
				'desc'          => (!empty($aInfos['desc']) ? $aInfos['desc'] : null),
				'version'       => (!empty($aInfos['version']) ? $aInfos['version'] : null),
				'author'        => (!empty($aInfos['author']) ? $aInfos['author'] : null),
				'priority'      => (!empty($aInfos['priority']) ? (integer) $aInfos['priority'] : 1000),
				'updatable'     => (!empty($aInfos['updatable']) ? (boolean) $aInfos['updatable'] : true)
			];
		}

		return $aExstensions;
	}

	/**
	 * Returns a list of all the extensions in the file system.
	 *
	 * @return array
	 */
	public function getAll()
	{
		if (null === $this->aAll) {
			$this->aAll = $this->getFromFileSystem();
		}

		return $this->aAll;
	}

	/**
	 * Resets the list of all the extensions in the file system.
	 *
	 * @return void
	 */
	public function resetAll()
	{
		$this->aAll = null;
	}

	/**
	 * Returns a list of extensions registered in the database.
	 *
	 * @param array $aParams
	 * @return array
	 */
	public function getFromDatabase(array $aParams = [])
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('id', 'name', 'description', 'author', 'version', 'priority', 'updatable', 'status', 'type')
			->from($this->sExtensionsTable)
			->where('type = :type')
			->setparameter('type', $this->type)
			->orderBy('priority', 'ASC')
			->addOrderBy('id', 'ASC')
		;

		if (!empty($aParams['id']))
		{
			$queryBuilder
				->andWhere('id = :id')
				->setParameter('id', $aParams['id']);
		}

		if (!empty($aParams['status']))
		{
			$queryBuilder
				->andWhere('status = :status')
				->setParameter('status', (integer) $aParams['status']);
		}

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Returns informations of a given extension registered in the database.
	 *
	 * @param string $sExtensionId
	 * @return array
	 */
	public function getExtensionFromDatabase($sExtensionId)
	{
		$aExtension = $this->getFromDatabase([
			'id' => $sExtensionId
			]);

		return isset($aExtension[0]) ? $aExtension[0] : null;
	}

	/**
	 * Returns the list of installed extensions.
	 *
	 * @return array
	 */
	public function getInstalled()
	{
		$aInstalled = $this->getFromDatabase();

		$aReturn = [];

		foreach ($aInstalled as $aExtension)
		{
			$aReturn[$aExtension['id']] = [
				'id'            => $aExtension['id'],
				'root'          => $this->path . '/' . $aExtension['id'],
				'name'          => $aExtension['name'],
				'name_l10n'     => __($aExtension['name']),
				'desc'          => $aExtension['description'],
				'desc_l10n'     => __($aExtension['description']),
				'author'        => $aExtension['author'],
				'version'       => $aExtension['version'],
				'priority'      => $aExtension['priority'],
				'status'        => $aExtension['status'],
				'updatable'     => $aExtension['updatable']
			];
		}

		return $aReturn;
	}

	/**
	 * Add an extension to the database.
	 *
	 * @param string $id
	 * @param string $version
	 * @param string $name
	 * @param string $desc
	 * @param string $author
	 * @param integer $priority
	 * @param integer $status
	 * @return booolean
	 */
	public function addExtension($id, $version, $name = '', $desc = '', $author = '', $priority = 1000, $status = 0)
	{
		$this->okt['db']->insert($this->sExtensionsTable, [
			'id' 			=> $id,
			'name' 			=> $name,
			'description' 	=> $desc,
			'author'		=> $author,
			'version' 		=> $version,
			'priority' 		=> (integer) $priority,
			'status' 		=> (integer) $status,
			'type' 			=> $this->type,
		]);

		return true;
	}

	/**
	 * Update an extension into the database.
	 *
	 * @param string $id
	 * @param string $version
	 * @param string $name
	 * @param string $desc
	 * @param string $author
	 * @param integer $priority
	 * @param integer $status
	 * @return boolean
	 */
	public function updateExtension($id, $version, $name = '', $desc = '', $author = '', $priority = null, $status = null)
	{
		$aExtension = $this->getExtensionFromDatabase($id);

		if (empty($aExtension)) {
			return false;
		}

		$this->okt['db']->update($this->sExtensionsTable,
			[
				'name' 			=> $name ?: $aExtension['name'],
				'description' 	=> $desc ?: $aExtension['description'],
				'author' 		=> $author ?: $aExtension['author'],
				'version' 		=> $version,
				'priority' 		=> $priority ?: $aExtension['priority'],
				'status' 		=> $status ?: $aExtension['status']
			],
			[
				'id' => $id
			]
		);

		return true;
	}

	/**
	 * Enable an extension.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function enableExtension($sExtensionId)
	{
		$aExtension = $this->getExtensionFromDatabase($sExtensionId);

		if (empty($aExtension)) {
			return false;
		}

		$this->okt['db']->update($this->sExtensionsTable,
			[ 'status' => 1 ],
			[ 'id' => $sExtensionId ]
		);

		return true;
	}

	/**
	 * Disable an extension.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function disableExtension($sExtensionId)
	{
		$aExtension = $this->getExtensionFromDatabase($sExtensionId);

		if (empty($aExtension)) {
			return false;
		}

		$this->okt['db']->update($this->sExtensionsTable,
			[ 'status' => 0 ],
			[ 'id' => $sExtensionId ]
		);

		return true;
	}

	/**
	 * Delete an extension from the database.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function deleteExtension($sExtensionId)
	{
		$aExtension = $this->getExtensionFromDatabase($sExtensionId);

		if (empty($aExtension)) {
			return false;
		}

		$this->okt['db']->delete($this->sExtensionsTable, ['id' => $sExtensionId]);

		return true;
	}

	/**
	 * Install an extension from a zip file.
	 *
	 * @param string $zip_file
	 * @param Collection $extensions
	 */
	public function installPackage($zip_file, $extensions)
	{
		$zip = new \fileUnzip($zip_file);
		$zip->getList(false, '#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db)(/|$)#');

		$zip_root_dir = $zip->getRootDir();

		if ($zip_root_dir !== false)
		{
			$target = dirname($zip_file);
			$destination = $target . '/' . $zip_root_dir;
			$define = $zip_root_dir . '/_define.php';
			$has_define = $zip->hasFile($define);
		}
		else
		{
			$target = dirname($zip_file) . '/' . preg_replace('/\.([^.]+)$/', '', basename($zip_file));
			$destination = $target;
			$define = '_define.php';
			$has_define = $zip->hasFile($define);
		}

		if ($zip->isEmpty())
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('Empty module zip file.'));
		}

		if (!$has_define)
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('The zip file does not appear to be a valid module.'));
		}

		$ret_code = 1;

		if (is_dir($destination))
		{
			copy($target . '/_define.php', $target . '/_define.php.bak');

			# test for update
			$sandbox = clone $extensions;
			$zip->unzip($define, $target . '/_define.php');

			$sandbox->resetCompleteList();
			$sandbox->requireDefine($target, basename($destination));
			unlink($target . '/_define.php');
			$new_modules = $sandbox->getCompleteList();
			$old_modules = $extensions->getModulesFromFileSystem();

			$extensions->disableModule(basename($destination));
			$extensions->generateCacheList();

			if (!empty($new_modules))
			{
				$tmp = array_keys($new_modules);
				$id = $tmp[0];
				$cur_module = $old_modules[$id];

				if (!empty($cur_module) && $new_modules[$id]['version'] != $cur_module['version'])
				{
					(new Filesystem())->remove($destination);

					$ret_code = 2;
				}
				else
				{
					$zip->close();
					unlink($zip_file);

					if (file_exists($target . '/_define.php.bak'))
					{
						rename($target . '/_define.php.bak', $target . '/_define.php');
					}

					throw new \Exception(sprintf(__('Unable to upgrade "%s". (same version)'), basename($destination)));
				}
			}
			else
			{
				$zip->close();
				unlink($zip_file);

				if (file_exists($target . '/_define.php.bak'))
				{
					rename($target . '/_define.php.bak', $target . '/_define.php');
				}

				throw new \Exception(sprintf(__('Unable to read new _define.php file')));
			}
		}

		$zip->unzipAll($target);
		$zip->close();
		unlink($zip_file);

		return $ret_code;
	}
}
