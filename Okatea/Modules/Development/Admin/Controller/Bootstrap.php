<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Development\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Modules\Development\Bootstrap\Module;

class Bootstrap extends Controller
{
	protected $aBootstrapData;

	public function page()
	{
		if (!$this->okt->checkPerm('m_development_perm_usage') || !$this->okt->checkPerm('m_development_perm_bootstrap')) {
			return $this->serve401();
		}

		$this->init();

		$this->handleRequest();

		return $this->render('Development/Admin/Templates/Bootstrap', array(
			'aBootstrapData' => $this->aBootstrapData
		));
	}

	protected function init()
	{
		# Modules locales
		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/'.$this->okt->user->language.'/bootstrap');

		$this->aBootstrapData = array(
			'name' 				=> 'My new module',
			'name_fr' 			=> 'Mon nouveau module',
			'description' 		=> 'A module that does nothing yet.',
			'description_fr' 	=> 'Un module qui ne fait rien pour le moment.',
			'author' 			=> 'okatea.org',
			'version' 			=> '0.1',

			'licence' 			=> 'none',

			'locales' => array(
				'en' => array(
					1 => 'Items',
					2 => 'Item',
					3 => 'items',
					4 => 'item',
					5 => 'an item',
					6 => 'no item',
					7 => 'the item',
					8 => 'The item',
					9 => 'of the item',
					10 => 'this item'
				),
				'fr' => array(
					1 => 'Éléments',
					2 => 'Élément',
					3 => 'éléments',
					4 => 'élément',
					5 => 'un élément',
					6 => 'aucun élément',
					7 => 'l’élément',
					8 => 'L’élément',
					9 => 'de l’élément',
					10 => 'cet élément'
				)
			),

			'l10n_fem' 			=> false
		);
	}

	protected function handleRequest()
	{
		$bSimple = $this->request->request->has('simple');
		$bAdvanced = $this->request->request->has('advanced');

		# Bootstrap a module
		if (!$bSimple && !$bAdvanced) {
			return false;
		}

		$this->aBootstrapData = array(
			'name' 				=> $this->request->request->get('bootstrap_module_name'),
			'name_fr' 			=> $this->request->request->get('bootstrap_module_name_fr'),
			'version' 			=> $this->request->request->get('bootstrap_module_version'),
			'description' 		=> $this->request->request->get('bootstrap_module_description'),
			'description_fr' 	=> $this->request->request->get('bootstrap_module_description_fr'),
			'author' 			=> $this->request->request->get('bootstrap_module_author'),

			'licence' 			=> $this->request->request->get('bootstrap_module_licence'),

			'locales' => array(
				'en' => array(
					1 => $this->request->request->get('bootstrap_module_l10n_1_en'),
					2 => $this->request->request->get('bootstrap_module_l10n_2_en'),
					3 => $this->request->request->get('bootstrap_module_l10n_3_en'),
					4 => $this->request->request->get('bootstrap_module_l10n_4_en'),
					5 => $this->request->request->get('bootstrap_module_l10n_5_en'),
					6 => $this->request->request->get('bootstrap_module_l10n_6_en'),
					7 => $this->request->request->get('bootstrap_module_l10n_7_en'),
					8 => $this->request->request->get('bootstrap_module_l10n_8_en'),
					9 => $this->request->request->get('bootstrap_module_l10n_9_en'),
					10 => $this->request->request->get('bootstrap_module_l10n_10_en')
				),
				'fr' => array(
					1 => $this->request->request->get('bootstrap_module_l10n_1_fr'),
					2 => $this->request->request->get('bootstrap_module_l10n_2_fr'),
					3 => $this->request->request->get('bootstrap_module_l10n_3_fr'),
					4 => $this->request->request->get('bootstrap_module_l10n_4_fr'),
					5 => $this->request->request->get('bootstrap_module_l10n_5_fr'),
					6 => $this->request->request->get('bootstrap_module_l10n_6_fr'),
					7 => $this->request->request->get('bootstrap_module_l10n_7_fr'),
					8 => $this->request->request->get('bootstrap_module_l10n_8_fr'),
					9 => $this->request->request->get('bootstrap_module_l10n_9_fr'),
					10 => $this->request->request->get('bootstrap_module_l10n_10_fr')
				)
			),

			'l10n_fem' => $this->request->request->has('bootstrap_module_l10n_fem')
		);

		try
		{
			if (empty($this->aBootstrapData['name'])) {
				throw new \Exception(__('m_development_bootstrap_need_en_name'));
			}

			if (empty($this->aBootstrapData['name_fr'])) {
				throw new \Exception(__('m_development_bootstrap_need_fr_name'));
			}

			if (empty($this->aBootstrapData['version'])) {
				throw new \Exception(__('m_development_bootstrap_need_number_version'));
			}

			$bootstraper = null;

			# Bootstrap a simple module
			if ($bSimple) {
				$bootstraper = new Module\Simple();
				$bootstraper->setTemplatesDir(__DIR__.'/../../Bootstrap/Module/tpl/simple');

			}
			# Bootstrap an advanced module
			elseif ($bAdvanced)
			{
				$bootstraper = new Module\Advanced();
				$bootstraper->setTemplatesDir(__DIR__.'/../../Bootstrap/Module/tpl/advanced');
			}

			if (null === $bootstraper || !$bootstraper instanceof Module\Module)
			{
				throw new \Exception('There is no bootstraper instance.');
				return false;
			}

			$bootstraper
				->setName($this->aBootstrapData['name'])
				->setNameFr($this->aBootstrapData['name_fr'])
				->setDescription($this->aBootstrapData['description'])
				->setDescriptionFr($this->aBootstrapData['description_fr'])
				->setAuthor($this->aBootstrapData['author'])
				->setVersion($this->aBootstrapData['version'])
				->setLicence($this->aBootstrapData['licence'])
				->setLocales(array(
					'en' => array(
						1 => $this->aBootstrapData['locales']['en'][1],
						2 => $this->aBootstrapData['locales']['en'][2],
						3 => $this->aBootstrapData['locales']['en'][3],
						4 => $this->aBootstrapData['locales']['en'][4],
						5 => $this->aBootstrapData['locales']['en'][5],
						6 => $this->aBootstrapData['locales']['en'][6],
						7 => $this->aBootstrapData['locales']['en'][7],
						8 => $this->aBootstrapData['locales']['en'][8],
						9 => $this->aBootstrapData['locales']['en'][9],
						10 => $this->aBootstrapData['locales']['en'][10]
					),
					'fr' => array(
						1 => $this->aBootstrapData['locales']['fr'][1],
						2 => $this->aBootstrapData['locales']['fr'][2],
						3 => $this->aBootstrapData['locales']['fr'][3],
						4 => $this->aBootstrapData['locales']['fr'][4],
						5 => $this->aBootstrapData['locales']['fr'][5],
						6 => $this->aBootstrapData['locales']['fr'][6],
						7 => $this->aBootstrapData['locales']['fr'][7],
						8 => $this->aBootstrapData['locales']['fr'][8],
						9 => $this->aBootstrapData['locales']['fr'][9],
						10 => $this->aBootstrapData['locales']['fr'][10]
					),
					'fem' => $this->aBootstrapData['l10n_fem']
				))
				->setModulesDir($this->okt->options->get('modules_dir'));

			$bootstraper->build();

			$this->okt->page->flash->success(__('m_development_bootstrap_success'));

			$this->redirect($this->generateUrl('Development_bootstrap'));
		}
		catch (Exception $e)
		{
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}
}
