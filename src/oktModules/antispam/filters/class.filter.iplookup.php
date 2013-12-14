<?php
/**
 * @ingroup okt_module_antispam
 * @brief Filtre IP lookup
 *
 */

use Tao\Forms\StaticFormElements as form;

class oktFilterIpLookup extends oktSpamFilter
{
	public $name = 'IP Lookup';
	public $has_gui = true;

	private $default_bls = 'sbl-xbl.spamhaus.org , bsb.spamlookup.net';

	public function __construct($okt)
	{
		parent::__construct($okt);

//		if (defined('DC_DNSBL_SUPER') && DC_DNSBL_SUPER && !$okt->auth->isSuperAdmin()) {
//			$this->has_gui = false;
//		}
		$this->has_gui = false;
	}

	protected function setInfo()
	{
		$this->description = __('m_antispam_Checks_sender_IP_address');
	}

	public function getStatusMessage($status)
	{
		return sprintf(__('m_antispam_Filtered_by_%1$s_with_server_%2$s'),$this->guiLink(),$status);
	}

	public function isSpam($type,$author,$email,$site,$ip,$content,&$status)
	{
		if (!$ip || long2ip(ip2long($ip)) != $ip) {
			return;
		}

		$match = array();

		$bls = $this->getServers();
		$bls = preg_split('/\s*,\s*/',$bls);

		foreach ($bls as $bl)
		{
			if ($this->dnsblLookup($ip,$bl)) {
				$match[] = $bl;
			}
		}

		if (!empty($match)) {
			$status = substr(implode(', ',$match),0,128);
			return true;
		}
	}

	public function gui($url)
	{
		$bls = $this->getServers();

		if (isset($_POST['bls']))
		{
			try {
				$this->okt->blog->settings->setNameSpace('antispam');
				$this->okt->blog->settings->put('antispam_dnsbls',$_POST['bls'],'string','Antispam DNSBL servers',true,false);
				http::redirect($url.'&upd=1');
			} catch (Exception $e) {
				$okt->error->add($e->getMessage());
			}
		}

		/* DISPLAY
		---------------------------------------------- */
		$res = '';

		$res .=
		'<form action="'.html::escapeURL($url).'" method="post">'.
		'<fieldset><legend>' . __('m_antispam_IP_Lookup_servers') . '</legend>'.
		'<p>'.__('m_antispam_Add_coma_separated_list_of_servers').'</p>'.
		'<p>'.form::textarea('bls',40,3,html::escapeHTML($bls),'maximal').'</p>'.
		'<p><input type="submit" value="'.__('c_c_action_Save').'" />'.
		$this->okt->formNonce().'</p>'.
		'</fieldset>'.
		'</form>';

		return $res;
	}

	private function getServers()
	{
		return 'sbl-xbl.spamhaus.org , bsb.spamlookup.net';

		$bls = $this->okt->blog->settings->antispam_dnsbls;
		if ($bls === null) {
			$this->okt->blog->settings->setNameSpace('antispam');
			$this->okt->blog->settings->put('antispam_dnsbls',$this->default_bls,'string','Antispam DNSBL servers',true,false);
			return $this->default_bls;
		}

		return $bls;
	}

	private function dnsblLookup($ip,$bl)
	{
		$revIp = implode('.',array_reverse(explode('.',$ip)));

		$host = $revIp.'.'.$bl.'.';
		if (gethostbyname($host) != $host) {
			return true;
		}

		return false;
	}

}
