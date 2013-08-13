<?php

/*

Copyright (c) 2009 Dimas Begunoff, http://www.farinspace.com/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

class googleSpreadsheet
{
	private $client;

	private $spreadsheet;
	private $spreadsheet_id;

	private $worksheet = "Sheet1";
	private $worksheet_id;

	public function __construct($user,$pass,$ss=false,$ws=false)
	{
		$this->login($user,$pass);

		if ($ss) {
			$this->useSpreadsheet($ss);
		}

		if ($ws) {
			$this->useWorksheet($ws);
		}
	}

	public function useSpreadsheet($ss,$ws=false)
	{
		$this->spreadsheet = $ss;
		$this->spreadsheet_id = null;

		if ($ws) {
			$this->useWorksheet($ws);
		}
	}

	public function useWorksheet($ws)
	{
		$this->worksheet = $ws;
		$this->worksheet_id = null;
	}

	public function addRow($row)
	{
		if ($this->client instanceof Zend_Gdata_Spreadsheets)
		{
			$ss_id = $this->getSpreadsheetId($this->spreadsheet);

			if (!$ss_id) {
				throw new Exception('Unable to find spreadsheet by name: "' . $this->spreadsheet . '", confirm the name of the spreadsheet');
			}

			$ws_id = $this->getWorksheetId($ss_id,$this->worksheet);

			if (!$ws_id) {
				throw new Exception('Unable to find worksheet by name: "' . $this->worksheet . '", confirm the name of the worksheet');
			}

			$insert_row = array();

			foreach ($row as $k => $v) {
				$insert_row[$this->cleanKey($k)] = $v;
			}

			$entry = $this->client->insertRow($insert_row,$ss_id,$ws_id);

			if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
				return true;
			}
		}

		throw new Exception('Unable to add row to the spreadsheet');
	}

	// http://code.google.com/apis/spreadsheets/docs/2.0/reference.html#ListParameters
	public function updateRow($row,$search)
	{
		if ($this->client instanceof Zend_Gdata_Spreadsheets AND $search)
		{
			$feed = $this->findRows($search);

			if ($feed->entries)
			{
				foreach ($feed->entries as $entry)
				{
					if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry)
					{
						$update_row = array();

						$customRow = $entry->getCustom();
						foreach ($customRow as $customCol)
						{
							$update_row[$customCol->getColumnName()] = $customCol->getText();
						}

						// overwrite with new values
						foreach ($row as $k => $v)
						{
							$update_row[$this->cleanKey($k)] = $v;
						}

						// update row data, then save
						$entry = $this->client->updateRow($entry,$update_row);
						if (!($entry instanceof Zend_Gdata_Spreadsheets_ListEntry)) {
							return false;
						}
					}
				}

				return true;
			}
		}

		return false;
	}

	// http://code.google.com/apis/spreadsheets/docs/2.0/reference.html#ListParameters
	public function getRows($search=false)
	{
		$rows = array();

		if ($this->client instanceof Zend_Gdata_Spreadsheets)
		{
			$feed = $this->findRows($search);

			if ($feed->entries)
			{
				foreach ($feed->entries as $entry)
				{
					if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry)
					{
						$row = array();

						$customRow = $entry->getCustom();
						foreach ($customRow as $customCol)
						{
							$row[$customCol->getColumnName()] = $customCol->getText();
						}

						$rows[] = $row;
					}
				}
			}
		}

		return $rows;
	}

	// user contribution by dmon (6/10/2009)
	public function deleteRow($search)
	{
		if ($this->client instanceof Zend_Gdata_Spreadsheets AND $search)
		{
			$feed = $this->findRows($search);

			if ($feed->entries)
			{
				foreach ($feed->entries as $entry)
				{
					if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry)
					{
						$this->client->deleteRow($entry);

						if (!($entry instanceof Zend_Gdata_Spreadsheets_ListEntry)) {
							return false;
						}
					}
				}

				return true;
			}
		}

		return false;
	}

	public function getColumnNames()
	{
		$query = new Zend_Gdata_Spreadsheets_ListQuery();
		$query->setSpreadsheetKey($this->getSpreadsheetId());
		$query->setWorksheetId($this->getWorksheetId());
		$query->setMaxResults(1);
		$query->setStartIndex(1);

		$feed = $this->client->getListFeed($query);

		$data = array();

		if ($feed->entries)
		{
			foreach($feed->entries as $entry)
			{
				if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry)
				{
					$customRow = $entry->getCustom();

					foreach ($customRow as $customCol)
					{
						array_push($data,$customCol->getColumnName());
					}
				}
			}
		}

		return $data;
	}

	private function login($user,$pass)
	{
		// Zend Gdata package required
		// http://framework.zend.com/download/gdata

		require_once 'Zend/Loader.php';
		Zend_Loader::loadClass('Zend_Http_Client');
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');

		$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
		$http = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
		$this->client = new Zend_Gdata_Spreadsheets($http);

		if ($this->client instanceof Zend_Gdata_Spreadsheets) {
			return true;
		}

		return false;
	}

	private function findRows($search=false)
	{
		$query = new Zend_Gdata_Spreadsheets_ListQuery();
		$query->setSpreadsheetKey($this->getSpreadsheetId());
		$query->setWorksheetId($this->getWorksheetId());

		if ($search) {
			$query->setSpreadsheetQuery($search);
		}

		$feed = $this->client->getListFeed($query);

		return $feed;
	}

	private function getSpreadsheetId($ss=false)
	{
		if ($this->spreadsheet_id) {
			return $this->spreadsheet_id;
		}

		$ss = $ss ? $ss : $this->spreadsheet;

		$ss_id = false;

		$feed = $this->client->getSpreadsheetFeed();

		foreach ($feed->entries as $entry)
		{
			if ($entry->title->text == $ss)
			{
				$a = explode('/',$entry->id->text);

				$ss_id = array_pop($a);

				$this->spreadsheet_id = $ss_id;

				break;
			}
		}

		return $ss_id;
	}

	private function getWorksheetId($ss_id=false,$ws=false)
	{
		if ($this->worksheet_id) {
			return $this->worksheet_id;
		}

		$ss_id = $ss_id ? $ss_id : $this->spreadsheet_id;

		$ws = $ws ? $ws : $this->worksheet;

		$wk_id = false;

		if ($ss_id AND $ws)
		{
			$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
			$query->setSpreadsheetKey($ss_id);
			$feed = $this->client->getWorksheetFeed($query);

			foreach ($feed->entries as $entry)
			{
				if ($entry->title->text == $ws)
				{
					$a = explode('/',$entry->id->text);

					$wk_id = array_pop($a);

					$this->worksheet_id = $wk_id;

					break;
				}
			}
		}

		return $wk_id;
	}

	protected function cleanKey($k)
	{
		return strtolower(preg_replace('/[^A-Za-z0-9\-\.]+/','',$k));
	}

} # class
