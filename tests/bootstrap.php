<?php
/**
 * @author Julien Fontanet <julien.fontanet@isonoe.net>
 * @package GallicTest
 */

require(dirname(__FILE__).'/../src/Gallic.php');

Gallic::$paths[] = dirname(__FILE__).'/src';

////////////////////////////////////////////////////////////////////////////////

abstract class GallicTest_Base extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		// Enables error reporting by default.
		error_reporting(-1);
	}
}
