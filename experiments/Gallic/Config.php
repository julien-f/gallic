<?php
/**
 * This file is a part of Gallic.
 *
 * Gallic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Gallic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gallic. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Julien Fontanet <julien.fontanet@isonoe.net>
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GPLv3
 *
 * @package Gallic
 */

class Gallic_Config implements ArrayAccess, Countable, IteratorAggregate
{
	function __construct(array $entries, $read_only = false)
	{
		$this->setEntries($entries);
		$this->setReadOnly($read_only);
	}

	function __get($name)
	{
		if (!isset($this->_entries[$name]))
		{
			throw new Gallic_Exception('No such entry: '.$name);
		}

		return $this->_entries[$name];
	}

	function __isset($name)
	{
		return array_key_exists($name, $this->_entries);
	}

	function __set($name, $value)
	{
		if ($this->isReadOnly())
		{
			throw new Gallic_Exception(__CLASS__.' is read only');
		}

		if (is_array($value))
		{
			$value = new Gallic_Config($value, $this->isReadOnly());
		}

		$this->_entries[$name] = $value;
	}

	function __unset($name)
	{
		if ($this->isReadOnly())
		{
			throw new Gallic_Exception(__CLASS__.' is read only');
		}

		unset($this->_entries[$name]);
	}

	function get($name, $default = null)
	{
		if (isset($this->_entries[$name]))
		{
			return $this->_entries[$name];
		}

		return $default;
	}

	function isReadOnly()
	{
		return $this->_read_only;
	}

	function setEntries(array $entries)
	{
		foreach ($entries as $name => $value)
		{
			$this->__set($name, $value);
		}
	}

	function setReadOnly($read_only)
	{
		$this->_read_only = $read_only;
	}

	function toArray()
	{
		$result = array();

		foreach ($this->_entries as $entry)
		{
			if ($entry instanceof self)
			{
				$entry = $entry->toArray();
			}

			$result[] = $entry;
		}

		return $result;
	}

	////////////////////////////////////////
	// Countable

	function count()
	{
		return count($this->_entries);
	}

	////////////////////////////////////////
	// IteratorAggregate

	function getIterator()
	{
		return new ArrayIterator($this->_entries);
	}

	////////////////////////////////////////
	// ArrayAccess

	function offsetExists($name)
	{
		return $this->__isset($name);
	}

	function offsetGet($name)
	{
		return $this->__get($name);
	}

	function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}

	function offsetUnset($name)
	{
		$this->__unset($name);
	}

	private
		$_entries,
		$_read_only;
}
