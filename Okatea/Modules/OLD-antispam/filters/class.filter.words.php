<?php
/**
 * @ingroup okt_module_antispam
 * @brief Filtre mots interdits
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Database\Recordset;

class oktFilterWords extends oktSpamFilter
{

	public $has_gui = true;

	public $name = 'Bad Words';

	private $style_list = 'height: 200px; overflow: auto; margin-bottom: 1em; ';

	private $style_p = 'margin: 1px 0 0 0; padding: 0.2em 0.5em; ';

	private $style_global = 'background: #ccff99; ';

	private $con;

	private $table;

	public function __construct($okt)
	{
		parent::__construct($okt);
		$this->db = $okt->db;
		$this->table = $okt->db->prefix . 'mod_spamrule';
	}

	protected function setInfo()
	{
		$this->description = __('m_antispam_Words_Blacklist');
	}

	public function getStatusMessage($status)
	{
		return sprintf(__('m_antispam_Filtered_by_%1$s_with_word_%2$s'), $this->guiLink(), '<em>' . $status . '</em>');
	}

	public function isSpam($type, $author, $email, $site, $ip, $content, &$status)
	{
		$str = $author . ' ' . $email . ' ' . $site . ' ' . $content;
		
		$rs = $this->getRules();
		
		while ($rs->fetch())
		{
			$word = $rs->rule_content;
			
			if (substr($word, 0, 1) == '/' && substr($word, - 1, 1) == '/')
			{
				$reg = substr(substr($word, 1), 0, - 1);
			}
			else
			{
				$reg = preg_quote($word, '/');
				$reg = '(^|\s+|>|<)' . $reg . '(>|<|\s+|\.|$)';
			}
			
			if (preg_match('/' . $reg . '/msiu', $str))
			{
				$status = $word;
				return true;
			}
		}
		
		return false;
	}

	public function gui($url)
	{
		# Create list
		if (! empty($_POST['createlist']))
		{
			try
			{
				$this->defaultWordsList();
				
				$okt['flashMessages']->success(__('m_antispam_Words_successfully_added'));
				
				http::redirect($url);
			}
			catch (\Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}
		
		# Adding a word
		if (! empty($_POST['swa']))
		{
			try
			{
				$this->addRule($_POST['swa']);
				
				$okt['flashMessages']->success(__('m_antispam_Word_successfully_added'));
				
				http::redirect($url);
			}
			catch (\Exception $e)
			{
				$okt->error->add($e->getMessage());
			}
		}
		
		# Removing spamwords
		if (! empty($_POST['swd']) && is_array($_POST['swd']))
		{
			try
			{
				$this->removeRule($_POST['swd']);
				
				$okt['flashMessages']->success(__('m_antispam_Words_successfully_removed'));
				
				http::redirect($url);
			}
			catch (\Exception $e)
			{
				$okt->error->add($e->getMessage());
			}
		}
		
		/* DISPLAY
		---------------------------------------------- */
		
		global $okt;
		
		$res = '';
		
		$res .= '<form action="' . html::escapeURL($url) . '" method="post">' . '<fieldset><legend>' . __('m_antispam_Add_word') . '</legend>' . '<p>' . form::text('swa', 20, 128) . ' ';
		
		$res .= Page::formtoken() . '<input type="submit" value="' . __('c_c_action_Add') . '"/></p>' . '</fieldset>' . '</form>';
		
		$rs = $this->getRules();
		if ($rs->isEmpty())
		{
			$res .= '<p><strong>' . __('m_antispam_No_word_in_list') . '</strong></p>';
		}
		else
		{
			$res .= '<form action="' . html::escapeURL($url) . '" method="post">' . '<fieldset><legend>' . __('m_antispam_List') . '</legend>' . '<div style="' . $this->style_list . '">';
			
			while ($rs->fetch())
			{
				$disabled_word = false;
				$p_style = $this->style_p;
				
				$res .= '<p style="' . $p_style . '"><label class="classic">' . form::checkbox(array(
					'swd[]'
				), $rs->rule_id, false) . ' ' . html::escapeHTML($rs->rule_content) . '</label></p>';
			}
			
			$res .= '</div>' . '<p>' . form::hidden(array(
				'spamwords'
			), 1) . Page::formtoken() . '<input type="submit" value="' . __('m_antispam_Delete_selected_words') . '"/></p>' . '</fieldset></form>';
		}
		
		$res .= '<form action="' . html::escapeURL($url) . '" method="post">' . '<p><input type="submit" value="' . __('m_antispam_Create_default_wordlist') . '" />' . form::hidden(array(
			'spamwords'
		), 1) . form::hidden(array(
			'createlist'
		), 1) . Page::formtoken() . '</p>' . '</form>';
		
		return $res;
	}

	private function getRules()
	{
		$strReq = 'SELECT rule_id, rule_content ' . 'FROM ' . $this->table . ' ' . "WHERE rule_type = 'word' " . 'ORDER BY rule_content ASC ';
		
		if (($rs = $this->db->select($strReq)) === false)
		{
			return new Recordset(array());
		}
		
		return $rs;
	}

	private function addRule($content)
	{
		$strReq = 'SELECT rule_id FROM ' . $this->table . ' ' . "WHERE rule_type = 'word' " . "AND rule_content = '" . $this->db->escapeStr($content) . "' ";
		
		if (($rs = $this->db->select($strReq)) === false)
		{
			$rs = new Recordset(array());
		}
		
		if (! $rs->isEmpty())
		{
			throw new Exception(__('m_antispam_This_word_exists'));
		}
		
		$rs = $this->db->select('SELECT MAX(rule_id) FROM ' . $this->table);
		$id = (integer) $rs->f(0) + 1;
		
		$cur = $this->db->openCursor($this->table);
		$cur->rule_id = $id;
		$cur->rule_type = 'word';
		$cur->rule_content = (string) $content;
		
		$cur->insert();
	}

	private function removeRule($ids)
	{
		$strReq = 'DELETE FROM ' . $this->table . ' ';
		
		if (is_array($ids))
		{
			foreach ($ids as &$v)
			{
				$v = (integer) $v;
			}
			$strReq .= 'WHERE rule_id IN (' . implode(',', $ids) . ') ';
		}
		else
		{
			$ids = (integer) $ids;
			$strReq .= 'WHERE rule_id = ' . $ids . ' ';
		}
		
		$this->db->execute($strReq);
	}

	public function defaultWordsList()
	{
		$words = array(
			'/-credit(\s+|$)/',
			'/-digest(\s+|$)/',
			'/-loan(\s+|$)/',
			'/-online(\s+|$)/',
			'4u',
			'adipex',
			'advicer',
			'ambien',
			'baccarat',
			'baccarrat',
			'blackjack',
			'bllogspot',
			'bolobomb',
			'booker',
			'byob',
			'car-rental-e-site',
			'car-rentals-e-site',
			'carisoprodol',
			'cash',
			'casino',
			'casinos',
			'chatroom',
			'cialis',
			'craps',
			'credit-card',
			'credit-report-4u',
			'cwas',
			'cyclen',
			'cyclobenzaprine',
			'dating-e-site',
			'day-trading',
			'debt',
			'digest-',
			'discount',
			'discreetordering',
			'duty-free',
			'dutyfree',
			'estate',
			'favourits',
			'fioricet',
			'flowers-leading-site',
			'freenet',
			'freenet-shopping',
			'gambling',
			'gamias',
			'health-insurancedeals-4u',
			'holdem',
			'holdempoker',
			'holdemsoftware',
			'holdemtexasturbowilson',
			'hotel-dealse-site',
			'hotele-site',
			'hotelse-site',
			'incest',
			'insurance-quotesdeals-4u',
			'insurancedeals-4u',
			'jrcreations',
			'levitra',
			'macinstruct',
			'mortgage',
			'online-gambling',
			'onlinegambling-4u',
			'ottawavalleyag',
			'ownsthis',
			'palm-texas-holdem-game',
			'paxil',
			'pharmacy',
			'phentermine',
			'pills',
			'poker',
			'poker-chip',
			'poze',
			'prescription',
			'rarehomes',
			'refund',
			'rental-car-e-site',
			'roulette',
			'shemale',
			'slot',
			'slot-machine',
			'soma',
			'taboo',
			'tamiflu',
			'texas-holdem',
			'thorcarlson',
			'top-e-site',
			'top-site',
			'tramadol',
			'trim-spa',
			'ultram',
			'v1h',
			'vacuum',
			'valeofglamorganconservatives',
			'viagra',
			'vicodin',
			'vioxx',
			'xanax',
			'zolus'
		);
		
		foreach ($words as $w)
		{
			try
			{
				$this->addRule($w, true);
			}
			catch (\Exception $e)
			{
			}
		}
	}
}
