<?php

class Gallic_String
{
	public static function has_prefix($string, $prefix)
	{
		$prefix_l = strlen($prefix);

		if ($prefix_l > strlen($string))
		{
			return false;
		}

		return (strncmp($string, $prefix, $prefix_l) === 0);
	}

	public static function has_suffix($string, $suffix)
	{
		$suffix_l = strlen($suffix);

		if ($suffix_l > strlen($string))
		{
			return false;
		}

		return (substr_compare($string, $suffix, -$suffix_l) === 0);
	}
}
