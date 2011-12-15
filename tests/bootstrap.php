<?php
/**
 * @author Julien Fontanet <julien.fontanet@isonoe.net>
 * @package HidalgoTest
 */

require(dirname(__FILE__).'/../src/Gallic.php');

Gallic::$paths[] = dirname(__FILE__).'/src';

////////////////////////////////////////////////////////////////////////////////

abstract class GallicTest_Base extends PHPUnit_Framework_TestCase
{}
