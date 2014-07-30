<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Misc;

use Okatea\Tao\Application;
use Okatea\Tao\Database\Recordset;

/**
 * A class to handle internationalized nested trees.
 */
class NestedTreei18n
{
	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
	 */
	protected $okt;

	protected $sTable;

	protected $sTableAlias;

	protected $sTableLocales;

	protected $sTableLocalesAlias;

	protected $aFields;

	protected $aLocalesFields;

	protected $sSortField;

	protected $sJoinField;

	/**
	 * Constructor.
	 * Set the database table name and necessary field names
	 *
	 * @param object $okt of core application
	 * @param string $sTable Name of the tree database table
	 * @param string $sTableLocales Name of the locales database table
	 * @param string $idField Name of the primary key ID field
	 * @param string $parentField Name of the parent ID field
	 * @param string $sSortField Name of the field to sort data
	 * @param string $sJoinField Name of the join field
	 * @param string $sLanguageField Name of the language field
	 * @param array $addFields fields to be selecteds
	 * @param array $addLocalesFields Others localized fields
	 * @return void
	 */
	public function __construct(Application $okt, $sTable, $sTableLocales, $idField, $parentField, $sSortField, $sJoinField = 'category_id', $sLanguageField = 'language', array $addFields = [], array $addLocalesFields = [])
	{
		$this->okt = $okt;

		$this->sTable = $sTable;
		$this->sTableAlias = 't';

		$this->sTableLocales = $sTableLocales;
		$this->sTableLocalesAlias = 'l';

		$this->aFields = array_merge($addFields, [
			'id' 		=> $idField,
			'parent' 	=> $parentField
		]);
		$this->aLocalesFields = $addLocalesFields;

		$this->sSortField = $sSortField;
		$this->sJoinField = $sJoinField;
		$this->sLanguageField = $sLanguageField;
	}

	/**
	 * Fetch the node data for the node identified by $id
	 *
	 * @param int $id The ID of the node to fetch
	 * @return array An array containing the node's data, or null if node not found
	 */
	public function getNode($id)
	{
		$node = $this->okt['db']->fetchAssoc(
			'SELECT ' . $this->getFields() . ' ' .
			'FROM ' . $this->getFrom() . ' ' .
			'WHERE ' . $this->prependTableAlias($this->aFields['id']) . ' = ?',
			array($id)
		);

		if (empty($node)) {
			return null;
		}

		return $node;
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
			$nleft = $node['nleft'];
			$nright = $node['nright'];
			$parent_id = $node[$this->aFields['id']];
		}

		$queryBuilder = $this->okt['db']->createQueryBuilder();
		$queryBuilder
			->select($this->getFieldsList(true))
			->from($this->sTable, $this->sTableAlias)
			->innerJoin(
				$this->sTableAlias,
				$this->sTableLocales,
				$this->sTableLocalesAlias,
				$this->prependTableAlias('id') . ' = ' . $this->prependLocalesAlias($this->sJoinField)
			)
			->orderBy($this->prependTableAlias('nleft'))
		;

		if ($bChildrenOnly)
		{
			if ($bIncludeSelf)
			{
				$queryBuilder
					->where($this->aFields['id'] . ' = :parent_id')
					->orWhere($this->aFields['parent'] . ' = :parent_id')
				;
			}
			else
			{
				$queryBuilder
					->where($this->aFields['parent'] . ' = :parent_id')
				;
			}
		}
		else
		{
			if ($nleft > 0 && $bIncludeSelf)
			{
				$queryBuilder
					->where('nleft >= :nleft')
					->andWhere('nright <= :nright')
				;
			}
			elseif ($nleft > 0)
			{
				$queryBuilder
					->where('nleft > :nleft')
					->andWhere('nright < :nright')
				;
			}
		}

		if (! is_null($sLanguageCode))
		{
			$queryBuilder
				->andWhere($this->prependLocalesAlias($this->sLanguageField) . ' = :language');
		}

		$queryBuilder
			->setParameter('parent_id', $parent_id)
			->setParameter('nleft', $nleft)
			->setParameter('nright', $nright)
			->setParameter('language', $sLanguageCode)
		;

		return $queryBuilder->execute()->fetchAll();
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

		if ($node === null) {
			return false;
		}

		$queryBuilder = $this->okt['db']->createQueryBuilder();
		$queryBuilder
			->select($this->getFieldsList(true))
			->from($this->sTable, $this->sTableAlias)
			->innerJoin(
				$this->sTableAlias,
				$this->sTableLocales,
				$this->sTableLocalesAlias,
				$this->prependTableAlias('id') . ' = ' . $this->prependLocalesAlias($this->sJoinField)
			)
			->orderBy($this->prependTableAlias('level'));

		if ($bIncludeSelf)
		{
			$queryBuilder
				->where('nleft <= :nleft')
				->andWhere('nright >= :nright');
		}
		else
		{
			$queryBuilder
				->where('nleft < :nleft')
				->andWhere('nright > :nright');
		}

		if (! is_null($sLanguageCode))
		{
			$queryBuilder
				->andWhere($this->prependLocalesAlias($this->sLanguageField) . ' = :language');
		}

		$queryBuilder
			->setParameter('nleft', $node['nleft'])
			->setParameter('nright', $node['nright'])
			->setParameter('language', $sLanguageCode)
		;

		return $queryBuilder->execute()->fetchAll();
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

		if ($node === null) {
			return false;
		}

		$queryBuilder = $this->okt['db']->createQueryBuilder();
		$queryBuilder
			->select('count('.$this->aFields['id'].') AS is_descendant')
			->from($this->sTable, $this->sTableAlias)
			->where($this->aFields['id'] . ' = :descendant_id')
			->andWhere('nleft > :nleft')
			->andWhere('nright < :nright')

			->setParameter('descendant_id', $descendant_id)
			->setParameter('nleft', $node['nleft'])
			->setParameter('nright', $node['nright'])
		;

		return (boolean) ($queryBuilder->execute()->fetchColumn() > 0);
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
		$queryBuilder = $this->okt['db']->createQueryBuilder();
		$queryBuilder
			->select('count('.$this->aFields['id'].') AS is_child')
			->from($this->sTable, $this->sTableAlias)
			->where($this->aFields['id'] . ' = :child_id')
			->andWhere($this->aFields['parent'] . ' = :parent_id')

			->setParameter('child_id', $child_id)
			->setParameter('parent_id', $parent_id)
		;

		return (boolean) ($queryBuilder->execute()->fetchColumn() > 0);
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
			$queryBuilder = $this->okt['db']->createQueryBuilder();
			$queryBuilder
				->select('count('.$this->aFields['id'].') AS is_child')
				->from($this->sTable, $this->sTableAlias)
			;

			return (integer) $queryBuilder->execute()->fetchColumn();
		}
		else
		{
			$node = $this->getNode($id);

			if (null !== $node) {
				return (integer) (($node['nright'] - $node['nleft'] - 1) / 2);
			}
		}

		return - 1;
	}

	/**
	 * Find the number of children a node has
	 *
	 * @param int $id
	 *        	The ID of the node to search for. Pass 0 to count the first level items
	 * @return int The number of descendants the node has.
	 */
	public function numChildren($id)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();
		$queryBuilder
			->select('count('.$this->aFields['id'].') AS num_children')
			->from($this->sTable, $this->sTableAlias)
			->where($this->aFields['parent'] . ' = :parent_id')
			->setParameter('parent_id', $id)
		;

		return (integer) $queryBuilder->execute()->fetchColumn();
	}

	/**
	 * Fetch the tree data, nesting within each node references to the node's children
	 *
	 * @return array The tree with the node's child data
	 */
	public function getTreeWithChildren()
	{
		$leaves = $this->okt['db']->fetchAll('SELECT ' . $this->getFields() . ' FROM ' . $this->getFrom() . ' ORDER BY ' . $this->sSortField);

		// create a root node to hold child data about first level items
		$arr = [];
		$arr[0] = [
			$this->aFields['id'] => 0,
			'children' => []
		];

		// populate the array and create an empty children array
		$fields = $this->getFieldsList();

		foreach ($leaves as $leaf)
		{
			$arr[$leaf[$this->aFields['id']]] = [];

			foreach ($fields as $field)
			{
				$arr[$leaf[$this->aFields['id']]][$field] = $leaf[$field];
			}

			$arr[$leaf[$this->aFields['id']]]['children'] = [];
		}

		// now process the array and build the child data
		foreach ($arr as $id => $row)
		{
			if (isset($row[$this->aFields['parent']])) {
				$arr[$row[$this->aFields['parent']]]['children'][$id] = $id;
			}
		}

		return $arr;
	}

	/**
	 * Fetch the tree data into a multidimensional array
	 *
	 * @return array The tree with the node's child data
	 */
	public function getStructuredTree()
	{
		$leaves = $this->okt['db']->fetchAll('SELECT ' . $this->getFields() . ' FROM ' . $this->getFrom() . ' ORDER BY ' . $this->sSortField);

		$data = array();

		# populate the array
		$fields = $this->getFieldsList();

		foreach ($leaves as $leaf)
		{
			$data[$leaf[$this->aFields['id']]] = array();

			foreach ($fields as $field) {
				$data[$leaf[$this->aFields['id']]][$field] = $leaf[$field];
			}
		}

		# build the structured tree
		$_tree = array();
		foreach ($data as &$value)
		{
			if ($parent = $value['parent_id']) {
				$data[$parent]['children'][] = &$value;
			}
			else {
				$_tree[] = &$value;
			}
		}
		unset($value);
		$data = $_tree;
		unset($_tree);

		return $data;
	}

	/**
	 * Rebuilds the tree data and saves it to the database
	 */
	public function rebuild()
	{
		$data = $this->getTreeWithChildren();

		$n = 0; // need a variable to hold the running n tally
		//	$level = 0; // need a variable to hold the running level tally


		// invoke the recursive function. Start it processing
		// on the fake "root node" generated in getTreeWithChildren().
		// because this node doesn't really exist in the database, we
		// give it an initial nleft value of 0 and an level of 0.
		$this->generateTreeData($data, 0, 0, $n);

		// at this point the root node will have nleft of 0, level of 0
		// and nright of (tree size * 2 + 1)
		foreach ($data as $id => $row)
		{
			// skip the root node
			if ($id == 0) {
				continue;
			}

			$this->okt['db']->update(
				$this->sTable,
				array(
					'level' => $row['level'],
					'nleft' => $row['nleft'],
					'nright' => $row['nright']
				),
				array($this->aFields['id'] => $id)
			);
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
	protected function generateTreeData(&$arr, $id, $level, &$n)
	{
		$arr[$id]['level'] = $level;
		$arr[$id]['nleft'] = $n++;

		// loop over the node's children and process their data
		// before assigning the nright value
		foreach ($arr[$id]['children'] as $child_id)
		{
			$this->generateTreeData($arr, $child_id, $level + 1, $n);
		}

		$arr[$id]['nright'] = $n++;
	}

	/**
	 * A utility function to return an array of the fields
	 * that need to be selected in SQL select queries.
	 *
	 * @param boolean $bPrependAliases Prepend aliases to field names.
	 * @return array
	 */
	protected function getFieldsList($bPrependAliases = false)
	{
		$fields = array_merge($this->aFields, [
			'nleft',
			'nright',
			'level'
		]);

		$localesFields = $this->aLocalesFields;

		if ($bPrependAliases)
		{
			$fields = array_map([$this, 'prependTableAlias'], $fields);

			$localesFields = array_map([$this, 'prependLocalesAlias'], $localesFields);
		}

		return array_merge($fields, $localesFields);
	}

	/**
	 * A utility function to return a string of the fields
	 * that need to be selected in SQL select queries.
	 *
	 * @param boolean $bPrependAliases Prepend aliases to field names.
	 * @return string
	 */
	protected function getFields($bPrependAliases = true)
	{
		return implode(', ', $this->getFieldsList($bPrependAliases));
	}

	protected function prependTableAlias($v)
	{
		return $this->sTableAlias . '.' . $v;
	}

	protected function prependLocalesAlias($v)
	{
		return $this->sTableLocalesAlias . '.' . $v;
	}

	protected function getFrom()
	{
		return $this->sTable . ' AS ' . $this->sTableAlias . ' ' .
			'JOIN ' . $this->sTableLocales . ' AS ' . $this->sTableLocalesAlias . ' ' .
			'ON ' . $this->prependTableAlias('id') . ' = ' . $this->prependLocalesAlias($this->sJoinField);
	}
}
