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

		return ($prefix === substr($string, 0, $prefix_l));
	}

	public static function has_suffix($string, $suffix)
	{
		$string_l = strlen($string);
		$suffix_l = strlen($suffix);

		if ($suffix_l > $string_l)
		{
			return false;
		}

		return ($suffix === substr($string, $string_l - $suffix_l));
	}
}
