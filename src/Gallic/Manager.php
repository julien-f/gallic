<?php
/**
 * This file is a part of IoViz.
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
 * @todo Allows “wheres” to be complex expression.
 */
interface Gallic_Manager
{
	/**
	 * @param string $type
	 * @param array  $properties
	 */
	function create($type, array $properties);

	/**
	 * @param string $type
	 * @param array  $where
	 *
	 * @return integer How many records where deleted.
	 */
	function delete($type, array $where);

	/**
	 * @param string       $type
	 * @param array|null   $where
	 * @param integer|null $limit
	 * @param array|null   $order
	 *
	 * @return array[]
	 */
	function search($type, array $where = null,
	                $limit = null, array $order = null);

	/**
	 * @param string $type
	 * @param array  $properties
	 * @param array  $where
	 *
	 * @return integer How many records where updated.
	 */
	function update($type, array $properties, array $where);
}
