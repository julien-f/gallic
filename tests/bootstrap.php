<?php
/**
 * @author Julien Fontanet <julien.fontanet@isonoe.net>
 * @package GallicTest
 */

require(dirname(__FILE__).'/../src/Gallic.php');

////////////////////////////////////////////////////////////////////////////////

spl_autoload_register(array(
	new Gallic_ClassLoader_PrefixFilter(
		new Gallic_ClassLoader_Standard(array(dirname(__FILE__).'/src')),
		array('Gallic')
	),
	'load'
));

////////////////////////////////////////////////////////////////////////////////

abstract class GallicTest_Base extends PHPUnit_Framework_TestCase
{
	static function setUpBeforeClass()
	{
		// Enables error reporting by default.
		error_reporting(-1);
	}
}
