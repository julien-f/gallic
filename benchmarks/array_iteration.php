<?php

require(dirname(__FILE__).'/../src/Gallic.php');

////////////////////////////////////////////////////////////////////////////////

$p = new Gallic_Profiler();

//--------------------------------------

$size  = $loops = 1e3;

$data = range(0, $size - 1);

////////////////////////////////////////////////////////////////////////////////

$p->start('foreach ($array as $i)');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	foreach ($data as $j)
	{
		$sum += $j;
	}
}
unset($sum, $i, $j);
$p->stop();

//--------------------------------------

$p->start('for($i = 0; $i < $n; ++$i)');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	for ($j = 0; $j < $size; ++$j)
	{
		$sum += $data[$j];
	}
}
unset($sum, $i, $j);
$p->stop();

//--------------------------------------

$p->start('for($i = 0; $i < count($array); ++$i)');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	for ($j = 0; $j < count($data); ++$j)
	{
		$sum += $data[$j];
	}
}
unset($sum, $i, $j);
$p->stop();

//--------------------------------------

$p->start('while (list(, $i) = each($array))');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	reset($data);
	while (list(, $j) = each($data))
	{
		$sum += $j;
	}
}
unset($sum, $i, $j);
$p->stop();

////////////////////////////////////////////////////////////////////////////////

$p->present();
