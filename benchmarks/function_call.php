<?php

require(dirname(__FILE__).'/../src/Gallic.php');

////////////////////////////////////////////////////////////////////////////////

$p = new Gallic_Profiler();

//--------------------------------------

$size  = $loops = 1e3;

$data = range(0, $size - 1);
function f($data)
{
	return $data;
}
$f = 'f';

////////////////////////////////////////////////////////////////////////////////

$p->start('$f($data)');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	foreach ($data as $j)
	{
		$sum += $f($j);
	}
}
unset($sum, $i, $j);
$p->stop();

//--------------------------------------

$p->start('call_user_func($f, $data)');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	foreach ($data as $j)
	{
		$sum += call_user_func($f, $j);
	}
}
unset($sum, $i, $j);
$p->stop();

//--------------------------------------

// Next function requires arrays.
foreach ($data as &$entry)
{
	$entry = array($entry);
}
unset($entry);

$p->start('call_user_func_array($f, $data)');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	foreach ($data as $j)
	{
		$sum += call_user_func_array($f, $j);
	}
}
unset($sum, $i, $j);
$p->stop();

////////////////////////////////////////////////////////////////////////////////

$p->present();
