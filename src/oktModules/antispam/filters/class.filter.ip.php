<?php
/**
 * @ingroup okt_module_antispam
 * @brief  Filtre IP
 *
 */

use Okatea\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

class oktFilterIP extends oktSpamFilter
{
	public $has_gui = true;
	public $name = 'IP Filter';

	private $style_list = 'height: 200px; overflow: auto; margin-bottom: 1em; ';
	private $style_p = 'margin: 1px 0 0 0; padding: 0.2em 0.5em; ';
	private $style_global = 'background: #ccff99; ';

	private $db;
	private $table;

	public function __construct($okt)
	{
		parent::__construct($okt);
		$this->db = $okt->db;
		$this->table = $okt->db->prefix.'mod_spamrule';
	}

	protected function setInfo()
	{
		$this->description = __('m_antispam_IP_Blacklist_Whitelist_Filter');
	}

	public function getStatusMessage($status)
	{
		return sprintf(__('m_antispam_Filtered_by_%1$s_with_rule_%2$s'),$this->guiLink(),$status);
	}

	public function isSpam($type,$author,$email,$site,$ip,$content,&$status)
	{
		if (!$ip) {
			return;
		}

		# White list check
		if ($this->checkIP($ip,'white') !== false) {
			return false;
		}

		# Black list check
		if (($s = $this->checkIP($ip,'black')) !== false) {
			$status = $s;
			return true;
		}
	}

	public function gui($url)
	{
		# Set current type and tab
		$ip_type = 'black';
		if (!empty($_REQUEST['ip_type']) && $_REQUEST['ip_type'] == 'white') {
			$ip_type = 'white';
		}

		# Add IP to list
		if (!empty($_POST['addip']))
		{
			try
			{
				$this->addIP($ip_type,$_POST['addip']);

				$okt->page->flash->success(__('m_antispam_IP_successfully_added'));

				http::redirect($url.'&ip_type='.$ip_type);
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}

		# Remove IP from list
		if (!empty($_POST['delip']) && is_array($_POST['delip']))
		{
			try {
				$this->removeRule($_POST['delip']);

				$okt->page->flash->success(__('m_antispam_IP_successfully_removed'));

				http::redirect($url.'&ip_type='.$ip_type);
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}

		/* DISPLAY
		---------------------------------------------- */

		$res = '';

		$res .=
		$this->displayForms($url,'black',__('m_antispam_Blacklist')).
		$this->displayForms($url,'white',__('m_antispam_Whitelist'));

		return $res;
	}

	private function displayForms($url,$type,$title)
	{
		$res =
		'<h3>'.$title.'</h3>'.

		'<form action="'.html::escapeURL($url).'" method="post">'.
		'<fieldset><legend>'.__('m_antispam_Add_IP_address').'</legend><p>'.
		form::hidden(array('ip_type'),$type).
		form::text(array('addip'),18,255).' ';

		$res .=
		Page::formtoken().
		'<input type="submit" value="'.__('c_c_action_Add').'"/></p>'.
		'</fieldset></form>';

		$rs = $this->getRules($type);

		if ($rs->isEmpty())
		{
			$res .= '<p><strong>'.__('m_antispam_No_IP_address_in_list').'</strong></p>';
		}
		else
		{
			$res .=
			'<form action="'.html::escapeURL($url).'" method="post">'.
			'<fieldset><legend>' . __('m_antispam_IP_list') . '</legend>'.
			'<div style="'.$this->style_list.'">';

			while ($rs->fetch())
			{
				$bits = explode(':',$rs->rule_content);
				$pattern = $bits[0];
				$ip = $bits[1];
				$bitmask = $bits[2];

				$p_style = $this->style_p;

				$res .=
				'<p style="'.$p_style.'"><label class="classic">'.
				form::checkbox(array('delip[]'),$rs->rule_id,false).' '.
				html::escapeHTML($pattern).
				'</label></p>';
			}
			$res .=
			'</div>'.
			'<p><input type="submit" value="'.__('c_c_action_Delete').'"/>'.
			Page::formtoken().
			form::hidden(array('ip_type'),$type).
			'</p>'.
			'</fieldset></form>';
		}

		return $res;
	}

	private function ipmask($pattern,&$ip,&$mask)
	{
		$bits = explode('/',$pattern);

		# Set IP
		$bits[0] .= str_repeat(".0", 3 - substr_count($bits[0], "."));
		$ip = ip2long($bits[0]);

		if (!$ip || $ip == -1) {
			throw new Exception('Invalid IP address');
		}

		# Set mask
		if (!isset($bits[1])) {
			$mask = -1;
		}
		elseif (strpos($bits[1],'.'))
		{
			$mask = ip2long($bits[1]);
			if (!$mask) {
				$mask = -1;
			}
		}
		else {
			$mask = ~((1 << (32 - $bits[1])) - 1);
		}
	}

	private function addIP($type,$pattern,$global)
	{
		$this->ipmask($pattern,$ip,$mask);
		$pattern = long2ip($ip).($mask != -1 ? '/'.long2ip($mask) : '');
		$content = $pattern.':'.$ip.':'.$mask;

		$old = $this->getRuleCIDR($type,$global,$ip,$mask);
		$cur = $this->db->openCursor($this->table);

		if ($old->isEmpty())
		{
			$id = $this->db->select('SELECT MAX(rule_id) FROM '.$this->table)->f(0) + 1;

			$cur->rule_id = $id;
			$cur->rule_type = (string) $type;
			$cur->rule_content = (string) $content;

			$cur->insert();
		}
		else {
			$cur->rule_type = (string) $type;
			$cur->rule_content = (string) $content;
			$cur->update('WHERE rule_id = '.(integer) $old->rule_id);
		}
	}

	private function getRules($type='all')
	{
		$strReq =
		'SELECT rule_id, rule_type, rule_content '.
		'FROM '.$this->table.' '.
		"WHERE rule_type = '".$this->db->escapeStr($type)."' ".
		'ORDER BY rule_content ASC ';

		if (($rs = $this->db->select($strReq)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	private function getRuleCIDR($type,$global,$ip,$mask)
	{
		$strReq =
		'SELECT * FROM '.$this->table.' '.
		"WHERE rule_type = '".$this->db->escapeStr($type)."' ".
		"AND rule_content LIKE '%:".(integer) $ip.":".(integer) $mask."' ";

		if (($rs = $this->db->select($strReq)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	private function checkIP($cip,$type)
	{
		$strReq =
		'SELECT DISTINCT(rule_content) '.
		'FROM '.$this->table.' '.
		"WHERE rule_type = '".$this->db->escapeStr($type)."' ".
		'ORDER BY rule_content ASC ';

		$rs = $this->db->select($strReq);
		while ($rs->fetch())
		{
			list($pattern,$ip,$mask) = explode(':',$rs->rule_content);
			if ((ip2long($cip) & (integer) $mask) == ((integer) $ip & (integer) $mask)) {
				return $pattern;
			}
		}
		return false;
	}

	private function removeRule($ids)
	{
		$strReq = 'DELETE FROM '.$this->table.' ';

		if (is_array($ids))
		{
			foreach ($ids as $i => $v) {
				$ids[$i] = (integer) $v;
			}
			$strReq .= 'WHERE rule_id IN ('.implode(',',$ids).') ';
		}
		else {
			$ids = (integer) $ids;
			$strReq .= 'WHERE rule_id = '.$ids.' ';
		}

		$this->db->execute($strReq);
	}

}
