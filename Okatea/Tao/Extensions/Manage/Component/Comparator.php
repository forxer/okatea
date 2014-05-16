<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions\Manage\Component;

use Okatea\Tao\Diff\Engine as DiffEngine;
use Okatea\Tao\Diff\Renderer\Html\SideBySide as DiffRenderer;
use Okatea\Tao\Extensions\Manage\Component\ComponentBase;

class Comparator extends ComponentBase
{

	/**
	 * Comparaison des fichiers des deux dossiers donnés
	 *
	 * @param string $sSourceDir        	
	 * @param string $sDestDir        	
	 * @param boolean $bOptional        	
	 */
	public function folder($sSourceDir, $sDestDir, $bOptional = false)
	{
		if (! is_dir($sSourceDir))
		{
			return null;
		}
		
		$finder = $this->getFinder()
			->files()
			->in($sSourceDir);
		
		foreach ($finder as $file)
		{
			$sRelativePath = $file->getRelativePath();
			if (! empty($sRelativePath))
			{
				$sRelativePath .= '/';
			}
			
			$this->file($file->getFilename(), $sSourceDir . $sRelativePath, $sDestDir . $sRelativePath, false, $bOptional);
			$this->file($file->getFilename(), $sSourceDir . $sRelativePath, $sDestDir . $sRelativePath, true, $bOptional);
		}
		
		return true;
	}

	/**
	 * Comparaison de deux fichiers
	 *
	 * @param string $sFile        	
	 * @param string $sSourceDir        	
	 * @param string $sDestDir        	
	 * @param boolean $bTestBackup        	
	 * @param boolean $bOptional        	
	 * @return void
	 */
	public function file($sFile, $sSourceDir, $sDestDir, $bTestBackup = false, $bOptional = false)
	{
		$sSourceFile = $sSourceDir . $sFile;
		
		$sSourceBase = str_replace($this->okt->options->get('root_dir'), '', $sSourceDir);
		$sDestBase = str_replace($this->okt->options->get('root_dir'), '', $sDestDir);
		
		$sBaseSourceFile = $sSourceBase . $sFile;
		
		if ($bTestBackup)
		{
			$sFile .= '.bak';
		}
		
		$sBaseDestFile = $sDestBase . $sFile;
		
		if (! file_exists($sDestDir . $sFile))
		{
			if (! $bTestBackup)
			{
				$this->checklist->addItem('file_exists_' . $sFile, ($bOptional ? null : false), sprintf(__('c_a_compare_file_%s_not_exists'), '<code>' . $sBaseDestFile . '</code>'), sprintf(__('c_a_compare_file_%s_not_exists'), '<code>' . $sBaseDestFile . '</code>'));
			}
		}
		else
		{
			$l_text = file_get_contents($sSourceFile);
			$r_text = file_get_contents($sDestDir . $sFile);
			
			// Include two sample files for comparison
			$a = explode("\n", file_get_contents($sSourceFile));
			$b = explode("\n", file_get_contents($sDestDir . $sFile));
			
			// Options for generating the diff
			$options = array(
				//'ignoreWhitespace' => true,
				//'ignoreCase' => true,
			)()
;
			
			$diff = new DiffEngine($a, $b, $options);
			$opCodes = $diff->getGroupedOpcodes();
			
			if (! empty($opCodes))
			{
				$renderer = new DiffRenderer();
				$renderer->diff = $diff;
				
				$ze_string = sprintf(__('c_a_compare_file_%s_different_%s'), '<code>' . $sBaseDestFile . '</code>', $renderer->render($sBaseSourceFile, $sBaseDestFile));
				
				$this->checklist->addItem('file_' . $sFile . '_different', null, $ze_string, $ze_string);
			}
			else
			{
				$this->checklist->addItem('files_' . $sFile . '_identical', true, sprintf(__('c_a_compare_file_%s_identical'), '<code>' . $sDestBase . $sFile . '</code>'), sprintf(__('c_a_compare_file_%s_identical'), '<code>' . $sDestBase . $sFile . '</code>'));
			}
		}
	}

	/**
	 * Retourne le tableau HTML d'une comparaison de fichier.
	 *
	 * @param string $th1        	
	 * @param string $th2        	
	 * @param string $body        	
	 */
	protected static function getComparaisonTable($th1, $th2, $body)
	{
		return sprintf('<table class="diff diff_sidebyside">' . PHP_EOL . "\t" . '<tr>' . PHP_EOL . "\t\t" . '<th colspan="2">' . PHP_EOL . "\t\t\t" . '%s' . PHP_EOL . "\t\t" . '</th>' . PHP_EOL . "\t\t" . '<th colspan="2">' . PHP_EOL . "\t\t\t" . '%s' . PHP_EOL . "\t\t" . '</th>' . PHP_EOL . "\t" . '</tr>' . PHP_EOL . "\t" . '%s' . PHP_EOL . '</table>' . PHP_EOL, $th1, $th2, $body);
	}
}
