<?php

class Gallic_Config implements ArrayAccess, Countable, IteratorAggregate
{
	public function __construct(array $entries, $read_only = false)
	{
		$this->setEntries($entries);
		$this->setReadOnly($read_only);
	}

	public function __get($name)
	{
		if (!isset($this->_entries[$name]))
		{
			throw new Gallic_Exception('No such entry: '.$name);
		}

		return $this->_entries[$name];
	}

	public function __isset($name)
	{
		return array_key_exists($name, $this->_entries);
	}

	public function __set($name, $value)
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

	public function __unset($name)
	{
		if ($this->isReadOnly())
		{
			throw new Gallic_Exception(__CLASS__.' is read only');
		}

		unset($this->_entries[$name]);
	}

	public function get($name, $default = null)
	{
		if (isset($this->_entries[$name]))
		{
			return $this->_entries[$name];
		}

		return $default;
	}

	public function isReadOnly()
	{
		return $this->_read_only;
	}

	public function setEntries(array $entries)
	{
		foreach ($entries as $name => $value)
		{
			$this->__set($name, $value);
		}
	}

	public function setReadOnly($read_only)
	{
		$this->_read_only = $read_only;
	}

	public function toArray()
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

	public function count()
	{
		return count($this->_entries);
	}

	////////////////////////////////////////
	// IteratorAggregate

	public function getIterator()
	{
		return new ArrayIterator($this->_entries);
	}

	////////////////////////////////////////
	// ArrayAccess

	public function offsetExists($name)
	{
		return $this->__isset($name);
	}

	public function offsetGet($name)
	{
		return $this->__get($name);
	}

	public function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}

	public function offsetUnset($name)
	{
		$this->__unset($name);
	}

	private
		$_entries,
		$_read_only;
}
