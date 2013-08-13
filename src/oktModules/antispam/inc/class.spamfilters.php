<?php
/**
 * @ingroup okt_module_antispam
 * @brief Définit une collection de filtres antispam
 *
 */

class oktSpamFilters
{
	private $filters = array();
	private $filters_opt = array();
	private $okt;

	public function __construct($okt)
	{
		$this->okt = $okt;
	}

	public function init($filters)
	{
		foreach ($filters as $f)
		{
			if (!class_exists($f)) {
				continue;
			}

			$r = new ReflectionClass($f);
			$p = $r->getParentClass();

			if (!$p || $p->name != 'oktSpamFilter') {
				continue;
			}

			$this->filters[$f] = new $f($this->okt);
		}

		$this->setFilterOpts();
		if (!empty($this->filters_opt)) {
			uasort($this->filters,array($this,'orderCallBack'));
		}
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function isSpam($type,$author,$email,$site,$ip,$content)
	{
		foreach ($this->filters as $fid=>$f)
		{
			if (!$f->active) {
				continue;
			}

			$is_spam = $f->isSpam($type,$author,$email,$site,$ip,$content,$status);

			if ($is_spam === true) {
				return array('spam_status'=>$status,'spam_filter'=>$fid,'auto_delete'=>$f->auto_delete);
			}
			elseif ($is_spam === false) {
				return false;
			}
		}

		return false;
	}


	public function trainFilters(&$rs,$status,$filter_name)
	{
		foreach ($this->filters as $fid => $f)
		{
			if (!$f->active) {
				continue;
			}

			$type = $rs->type;
			$author = $rs->author;
			$email = $rs->email;
			$site = $rs->site;
			$ip = $rs->ip;
			$content = $rs->content;

			$f->trainFilter($status,$filter_name,$type,$author,$email,$site,$ip,$content,$rs);
		}
	}

	public function statusMessage(&$rs,$filter_name)
	{
		$f = isset($this->filters[$filter_name]) ? $this->filters[$filter_name] : null;

		if ($f === null) {
			return __('m_antispam_Unknown_filter');
		}
		else {
			$status = $rs->exists('spam_status') ? $rs->spam_status : null;

			return $f->getStatusMessage($status);
		}
	}

	public function saveFilterOpts($opts)
	{
		$this->okt->config->write(array('antispam_filters'=>serialize($opts)));
	}

	private function setFilterOpts()
	{
		if (isset($this->okt->config->antispam_filters)) {
			$this->filters_opt = @unserialize($this->okt->config->antispam_filters);
		}

		# Create default options if needed
		if (!is_array($this->filters_opt)) {
			$this->saveFilterOpts(array());
			$this->filters_opt = array();
		}

		foreach ($this->filters_opt as $k => $o)
		{
			if (isset($this->filters[$k]) && is_array($o))
			{
				$this->filters[$k]->active = isset($o[0]) ? $o[0] : false;
				$this->filters[$k]->order = isset($o[1]) ? $o[1] : 0;
				$this->filters[$k]->auto_delete = isset($o[2]) ? $o[2] : false;
			}
		}
	}

	private function orderCallBack($a,$b)
	{
		if ($a->order == $b->order) {
			return 0;
		}

		return $a->order > $b->order ? 1 : -1;
	}

} # class
