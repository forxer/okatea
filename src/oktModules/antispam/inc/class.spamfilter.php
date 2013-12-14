<?php
/**
 * @ingroup okt_module_antispam
 * @brief Définit un filtre antispam
 *
 */

class oktSpamFilter
{
	public $name;
	public $description;
	public $active = true;
	public $order = 100;
	public $auto_delete = false;

	protected $has_gui = false;
	protected $gui_url = null;

	protected $okt;

	/**
	 * Object constructor
	 *
	 * @param $okt
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->setInfo();

		if (!$this->name) {
			$this->name = get_class($this);
		}

		$this->gui_url = 'module.php?m=antispam&f='.get_class($this);
	}

	/**
	This method is called by the constructor and allows you to change some
	object properties without overloading object constructor.
	*/
	protected function setInfo()
	{
		$this->description = __('m_antispam_No_description');
	}

	/**
	This method should return if a comment is a spam or not. If it returns true
	or false, execution of next filters will be stoped. If should return nothing
	to let next filters apply.

	Your filter should also fill $status variable with its own information if
	comment is a spam.

	@param		type		<b>string</b>		Comment type (comment or trackback)
	@param		author	<b>string</b>		Comment author
	@param		email	<b>string</b>		Comment author email
	@param		site		<b>string</b>		Comment author website
	@param		ip		<b>string</b>		Comment author IP address
	@param		content	<b>string</b>		Comment content
	@param[out]	status	<b>integer</b>		Comment status
	@return	<b>boolean</b>
	*/
	public function isSpam($type,$author,$email,$site,$ip,$content,&$status)
	{
	}

	/**
	This method is called when a non-spam (ham) comment becomes spam or when a
	spam becomes a ham.

	@param	type		<b>string</b>		Comment type (comment or trackback)
	@param	filter	<b>string</b>		Filter name
	@param	author	<b>string</b>		Comment author
	@param	email	<b>string</b>		Comment author email
	@param	site		<b>string</b>		Comment author website
	@param	ip		<b>string</b>		Comment author IP address
	@param	content	<b>string</b>		Comment content
	@param	post_url	<b>string</b>		Post URL
	@param	rs		<b>record</b>		Comment record
	@return	<b>boolean</b>
	*/
	public function trainFilter($status,$filter,$type,$author,$email,$site,$ip,$content,$rs)
	{
	}

	/**
	This method returns filter status message. You can overload this method to
	return a custom message. Message is shown in comment details and in
	comments list.

	@param	status		<b>string</b>		Filter status.
	@param	comment_id	<b>record</b>		Comment record
	@return	<b>string</b>
	*/
	public function getStatusMessage($status)
	{
		return sprintf(__('m_antispam_Filtered_by_%1$s_(%2$s)'),$this->guiLink(),$status);
	}

	/**
	This method is called when you enter filter configuration. Your class should
	have $has_gui property set to "true" to enable GUI.

	In this method you should put everything related to filter configuration.
	$url variable is the URL of GUI <i>unescaped</i>.

	@param	url		<b>string</b>		GUI URL.
	*/
	public function gui($url)
	{
	}

	public function hasGUI()
	{
		if (!$this->okt->user->is_admin || !$this->okt->checkPerm('antispam')) {
			return false;
		}

		if (!$this->has_gui) {
			return false;
		}

		return true;
	}

	public function guiURL()
	{
		if (!$this->hasGui()) {
			return false;
		}

		return $this->gui_url;
	}

	/**
	Returns a link to filter GUI if exists or only filter name if has_gui
	property is false.

	@return	<b>string</b>
	*/
	public function guiLink()
	{
		if (($url = $this->guiURL()) !== false) {
			$url = html::escapeHTML($url);
			$link = '<a href="%2$s">%1$s</a>';
		} else {
			$link = '%1$s';
		}

		return sprintf($link,$this->name,$url);
	}

}
