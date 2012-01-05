<?php

require(dirname(__FILE__).'/../src/Gallic.php');

////////////////////////////////////////////////////////////////////////////////

$p = new Gallic_Profiler();

//--------------------------------------

$size  = 1e3;
$loops = 1e4;

$data = new SplFixedArray($size);
$results = new SplFixedArray($size);
for ($i = 0; $i < $size; ++$i)
{
	$data[$i] = (rand(0, 1) === 0 ? null : true);
	$results[$i] = true;
}

////////////////////////////////////////////////////////////////////////////////

$p->start('isset');
for ($i = 0; $i < $loops; ++$i)
{
	foreach ($data as $j => $entry)
	{
		$results[$j] = !isset($entry);
	}
}
$p->stop();

//--------------------------------------

$p->start('=== null');
for ($i = 0; $i < $loops; ++$i)
{
	foreach ($data as $j => $entry)
	{
		$results[$j] = ($entry === null);
	}
}
$p->stop();

//--------------------------------------

$p->start('is_null');
for ($i = 0; $i < $loops; ++$i)
{
	foreach ($data as $j => $entry)
	{
		$results[$j] = is_null($entry);
	}
}
$p->stop();

////////////////////////////////////////////////////////////////////////////////

$p->present();