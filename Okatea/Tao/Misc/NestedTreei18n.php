<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Misc;

use Okatea\Tao\Database\Recordset;

/**
 * Une classe internationalisées pour gérer des arbres imbriqués.
 */
class NestedTreei18n
{

	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 *
	 * @var object
	 */
	protected $db;

	/**
	 * The errors manager instance.
	 *
	 * @var object
	 */
	protected $error;

	protected $sTable;

	protected $tablePrefix;

	protected $sTableLocales;

	protected $sTableLocalesPrefix;

	protected $aFields;

	protected $aLocalesFields;

	protected $sSortField;

	protected $sJoinField;

	/**
	 * Constructor.
	 * Set the database table name and necessary field names
	 *
	 * @param object $okt
	 *        	of core application
	 * @param string $sTable
	 *        	Name of the tree database table
	 * @param string $sTableLocales
	 *        	Name of the locales database table
	 * @param string $idField
	 *        	Name of the primary key ID field
	 * @param string $parentField
	 *        	Name of the parent ID field
	 * @param string $sSortField
	 *        	Name of the field to sort data
	 * @param string $sJoinField
	 *        	Name of the join field
	 * @param string $sLanguageField
	 *        	Name of the language field
	 * @param array $addFields
	 *        	fields to be selecteds
	 * @param array $addLocalesFields
	 *        	Others localized fields
	 */
	public function __construct($okt, $sTable, $sTableLocales, $idField, $parentField, $sSortField, $sJoinField = 'category_id', $sLanguageField = 'language', $addFields = array(), $addLocalesFields = array())
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;
		
		$this->sTable = $sTable;
		$this->sTablePrefix = 't';
		
		$this->sTableLocales = $sTableLocales;
		$this->sTableLocalesPrefix = 'l';
		
		$this->aFields = array_merge($addFields, array(
			'id' => $idField,
			'parent' => $parentField
		));
		$this->aLocalesFields = $addLocalesFields;
		
		$this->sSortField = $sSortField;
		$this->sJoinField = $sJoinField;
		$this->sLanguageField = $sLanguageField;
	}

	/**
	 * A utility function to return an array of the fields
	 * that need to be selected in SQL select queries
	 *
	 * @return array An indexed array of fields to select
	 */
	protected function getFields($in_array = false)
	{
		$fields = array_merge($this->aFields, array(
			'nleft',
			'nright',
			'level'
		));
		
		if ($in_array)
		{
			return array_merge($fields, $this->aLocalesFields);
		}
		
		$fields = array_map(array(
			$this,
			'prependTAlias'
		), $fields);
		$localesFields = array_map(array(
			$this,
			'prependLAlias'
		), $this->aLocalesFields);
		
		return implode(', ', array_merge($fields, $localesFields));
	}

	protected function prependTAlias($v)
	{
		return $this->sTablePrefix . '.' . $v;
	}

	protected function prependLAlias($v)
	{
		return $this->sTableLocalesPrefix . '.' . $v;
	}

	protected function getFrom()
	{
		return $this->sTable . ' AS ' . $this->sTablePrefix . ' ' . 'JOIN ' . $this->sTableLocales . ' AS ' . $this->sTableLocalesPrefix . ' ' . 'ON ' . $this->sTablePrefix . '.id = ' . $this->sTableLocalesPrefix . '.' . $this->sJoinField;
	}

	/**
	 * Fetch the node data for the node identified by $id
	 *
	 * @param int $id
	 *        	The ID of the node to fetch
	 * @return object An object containing the node's
	 *         data, or null if node not found
	 */
	public function getNode($id)
	{
		$query = sprintf('SELECT %s FROM %s WHERE %s = %d', $this->getFields(), $this->getFrom(), $this->sTablePrefix . '.' . $this->aFields['id'], $id);
		
		if (($rs = $this->db->select($query)) === false)
		{
			return null;
		}
		
		if ($rs->isEmpty())
		{
			return null;
		}
		
		return $rs;
	}

	/**
	 * Fetch the descendants of a node, or if no node is specified, fetch the
	 * entire tree.
	 * Optionally, only return child data instead of all descendant
	 * data.
	 *
	 * @param int $id
	 *        	The ID of the node to fetch descendant data for.
	 *        	Specify an invalid ID (e.g. 0) to retrieve all data.
	 * @param bool $bIncludeSelf
	 *        	Whether or not to include the passed node in the
	 *        	the results. This has no meaning if fetching entire tree.
	 * @param bool $bChildrenOnly
	 *        	True if only returning children data. False if
	 *        	returning all descendant data
	 * @param string $sLanguageCode
	 *        	The ISO code to restrict results.
	 * @return recordset The descendants of the passed now
	 */
	public function getDescendants($id = 0, $bIncludeSelf = false, $bChildrenOnly = false, $sLanguageCode = null)
	{
		$node = $this->getNode($id);
		
		if ($node === null)
		{
			$nleft = 0;
			$nright = 0;
			$parent_id = 0;
		}
		else
		{
			$nleft = $node->f('nleft');
			$nright = $node->f('nright');
			$parent_id = $node->f($this->aFields['id']);
		}
		
		$sLanguageWhere = '';
		if (! is_null($sLanguageCode))
		{
			$sLanguageWhere = 'AND ' . $this->prependLAlias($this->sLanguageField) . '=\'' . $sLanguageCode . '\' ';
		}
		
		if ($bChildrenOnly)
		{
			if ($bIncludeSelf)
			{
				$query = sprintf('SELECT %s FROM %s WHERE (%s = %d OR %s = %d) ' . $sLanguageWhere . 'ORDER BY nleft', $this->getFields(), $this->getFrom(), $this->aFields['id'], $parent_id, $this->aFields['parent'], $parent_id);
			}
			else
			{
				$query = sprintf('SELECT %s FROM %s WHERE %s = %d ' . $sLanguageWhere . 'ORDER BY nleft', $this->getFields(), $this->getFrom(), $this->aFields['parent'], $parent_id);
			}
		}
		else
		{
			if ($nleft > 0 && $bIncludeSelf)
			{
				$query = sprintf('SELECT %s FROM %s WHERE nleft >= %d AND nright <= %d ' . $sLanguageWhere . 'ORDER BY nleft', $this->getFields(), $this->getFrom(), $nleft, $nright);
			}
			elseif ($nleft > 0)
			{
				$query = sprintf('SELECT %s FROM %s WHERE nleft > %d AND nright < %d ' . $sLanguageWhere . 'ORDER BY nleft', $this->getFields(), $this->getFrom(), $nleft, $nright);
			}
			else
			{
				$query = sprintf('SELECT %s FROM %s ' . $sLanguageWhere . 'ORDER BY nleft', $this->getFields(), $this->getFrom());
			}
		}
		
		if (($rs = $this->db->select($query)) === false)
		{
			return new Recordset(array());
		}
		
		return $rs;
	}

	/**
	 * Fetch the children of a node, or if no node is specified, fetch the
	 * top level items.
	 *
	 * @param int $id
	 *        	The ID of the node to fetch child data for.
	 * @param bool $bIncludeSelf
	 *        	Whether or not to include the passed node in the
	 *        	the results.
	 * @param string $sLanguageCode
	 *        	The ISO code to restrict results.
	 * @return recordset The children of the passed node
	 */
	public function getChildren($id = 0, $bIncludeSelf = false, $sLanguageCode = null)
	{
		return $this->getDescendants($id, $bIncludeSelf, true, $sLanguageCode);
	}

	/**
	 * Fetch the path to a node.
	 * If an invalid node is passed, an empty array is returned.
	 * If a top level node is passed, an array containing on that node is included (if
	 * 'includeSelf' is set to true, otherwise an empty array)
	 *
	 * @param int $id
	 *        	The ID of the node to fetch child data for.
	 * @param bool $bIncludeSelf
	 *        	Whether or not to include the passed node in the
	 *        	the results.
	 * @param string $sLanguageCode
	 *        	The ISO code to restrict results.
	 * @return recordset An recordset of each node to passed node
	 */
	public function getPath($id = 0, $bIncludeSelf = false, $sLanguageCode = null)
	{
		$node = $this->getNode($id);
		
		if ($node === null)
		{
			return false;
		}
		
		if ($bIncludeSelf)
		{
			$sWhere = 'nleft <= %d AND nright >= %d';
		}
		else
		{
			$sWhere = 'nleft < %d AND nright > %d';
		}
		
		if (! is_null($sLanguageCode))
		{
			$sWhere .= ' AND ' . $this->prependLAlias($this->sLanguageField) . '=\'' . $sLanguageCode . '\'';
		}
		
		$query = sprintf('SELECT %s FROM %s WHERE ' . $sWhere . ' ORDER BY level', $this->getFields(), $this->getFrom(), $node->nleft, $node->nright);
		
		if (($rs = $this->db->select($query)) === false)
		{
			return new Recordset(array());
		}
		
		return $rs;
	}

	/**
	 * Check if one node descends WHERE another node.
	 * If either node is not
	 * found, then false is returned.
	 *
	 * @param int $descendant_id
	 *        	The node that potentially descends
	 * @param int $ancestor_id
	 *        	The node that is potentially descended from
	 * @return bool True if $descendant_id descends from $ancestor_id, false otherwise
	 */
	public function isDescendantOf($descendant_id, $ancestor_id)
	{
		$node = $this->getNode($ancestor_id);
		
		if ($node === null)
		{
			return false;
		}
		
		$query = sprintf('SELECT count(*) AS is_descendant FROM %s WHERE %s = %d AND nleft > %d AND nright < %d', $this->getFrom(), $this->aFields['id'], $descendant_id, $node->nleft, $node->nright);
		
		if (($rs = $this->db->select($query)) === false)
		{
			return false;
		}
		
		if ($rs->isEmpty())
		{
			return false;
		}
		
		return (boolean) ($rs->is_descendant > 0);
	}

	/**
	 * Check if one node is a child of another node.
	 * If either node is not
	 * found, then false is returned.
	 *
	 * @param int $child_id
	 *        	The node that is possibly a child
	 * @param int $parent_id
	 *        	The node that is possibly a parent
	 * @return bool True if $child_id is a child of $parent_id, false otherwise
	 */
	public function isChildOf($child_id, $parent_id)
	{
		$query = sprintf('SELECT count(*) AS is_child FROM %s WHERE %s = %d AND %s = %d', $this->getFrom(), $this->aFields['id'], $child_id, $this->aFields['parent'], $parent_id);
		
		if (($rs = $this->db->select($query)) === false)
		{
			return false;
		}
		
		if ($rs->isEmpty())
		{
			return false;
		}
		
		return (boolean) ($rs->is_child > 0);
	}

	/**
	 * Find the number of descendants a node has
	 *
	 * @param int $id
	 *        	The ID of the node to search for. Pass 0 to count all nodes in the tree.
	 * @return int The number of descendants the node has, or -1 if the node isn't found.
	 */
	public function numDescendants($id)
	{
		if ($id == 0)
		{
			$query = sprintf('SELECT COUNT(%s) AS num_descendants FROM %s', $this->aFields['id'], $this->sTable);
			
			if (($rs = $this->db->select($query)) === false)
			{
				return - 1;
			}
			
			if ($rs->isEmpty())
			{
				return - 1;
			}
			
			return (integer) $rs->num_descendants;
		}
		else
		{
			$node = $this->getNode($id);
			
			if ($node !== null)
			{
				return (integer) (($node->nright - $node->nleft - 1) / 2);
			}
		}
		
		return - 1;
	}

	/**
	 * Find the number of children a node has
	 *
	 * @param int $id
	 *        	The ID of the node to search for. Pass 0 to count the first level items
	 * @return int The number of descendants the node has, or -1 if the node isn't found.
	 */
	public function numChildren($id)
	{
		$query = sprintf('SELECT COUNT(%s) AS num_children FROM %s WHERE %s = %d', $this->aFields['id'], $this->sTable, $this->aFields['parent'], $id);
		
		if (($rs = $this->db->select($query)) === false)
		{
			return - 1;
		}
		
		if ($rs->isEmpty())
		{
			return - 1;
		}
		
		return (integer) $rs->num_children;
	}

	/**
	 * Fetch the tree data, nesting within each node references to the node's children
	 *
	 * @return array The tree with the node's child data
	 */
	public function getTreeWithChildren()
	{
		$idField = $this->aFields['id'];
		$parentField = $this->aFields['parent'];
		
		$query = sprintf('SELECT %s FROM %s ORDER BY %s', $this->getFields(), $this->getFrom(), $this->sSortField);
		
		if (($rs = $this->db->select($query)) === false)
		{
			return array();
		}
		
		// create a root node to hold child data about first level items
		$arr = array();
		$arr[0] = array(
			$idField => 0,
			'children' => array()
		);
		
		// populate the array and create an empty children array
		$fields = $this->getFields(true);
		
		while ($rs->fetch())
		{
			$arr[$rs->f($idField)] = array();
			
			foreach ($fields as $field)
			{
				$arr[$rs->f($idField)][$field] = $rs->f($field);
			}
			
			$arr[$rs->f($idField)]['children'] = array();
		}
		
		// now process the array and build the child data
		foreach ($arr as $id => $row)
		{
			if (isset($row[$parentField]))
			{
				$arr[$row[$parentField]]['children'][$id] = $id;
			}
		}
		
		return $arr;
	}

	/**
	 * Rebuilds the tree data and saves it to the database
	 */
	public function rebuild()
	{
		$data = $this->getTreeWithChildren();
		
		$n = 0; // need a variable to hold the running n tally
		$level = 0; // need a variable to hold the running level tally
		

		// invoke the recursive function. Start it processing
		// on the fake "root node" generated in getTreeWithChildren().
		// because this node doesn't really exist in the database, we
		// give it an initial nleft value of 0 and an level of 0.
		$this->_generateTreeData($data, 0, 0, $n);
		
		// at this point the the root node will have nleft of 0, level of 0
		// and nright of (tree size * 2 + 1)
		foreach ($data as $id => $row)
		{
			// skip the root node
			if ($id == 0)
			{
				continue;
			}
			
			$query = sprintf('UPDATE %s SET level = %d, nleft = %d, nright = %d WHERE %s = %d', $this->getFrom(), $row['level'], $row['nleft'], $row['nright'], $this->aFields['id'], $id);
			
			$this->db->execute($query);
		}
	}

	/**
	 * Generate the tree data.
	 * A single call to this generates the n-values for
	 * 1 node in the tree. This function assigns the passed in n value as the
	 * node's nleft value. It then processes all the node's children (which
	 * in turn recursively processes that node's children and so on), and when
	 * it is finally done, it takes the update n-value and assigns it as its
	 * nright value. Because it is passed as a reference, the subsequent changes
	 * in subrequests are held over to when control is returned so the nright
	 * can be assigned.
	 *
	 * @param
	 *        	array &$arr A reference to the data array, since we need to
	 *        	be able to update the data in it
	 * @param int $id
	 *        	The ID of the current node to process
	 * @param int $level
	 *        	The level to assign to the current node
	 * @param
	 *        	int &$n A reference to the running tally for the n-value
	 */
	private function _generateTreeData(&$arr, $id, $level, &$n)
	{
		$arr[$id]['level'] = $level;
		$arr[$id]['nleft'] = $n ++;
		
		// loop over the node's children and process their data
		// before assigning the nright value
		foreach ($arr[$id]['children'] as $child_id)
		{
			$this->_generateTreeData($arr, $child_id, $level + 1, $n);
		}
		$arr[$id]['nright'] = $n ++;
	}
}
