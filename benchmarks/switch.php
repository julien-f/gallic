<?php

require(dirname(__FILE__).'/../src/Gallic.php');

////////////////////////////////////////////////////////////////////////////////

$p = new Gallic_Profiler();

//--------------------------------------

$size  = $loops = 1e3;

$data = new SplFixedArray($size);
for ($i = 0; $i < $size; ++$i)
{
	$data[$i] = rand(0, 4);
}
unset($i);

////////////////////////////////////////////////////////////////////////////////

$p->start('switch');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	foreach ($data as $j)
	{
		switch ($j)
		{
		case 0:
			$sum += 1;
			break;
		case 1:
		case 2:
			$sum -= 1;
			break;
		case 3:
			$sum += 2;
			break;
		default:
			$sum -= 1;

		}
	}
}
unset($sum, $i, $j);
$p->stop();

//--------------------------------------

$p->start('if, elseif, else');
for ($i = 0; $i < $loops; ++$i)
{
	$sum = 0;
	foreach ($data as $j)
	{
		if ($j === 0)
		{
			$sum += 1;
		}
		elseif (($j === 1) || ($j === 2))
		{
			$sum -= 1;
		}
		elseif ($j === 3)
		{
			$sum += 2;
		}
		else
		{
			$sum -= 1;
		}
	}
}
unset($sum, $i, $j);
$p->stop();

////////////////////////////////////////////////////////////////////////////////

$p->present();
