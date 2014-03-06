<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Modules\Builder\BuilderTools;
use Okatea\Modules\Builder\Stepper;

class Builder extends Controller
{
	protected $builderTools;
	protected $stepper;

	public function page()
	{
		if (!$this->okt->checkPerm('okatea_builder')) {
			return $this->serve401();
		}

		$this->stepper = new Stepper($this->generateUrl('Builder_index'), $this->request->attributes->get('step'));

		$this->builderTools = new BuilderTools($this->okt);

		$this->okt->tpl->addGlobal('stepper', $this->stepper);

		return $this->{$this->stepper->getCurrentStep()}();
	}

	protected function start()
	{

		return $this->render('Builder/Admin/Templates/Steps/start', array(
		));
	}

	protected function version()
	{
		$sVersion = $this->okt->getVersion();
		$sPackageType = 'stable';

		if (stripos($sVersion, 'beta') !== false || stripos($sVersion, 'rc') !== false) {
			$sPackageType = 'dev';
		}

		if ($this->request->request->has('form_sent'))
		{
			$this->session->set('release_type', $this->request->request->get('type'));

			return $this->redirect($this->generateUrl('Builder_index', array('step' => $this->stepper->getNextStep())));
		}

		return $this->render('Builder/Admin/Templates/Steps/version', array(
			'version' => $sVersion,
			'type' => $sPackageType
		));
	}

	protected function copy()
	{
		if ($this->request->request->has('form_sent'))
		{
			$this->builderTools->copy();

			return $this->redirect($this->generateUrl('Builder_index', array('step' => $this->stepper->getNextStep())));
		}

		return $this->render('Builder/Admin/Templates/Steps/copy', array(
		));
	}

	protected function cleanup()
	{
		if ($this->request->request->has('form_sent'))
		{
			$this->builderTools->cleanup();

			return $this->redirect($this->generateUrl('Builder_index', array('step' => $this->stepper->getNextStep())));
		}

		return $this->render('Builder/Admin/Templates/Steps/cleanup', array(
		));
	}

	protected function config()
	{
		$sConfigFile = $this->builderTools->getTempDir($this->okt->options->config_dir).'/conf_site.yml';

		if ($this->request->request->has('form_sent'))
		{
			file_put_contents($sConfigFile, $this->request->request->get('editor'));

			return $this->redirect($this->generateUrl('Builder_index', array('step' => $this->stepper->getNextStep())));
		}

		return $this->render('Builder/Admin/Templates/Steps/config', array(
			'sConfig' => file_get_contents($sConfigFile)
		));
	}

	protected function modules()
	{
		if ($this->request->request->has('form_sent'))
		{
			$this->builderTools->modules();

			return $this->redirect($this->generateUrl('Builder_index', array('step' => $this->stepper->getNextStep())));
		}

		return $this->render('Builder/Admin/Templates/Steps/modules', array(
		));
	}

	protected function themes()
	{
		if ($this->request->request->has('form_sent'))
		{

		}

		return $this->render('Builder/Admin/Templates/Steps/themes', array(
		));
	}

	protected function end()
	{
		$this->session->remove('release_type');

		return $this->render('Builder/Admin/Templates/Steps/end', array(
		));
	}
}
