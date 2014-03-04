<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Modules\Builder\Stepper;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class Builder extends Controller
{
	protected $storage;
	protected $stepper;

	public function page()
	{
		if (!$this->okt->checkPerm('okatea_builder')) {
			return $this->serve401();
		}

		$this->stepper = new Stepper($this->generateUrl('Builder_index'), $this->request->attributes->get('step'));

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

		if ($this->request->request->has('config_sent'))
		{
			$this->session->set('release_type', $this->request->request->get('type'));

			return $this->redirect($this->generateUrl('Builder_index', array('step' => $this->stepper->getNextStep())));
		}

		return $this->render('Builder/Admin/Templates/Steps/version', array(
			'version' => $sVersion,
			'type' => $sPackageType
		));
	}

	protected function end()
	{
		$this->session->remove('release_type');

		return $this->render('Builder/Admin/Templates/Steps/end', array(
		));
	}
}
