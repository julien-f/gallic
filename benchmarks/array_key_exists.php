<?php

require(dirname(__FILE__).'/../src/Gallic.php');

////////////////////////////////////////////////////////////////////////////////

$p = new Gallic_Profiler();

//--------------------------------------

$size  = $loops = 2e2;

$data = new SplFixedArray($size);
$results = new SplFixedArray($size);
for ($i = 0; $i < $size; ++$i)
{
	if (rand(0, 1) === 0)
	{
		$data[$i] = true;
	}
	$results[$i] = true;
}

////////////////////////////////////////////////////////////////////////////////

$p->start('isset($array[$key])');
for ($i = 0; $i < $loops; ++$i)
{
	for ($j = 0; $j < $size; ++$j)
	{
		$results[$j] = isset($data[$j]);
	}
}
unset($j);
$p->stop();

//--------------------------------------

$p->start('array_key_exists($key, $array)');
for ($i = 0; $i < $loops; ++$i)
{
	for ($j = 0; $j < $size; ++$j)
	{
		$results[$j] = array_key_exists($j, $data);
	}
}
unset($j);
$p->stop();

////////////////////////////////////////////////////////////////////////////////

$p->present();
