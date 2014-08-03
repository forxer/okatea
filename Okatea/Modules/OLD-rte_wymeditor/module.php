<?php
/**
 * @ingroup okt_module_rte_wymeditor
 * @brief La classe principale du module.
 *
 */
use Okatea\Tao\Modules\Module;

class module_rte_wymeditor extends Module
{

	const wym_version = '0.5';
	//	const wym_version = '1.0.beta.3';
	public $config = null;

	protected function prepend()
	{
		# configuration
		//		$this->config = $this->okt->newConfig('conf_wymeditor');
	}

	protected function prepend_admin()
	{
		$this->okt->page->addRte('wymeditor', 'WYMeditor', array(
			'module_rte_wymeditor',
			'wymeditor'
		));
	}

	protected function prepend_public()
	{
	}

	protected function loadL10n()
	{
	}

	public static function wymeditor($element = 'textarea', $user_options = array())
	{
		global $okt;
		
		$options = array(
			'lang' => $okt['visitor']->language,
			//			'stylesheet' => 'styles.css',
			'skin' => 'compact',
			'updateSelector' => 'input:submit.button',
			/**
			 * @TODO : ce selecteur n'est pas bon ! trop général
			 */
			'updateEvent' => 'click',
			'plain/text' => '
				postInit: function(wym) {
					wym.hovertools();          //activate hovertools
					wym.resizable();           //and resizable plugins
				}
			'
		);
		
		if (! empty($user_options))
		{
			$options = array_merge($options, $user_options);
		}
		
		$okt->page->js->addFile('http://code.jquery.com/jquery-migrate-1.2.1.js');
		$okt->page->js->addFile($this->okt['modules_url'] . '/rte_wymeditor/' . self::wym_version . '/jquery.wymeditor.min.js');
		$okt->page->js->addFile($this->okt['modules_url'] . '/rte_wymeditor/' . self::wym_version . '/plugins/hovertools/jquery.wymeditor.hovertools.js');
		$okt->page->js->addFile($this->okt['modules_url'] . '/rte_wymeditor/' . self::wym_version . '/plugins/resizable/jquery.wymeditor.resizable.js');
		
		$okt->page->js->addReady('
			jQuery("' . $element . '").wymeditor(' . json_encode($options) . ');
		');
	}
}
