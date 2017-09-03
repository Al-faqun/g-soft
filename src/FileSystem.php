<?php
namespace gsoft;

class FileSystem
{
	/**
	 * Appends provided files and folders into valid path using separators.
	 * @param array $folders array of strings, WITHOUT delimiters '/', '\',
	 * except first folder in linux system must be prepended with '/'
	 * @return string valid path without ending slash.
	 */
	static function append(array $folders)
	{
		$firstFolder = rtrim($folders[0], "\t\n\r\0\x0B\\\/");
		$path = $firstFolder . DIRECTORY_SEPARATOR;
		for ( $i = 1; $i < count($folders); $i++ ) {
			$nextFolder = trim($folders[$i], "\t\n\r\0\x0B\\\/");
			$path.= $nextFolder . DIRECTORY_SEPARATOR;
		}
		$path = rtrim($path, "\t\n\r\0\x0B\\\/");
		return $path;
	}
}