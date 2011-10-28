<?php

/**
 * Base class for all Gallic exceptions.
 */
class Gallic_Exception
{
	public function __construct($message = '', $code = 0, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
