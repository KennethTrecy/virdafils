<?php

namespace KennethTrecy\Virdafils\Util;

class PathHelper {
	const SEPARATOR = "/";
	const ABSOLUTE_ROOT = "/";

	/**
	 * Joins directory names into one path (unresolved).
	 *
	 * Note: It will have root at the front of the string regardless whethere the root already exist
	 * or not in the parameter. Empty directory names will be filtered out.
	 *
	 * @param array $names
	 * @return string
	 */
	public static function join(array $names) {
		$filtered_names = array_filter($names, function($name) {
			return $name !== "" && $name !== static::SEPARATOR;
		});
		return static::SEPARATOR.implode(static::SEPARATOR, $filtered_names);
	}

	/**
	 * Split directory into individual names but does not resolve it.
	 *
	 * @param string $directory_name
	 * @return string
	 */
	public static function split(array $path) {
		return explode(static::SEPARATOR, $path);
	}
}
