<?php
/**
 * Page d'administration des modules (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_MODULE')) die;


# Bootstrap a module
if (!empty($_POST['simple']) || !empty($_POST['advanced']))
{
	$bootstrap_module_name = !empty($_POST['bootstrap_module_name']) ? $_POST['bootstrap_module_name'] : '';
	$bootstrap_module_name_fr = !empty($_POST['bootstrap_module_name_fr']) ? $_POST['bootstrap_module_name_fr'] : '';
	$bootstrap_module_version = !empty($_POST['bootstrap_module_version']) ? $_POST['bootstrap_module_version'] : '';
	$bootstrap_module_description = !empty($_POST['bootstrap_module_description']) ? $_POST['bootstrap_module_description'] : '';
	$bootstrap_module_description_fr = !empty($_POST['bootstrap_module_description_fr']) ? $_POST['bootstrap_module_description_fr'] : '';
	$bootstrap_module_author = !empty($_POST['bootstrap_module_author']) ? $_POST['bootstrap_module_author'] : '';

	$bootstrap_module_licence = !empty($_POST['bootstrap_module_licence']) ? $_POST['bootstrap_module_licence'] : '';

	$bootstrap_module_l10n_1_en = !empty($_POST['bootstrap_module_l10n_1_en']) ? $_POST['bootstrap_module_l10n_1_en'] : '';
	$bootstrap_module_l10n_2_en = !empty($_POST['bootstrap_module_l10n_2_en']) ? $_POST['bootstrap_module_l10n_2_en'] : '';
	$bootstrap_module_l10n_3_en = !empty($_POST['bootstrap_module_l10n_3_en']) ? $_POST['bootstrap_module_l10n_3_en'] : '';
	$bootstrap_module_l10n_4_en = !empty($_POST['bootstrap_module_l10n_4_en']) ? $_POST['bootstrap_module_l10n_4_en'] : '';
	$bootstrap_module_l10n_5_en = !empty($_POST['bootstrap_module_l10n_5_en']) ? $_POST['bootstrap_module_l10n_5_en'] : '';
	$bootstrap_module_l10n_6_en = !empty($_POST['bootstrap_module_l10n_6_en']) ? $_POST['bootstrap_module_l10n_6_en'] : '';
	$bootstrap_module_l10n_7_en = !empty($_POST['bootstrap_module_l10n_7_en']) ? $_POST['bootstrap_module_l10n_7_en'] : '';
	$bootstrap_module_l10n_8_en = !empty($_POST['bootstrap_module_l10n_8_en']) ? $_POST['bootstrap_module_l10n_8_en'] : '';
	$bootstrap_module_l10n_9_en = !empty($_POST['bootstrap_module_l10n_9_en']) ? $_POST['bootstrap_module_l10n_9_en'] : '';
	$bootstrap_module_l10n_10_en = !empty($_POST['bootstrap_module_l10n_10_en']) ? $_POST['bootstrap_module_l10n_10_en'] : '';

	$bootstrap_module_l10n_1_fr = !empty($_POST['bootstrap_module_l10n_1_fr']) ? $_POST['bootstrap_module_l10n_1_fr'] : '';
	$bootstrap_module_l10n_2_fr = !empty($_POST['bootstrap_module_l10n_2_fr']) ? $_POST['bootstrap_module_l10n_2_fr'] : '';
	$bootstrap_module_l10n_3_fr = !empty($_POST['bootstrap_module_l10n_3_fr']) ? $_POST['bootstrap_module_l10n_3_fr'] : '';
	$bootstrap_module_l10n_4_fr = !empty($_POST['bootstrap_module_l10n_4_fr']) ? $_POST['bootstrap_module_l10n_4_fr'] : '';
	$bootstrap_module_l10n_5_fr = !empty($_POST['bootstrap_module_l10n_5_fr']) ? $_POST['bootstrap_module_l10n_5_fr'] : '';
	$bootstrap_module_l10n_6_fr = !empty($_POST['bootstrap_module_l10n_6_fr']) ? $_POST['bootstrap_module_l10n_6_fr'] : '';
	$bootstrap_module_l10n_7_fr = !empty($_POST['bootstrap_module_l10n_7_fr']) ? $_POST['bootstrap_module_l10n_7_fr'] : '';
	$bootstrap_module_l10n_8_fr = !empty($_POST['bootstrap_module_l10n_8_fr']) ? $_POST['bootstrap_module_l10n_8_fr'] : '';
	$bootstrap_module_l10n_9_fr = !empty($_POST['bootstrap_module_l10n_9_fr']) ? $_POST['bootstrap_module_l10n_9_fr'] : '';
	$bootstrap_module_l10n_10_fr = !empty($_POST['bootstrap_module_l10n_10_fr']) ? $_POST['bootstrap_module_l10n_10_fr'] : '';

	$bootstrap_module_l10n_fem = !empty($_POST['bootstrap_module_l10n_fem']) ? true : false;

	try
	{
		if (empty($bootstrap_module_name)) {
			throw new Exception(__('m_development_bootstrap_need_en_name'));
		}

		if (empty($bootstrap_module_name_fr)) {
			throw new Exception(__('m_development_bootstrap_need_fr_name'));
		}

		if (empty($bootstrap_module_version)) {
			throw new Exception(__('m_development_bootstrap_need_number_version'));
		}

		# Bootstrap a simple module
		if (!empty($_POST['simple'])) {
			$bootstraper = new oktModuleBootstrapSimple();
			$bootstraper->setTemplatesDir(__DIR__.'/../../tpl/simple');

		}
		# Bootstrap an advanced module
		else if (!empty($_POST['advanced']))
		{
			$bootstraper = new oktModuleBootstrapAdvanced();
			$bootstraper->setTemplatesDir(__DIR__.'/../../tpl/advanced');
		}

		$bootstraper
			->setName($bootstrap_module_name)
			->setNameFr($bootstrap_module_name_fr)
			->setDescription($bootstrap_module_description)
			->setDescriptionFr($bootstrap_module_description_fr)
			->setAuthor($bootstrap_module_author)
			->setVersion($bootstrap_module_version)
			->setLicence($bootstrap_module_licence)
			->setLocales(array(
				'en' => array(
					1 => $bootstrap_module_l10n_1_en,
					2 => $bootstrap_module_l10n_2_en,
					3 => $bootstrap_module_l10n_3_en,
					4 => $bootstrap_module_l10n_4_en,
					5 => $bootstrap_module_l10n_5_en,
					6 => $bootstrap_module_l10n_6_en,
					7 => $bootstrap_module_l10n_7_en,
					8 => $bootstrap_module_l10n_8_en,
					9 => $bootstrap_module_l10n_9_en,
					10 => $bootstrap_module_l10n_10_en
				),
				'fr' => array(
					1 => $bootstrap_module_l10n_1_fr,
					2 => $bootstrap_module_l10n_2_fr,
					3 => $bootstrap_module_l10n_3_fr,
					4 => $bootstrap_module_l10n_4_fr,
					5 => $bootstrap_module_l10n_5_fr,
					6 => $bootstrap_module_l10n_6_fr,
					7 => $bootstrap_module_l10n_7_fr,
					8 => $bootstrap_module_l10n_8_fr,
					9 => $bootstrap_module_l10n_9_fr,
					10 => $bootstrap_module_l10n_10_fr
				),
				'fem' => $bootstrap_module_l10n_fem
			))
			->setModulesDir($okt->options->get('modules_dir'));

		$bootstraper->build();

		$okt->page->flash->success(__('m_development_bootstrap_success'));

		http::redirect('module.php?m=development&action=bootstrap');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

