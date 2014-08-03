<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Builder\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Modules\Builder\Tools\BaseTools;
use Okatea\Modules\Builder\Stepper;

class Builder extends Controller
{

	protected $stepper;

	protected $tools;

	protected $bSetMaxRessourcesCalled = false;

	protected $sInitialMemoryLimit;

	protected $sInitialMaxExecutionTime;

	public function page()
	{
		if (! $this->okt->checkPerm('okatea_builder'))
		{
			return $this->serve401();
		}
		
		$this->stepper = new Stepper($this->generateUrl('Builder_index'), $this->okt['request']->attributes->get('step'));
		
		$this->tools = new BaseTools($this->okt);
		
		$this->okt->tpl->addGlobal('stepper', $this->stepper);
		
		return $this->{$this->stepper->getCurrentStep()}();
	}

	protected function start()
	{
		return $this->render('Builder/Admin/Templates/Steps/start', array());
	}

	protected function version()
	{
		$sVersion = $this->okt->getVersion();
		$sPackageType = 'stable';
		
		if (stripos($sVersion, 'beta') !== false || stripos($sVersion, 'rc') !== false)
		{
			$sPackageType = 'dev';
		}
		
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->okt['session']->set('release_type', $this->okt['request']->request->get('type'));
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/version', array(
			'version' => $sVersion,
			'type' => $sPackageType
		));
	}

	protected function copy()
	{
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->setMaxRessources();
			
			$this->tools->getCopier()->process();
			
			$this->restoreInitialRessources();
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/copy', array());
	}

	protected function cleanup()
	{
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->setMaxRessources();
			
			$this->tools->getCleaner()->process();
			
			$this->restoreInitialRessources();
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/cleanup', array());
	}

	protected function changelog()
	{
		$sChangelog = $this->tools->getTempDir($this->okt['okt_path']) . '/CHANGELOG';
		
		if ($this->okt['request']->request->has('form_sent'))
		{
			file_put_contents($sChangelog, $this->okt['request']->request->get('changelog_editor'));
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/changelog', array(
			'sChangelog' => file_get_contents($sChangelog)
		));
	}

	protected function config()
	{
		$sConfigFile = $this->tools->getTempDir($this->okt['config_path']) . '/conf_site.yml';
		$sOptionsFile = $this->tools->getTempDir() . '/oktOptions.php';
		
		if ($this->okt['request']->request->has('form_sent'))
		{
			file_put_contents($sConfigFile, $this->okt['request']->request->get('config_editor'));
			
			file_put_contents($sOptionsFile, $this->okt['request']->request->get('options_editor'));
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/config', array(
			'sConfig' => file_get_contents($sConfigFile),
			'sOptions' => file_get_contents($sOptionsFile)
		));
	}

	protected function modules()
	{
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->setMaxRessources();
			
			$this->tools->getModules()->process();
			
			$this->restoreInitialRessources();
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/modules', array());
	}

	protected function themes()
	{
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->setMaxRessources();
			
			$this->tools->getThemes()->process();
			
			$this->restoreInitialRessources();
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/themes', array());
	}

	protected function digests()
	{
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->setMaxRessources();
			
			$this->tools->getDigests()->process();
			
			$this->restoreInitialRessources();
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/digests', array());
	}

	protected function packages()
	{
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->setMaxRessources();
			
			$this->tools->getPackages()->process();
			
			$this->restoreInitialRessources();
			
			return $this->redirect($this->generateUrl('Builder_index', array(
				'step' => $this->stepper->getNextStep()
			)));
		}
		
		return $this->render('Builder/Admin/Templates/Steps/packages', array());
	}

	protected function end()
	{
		$this->setMaxRessources();
		
		$this->okt['session']->remove('release_type');
		
		$this->tools->removeTempDir();
		
		$this->restoreInitialRessources();
		
		return $this->render('Builder/Admin/Templates/Steps/end', array());
	}

	protected function setMaxRessources()
	{
		$this->bSetMaxRessourcesCalled = true;
		
		$this->sInitialMemoryLimit = ini_get('memory_limit');
		$this->sInitialMaxExecutionTime = ini_get('max_execution_time');
		
		ini_set('memory_limit', - 1);
		ini_set('max_execution_time', 0);
	}

	protected function restoreInitialRessources()
	{
		if ($this->bSetMaxRessourcesCalled)
		{
			ini_set('memory_limit', $this->sInitialMemoryLimit);
			ini_set('max_execution_time', $this->sInitialMaxExecutionTime);
		}
	}
}
