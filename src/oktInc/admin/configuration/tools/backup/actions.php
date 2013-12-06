<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil de backup (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

# création d'un fichier de backup
if (!empty($_GET['make_backup']))
{
	$sFilename = $sBackupFilenameBase.'-'.date('Y-m-d-H-i').'.zip';

	$fp = fopen(OKT_ROOT_PATH.'/'.$sFilename,'wb');
	if ($fp === false) {
		$okt->error->set(__('c_a_tools_backup_unable_write_file'));
	}

	try {
//		@ini_set('memory_limit',-1);
		set_time_limit(0);

		$zip = new fileZip($fp);

		//$zip->addExclusion('#(^|/).(.*?)_(m|s|sq|t).jpg$#');
		$zip->addExclusion('#(^|/)_notes$#');
		$zip->addExclusion('#(^|/)_old$#');
		$zip->addExclusion('#(^|/)_source$#');
		$zip->addExclusion('#(^|/)_sources$#');
		$zip->addExclusion('#(^|/).svn$#');
		$zip->addExclusion('#(^|/)oktCache$#');
		$zip->addExclusion('#(^|/)stats$#');
		$zip->addExclusion('#(^|/)'.preg_quote($sBackupFilenameBase,'#').'(.*?).zip$#');

		$zip->addDirectory(
			OKT_ROOT_PATH,
			$sBackupFilenameBase,
			true
		);

		$zip->write();
		fclose($fp);
		$zip->close();

		http::redirect('configuration.php?action=tools&bakcup_done=1');
	}
	catch (Exception $e)
	{
		$okt->error->set($e->getMessage());
	}
}

# création d'un fichier de backup de la base de données
if (!empty($_GET['make_db_backup']))
{
	$return = '';
	$tables = $okt->db->getTables();

	foreach ($tables as $table)
	{
		$return .= 'DROP TABLE IF EXISTS '.$table.';';

		$row2 = $okt->db->fetchRow($okt->db->query('SHOW CREATE TABLE '.$table));
		$return .= "\n\n".$row2[1].";\n\n";

		$result = $okt->db->query('SELECT * FROM '.$table);
		$num_fields = $okt->db->numFields($result);

		for ($i = 0; $i < $num_fields; $i++)
		{
			while ($row = $okt->db->fetchRow($result))
			{
				$return .= 'INSERT INTO '.$table.' VALUES(';

				for ($j=0; $j<$num_fields; $j++)
				{
					if (is_null($row[$j])) {
						$return.= 'NULL';
					}
					else {
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);
						$return.= '"'.$row[$j].'"';
					}

					if ($j<($num_fields-1)) {
						$return .= ', ';
					}
				}

				$return .= ");\n";
			}
		}

		$return .= "\n-- --------------------------------------------------------\n\n";
	}

	$sFilename = $sDbBackupFilenameBase.'-'.date('Y-m-d-H-i').'.sql';

	# save the file
	$fp = fopen(OKT_ROOT_PATH.'/'.$sFilename,'wb');
	fwrite($fp,$return);
	fclose($fp);
	http::redirect('configuration.php?action=tools&bakcup_done=1');
}

# suppression d'un fichier de backup
if (!empty($_GET['delete_backup_file']) && (in_array($_GET['delete_backup_file'],$aBackupFiles) || in_array($_GET['delete_backup_file'],$aDbBackupFiles)))
{
	@unlink(OKT_ROOT_PATH.'/'.$_GET['delete_backup_file']);
	http::redirect('configuration.php?action=tools&backup_file_deleted=1');
}


# téléchargement d'un fichier de backup
if (!empty($_GET['dl_backup']) && (in_array($_GET['dl_backup'],$aBackupFiles) || in_array($_GET['dl_backup'],$aDbBackupFiles)))
{
	util::forceDownload(OKT_ROOT_PATH.'/'.$_GET['dl_backup']);
	exit;
}
