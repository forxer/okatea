<?php
/**
 * @class nestedTree
 * @ingroup okt_classes_tools
 * @brief Une classe pour gérer des arbres imbriqués
 *
 */


class nestedTree
{
	protected $okt;
	protected $error;
	protected $db;
	protected $table;
	protected $fields;
	protected $sortField;

	/**
	 * Constructor. Set the database table name and necessary field names
	 *
	 * @param   string  $table          Name of the tree database table
	 * @param   string  $idField        Name of the primary key ID field
	 * @param   string  $parentField    Name of the parent ID field
	 * @param   string  $sortField      Name of the field to sort data.
	 * @param	array	$addFields		Others fields to be selecteds
	 */
	public function __construct($okt, $table, $idField, $parentField, $sortField, $addFields=array())
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;
		$this->table = $table;

		$this->fields = array_merge($addFields, array('id' => $idField, 'parent' => $parentField));
		$this->sortField = $sortField;
	}

	/**
	 * A utility function to return an array of the fields
	 * that need to be selected in SQL select queries
	 *
	 * @return  array   An indexed array of fields to select
	 */
	protected function getFields($in_array=false)
	{
		$array = array_merge($this->fields,array('nleft','nright','level'));

		if ($in_array) {
			return $array;
		}

		return implode(', ', $array);
	}

	/**
	 * Fetch the node data for the node identified by $id
	 *
	 * @param   int     $id     The ID of the node to fetch
	 * @return  object          An object containing the node's
	 *                          data, or null if node not found
	 */
	public function getNode($id)
	{
		$query = sprintf('select %s from %s where %s = %d',
					$this->getFields(),
					$this->table,
					$this->fields['id'],
					$id);

		if (($rs = $this->db->select($query)) === false) {
			return null;
		}

		if ($rs->isEmpty()) {
			return null;
		}

		return $rs;
	}

	/**
	 * Fetch the descendants of a node, or if no node is specified, fetch the
	 * entire tree. Optionally, only return child data instead of all descendant
	 * data.
	 *
	 * @param   int     $id             The ID of the node to fetch descendant data for.
	 *                                  Specify an invalid ID (e.g. 0) to retrieve all data.
	 * @param   bool    $includeSelf    Whether or not to include the passed node in the
	 *                                  the results. This has no meaning if fetching entire tree.
	 * @param   bool    $childrenOnly   True if only returning children data. False if
	 *                                  returning all descendant data
	 * @return  array                   The descendants of the passed now
	 */
	public function getDescendants($id=0, $includeSelf=false, $childrenOnly=false)
	{
		$node = $this->getNode($id);
		if ($node === null)
		{
			$nleft = 0;
			$nright = 0;
			$parent_id = 0;
		}
		else {
			$nleft = $node->f('nleft');
			$nright = $node->f('nright');
			$parent_id = $node->f($this->fields['id']);
		}

		if ($childrenOnly)
		{
			if ($includeSelf)
			{
				$query = sprintf('select %s from %s where %s = %d or %s = %d order by nleft',
							$this->getFields(),
							$this->table,
							$this->fields['id'],
							$parent_id,
							$this->fields['parent'],
							$parent_id);
			}
			else {
				$query = sprintf('select %s from %s where %s = %d order by nleft',
							$this->getFields(),
							$this->table,
							$this->fields['parent'],
							$parent_id);
			}
		}
		else {
			if ($nleft > 0 && $includeSelf)
			{
				$query = sprintf('select %s from %s where nleft >= %d and nright <= %d order by nleft',
							$this->getFields(),
							$this->table,
							$nleft,
							$nright);
			}
			elseif ($nleft > 0)
			{
				$query = sprintf('select %s from %s where nleft > %d and nright < %d order by nleft',
							$this->getFields(),
							$this->table,
							$nleft,
							$nright);
			}
			else {
				$query = sprintf('select %s from %s order by nleft',
							$this->getFields(),
							$this->table);
			}
		}

		if (($rs = $this->db->select($query)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	/**
	 * Fetch the children of a node, or if no node is specified, fetch the
	 * top level items.
	 *
	 * @param   int     $id             The ID of the node to fetch child data for.
	 * @param   bool    $includeSelf    Whether or not to include the passed node in the
	 *                                  the results.
	 * @return  array                   The children of the passed node
	 */
	public function getChildren($id=0, $includeSelf=false)
	{
		return $this->getDescendants($id, $includeSelf, true);
	}

	/**
	 * Fetch the path to a node. If an invalid node is passed, an empty array is returned.
	 * If a top level node is passed, an array containing on that node is included (if
	 * 'includeSelf' is set to true, otherwise an empty array)
	 *
	 * @param   int     $id             The ID of the node to fetch child data for.
	 * @param   bool    $includeSelf    Whether or not to include the passed node in the
	 *                                  the results.
	 * @return  array                   An array of each node to passed node
	 */
	public function getPath($id=0, $includeSelf=false)
	{
		$node = $this->getNode($id);
		if ($node === null) {
			return false;
		}

		if ($includeSelf)
		{
			$query = sprintf('select %s from %s where nleft <= %d and nright >= %d order by level',
						$this->getFields(),
						$this->table,
						$node->nleft,
						$node->nright);
		}
		else {
			$query = sprintf('select %s from %s where nleft < %d and nright > %d order by level',
						$this->getFields(),
						$this->table,
						$node->nleft,
						$node->nright);
		}

		if (($rs = $this->db->select($query)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	/**
	 * Check if one node descends from another node. If either node is not
	 * found, then false is returned.
	 *
	 * @param   int     $descendant_id  The node that potentially descends
	 * @param   int     $ancestor_id    The node that is potentially descended from
	 * @return  bool                    True if $descendant_id descends from $ancestor_id, false otherwise
	 */
	public function isDescendantOf($descendant_id, $ancestor_id)
	{
		$node = $this->getNode($ancestor_id);
		if ($node === null) {
			return false;
		}

		$query = sprintf('select count(*) as is_descendant from %s where %s = %d and nleft > %d and nright < %d',
					$this->table,
					$this->fields['id'],
					$descendant_id,
					$node->nleft,
					$node->nright);

		if (($rs = $this->db->select($query)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			return false;
		}

		return (boolean)($rs->is_descendant > 0);
	}

	/**
	 * Check if one node is a child of another node. If either node is not
	 * found, then false is returned.
	 *
	 * @param   int     $child_id       The node that is possibly a child
	 * @param   int     $parent_id      The node that is possibly a parent
	 * @return  bool                    True if $child_id is a child of $parent_id, false otherwise
	 */
	public function isChildOf($child_id, $parent_id)
	{
		$query = sprintf('select count(*) as is_child from %s where %s = %d and %s = %d',
					$this->table,
					$this->fields['id'],
					$child_id,
					$this->fields['parent'],
					$parent_id);

		if (($rs = $this->db->select($query)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			return false;
		}

		return (boolean)($rs->is_child > 0);
	}

	/**
	 * Find the number of descendants a node has
	 *
	 * @param   int     $id     The ID of the node to search for. Pass 0 to count all nodes in the tree.
	 * @return  int             The number of descendants the node has, or -1 if the node isn't found.
	 */
	public function numDescendants($id)
	{
		if ($id == 0)
		{
			$query = sprintf('select count(*) as num_descendants from %s', $this->table);

			if (($rs = $this->db->select($query)) === false) {
				return -1;
			}

			if ($rs->isEmpty()) {
				return -1;
			}

			return (integer)$rs->num_descendants;
		}
		else {
			$node = $this->getNode($id);
			if ($node !== null) {
				return (integer)(($node->nright - $node->nleft - 1) / 2);
			}
		}

		return -1;
	}

	/**
	 * Find the number of children a node has
	 *
	 * @param   int     $id     The ID of the node to search for. Pass 0 to count the first level items
	 * @return  int             The number of descendants the node has, or -1 if the node isn't found.
	 */
	public function numChildren($id)
	{
		$query = sprintf('select count(*) as num_children from %s where %s = %d',
					$this->table,
					$this->fields['parent'],
					$id);

		if (($rs = $this->db->select($query)) === false) {
			return -1;
		}

		if ($rs->isEmpty()) {
			return -1;
		}

		return (integer)$rs->num_children;
	}

	/**
	 * Fetch the tree data, nesting within each node references to the node's children
	 *
	 * @return  array       The tree with the node's child data
	 */
	public function getTreeWithChildren()
	{
		$idField = $this->fields['id'];
		$parentField = $this->fields['parent'];

		$query = sprintf('select %s from %s order by %s',
					$this->getFields(),
					$this->table,
					$this->sortField);

		if (($rs = $this->db->select($query)) === false) {
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

			foreach ($fields as $field) {
				$arr[$rs->f($idField)][$field] = $rs->f($field);
			}

			$arr[$rs->f($idField)]['children'] = array();
		}

		// now process the array and build the child data
		foreach ($arr as $id => $row)
		{
			if (isset($row[$parentField])) {
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
			if ($id == 0) {
				continue;
			}

			$query = sprintf('update %s set level = %d, nleft = %d, nright = %d where %s = %d',
						$this->table,
						$row['level'],
						$row['nleft'],
						$row['nright'],
						$this->fields['id'],
						$id);

			$this->db->execute($query);
		}
	}

	/**
	 * Generate the tree data. A single call to this generates the n-values for
	 * 1 node in the tree. This function assigns the passed in n value as the
	 * node's nleft value. It then processes all the node's children (which
	 * in turn recursively processes that node's children and so on), and when
	 * it is finally done, it takes the update n-value and assigns it as its
	 * nright value. Because it is passed as a reference, the subsequent changes
	 * in subrequests are held over to when control is returned so the nright
	 * can be assigned.
	 *
	 * @param   array   &$arr   A reference to the data array, since we need to
	 *                          be able to update the data in it
	 * @param   int     $id     The ID of the current node to process
	 * @param   int     $level  The level to assign to the current node
	 * @param   int     &$n     A reference to the running tally for the n-value
	 */
	private function _generateTreeData(&$arr, $id, $level, &$n)
	{
		$arr[$id]['level'] = $level;
		$arr[$id]['nleft'] = $n++;

		// loop over the node's children and process their data
		// before assigning the nright value
		foreach ($arr[$id]['children'] as $child_id) {
			$this->_generateTreeData($arr, $child_id, $level + 1, $n);
		}
		$arr[$id]['nright'] = $n++;
	}

} # class

