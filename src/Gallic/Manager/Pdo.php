<?php
/**
 * This file is part of IoViz.
 *
 * IoViz is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * IoViz is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with IoViz. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Julien Fontanet <julien.fontanet@vates.fr>
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GPLv3
 *
 * @package IoViz
 */

/**
 * @experimental
 */
final class Gallic_Manager_Pdo implements Gallic_Manager
{
	/**
	 *
	 */
	function __construct(PDO $pdo)
	{
		$pdo->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION
		);

		$this->_pdo = $pdo;
	}

	function create($type, array $properties)
	{
		$type   = $this->_quoteIdentifier($type);
		$fields = implode(
			', ',
			$this->_quoteIdentifier(array_keys($properties))
		);
		$values = implode(
			', ',
			$this->_quoteValue($properties)
		);

		$n = $this->_pdo->exec(
			"INSERT INTO $type ($fields) VALUES ($values)"
		);

		assert('$n === 1');
	}

	function delete($type, array $where)
	{
		return $this->_pdo->exec(
			"DELETE FROM $type".$this->_where($where)
		);
	}

	function search($type, array $where = null,
	                $limit = null, array $order = null)
	{
		$type = $this->_quoteIdentifier($type);

		$select =
			"SELECT * FROM $type"
			.$this->_where($where)
			.$this->_limit($limit)
			.$this->_order($order)
			;

		$stmt = $this->_pdo->prepare($select);
		$success = $stmt->execute();
		assert('$success');

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function update($type, array $properties, array $where)
	{
		$type = $this->_quoteIdentifier($type);

		$update = "UPDATE $type SET";
		foreach ($properties as $field => $value)
		{
			$field = $this->_quoteIdentifier($field);
			$value = $this->_quoteValue($value);

			$update .= " $field = $value";
		}
		$update .= $this->_where($where);

		return $this->_pdo->exec($update);
	}


	/**
	 *
	 */
	private function _limit($limit)
	{
		if ($limit === null)
		{
			return '';
		}

		return ' LIMIT '.((int) $limit);
	}


	/**
	 *
	 */
	private function _order($order)
	{
		if (empty($order))
		{
			return '';
		}

		return ' ORDER BY '.implode(
			', ',
			$this->_quoteIdentifier((array) $order)
		);
	}

	/**
	 * Quotes identifier(s).
	 *
	 * @param array|string $id
	 *
	 * @return array|string
	 */
	private function _quoteIdentifier($id)
	{
		if (is_array($id))
		{
			return array_map(
				array($this, __FUNCTION__),
				$id
			);
		}

		return '"'.str_replace('"', '', $id).'"';
	}

	/**
	 * Quotes value(s).
	 *
	 * @param array|string $value
	 *
	 * @return array|string
	 */
	private function _quoteValue($value)
	{
		if (is_array($value))
		{
			return array_map(
				array($this, __FUNCTION__),
				$value
			);
		}

		if ($value === null)
		{
			return 'NULL';
		}

		if ($value instanceof DateTime)
		{
			$value = $value->format('c');
		}

		return $this->_pdo->quote($value);
	}

	/**
	 *
	 */
	private function _where(array $criteria = null)
	{
		if (empty($criteria))
		{
			return '';
		}

		$wheres = array();
		foreach ($criteria as $field => $value)
		{
			$field = $this->_quoteIdentifier($field);
			$value = $this->_quoteValue($value);

			$wheres[] = "$field = $value";
		}
		return ' WHERE '.implode(' AND ', $wheres);
	}
}
