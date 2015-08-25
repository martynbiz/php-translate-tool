<?php namespace Cattlog;

//https://github.com/lijinma/php-cli-color

/*

New class structure:

$apapter = new Json(); // getFileData, putFileData

// this class is only here to allow us to mock file_exists etc
$fileSystem = new FileSystem();

// cattlog
$cattlog = new Cattlog($adapter, $fileSystem, $config);

*/

class FileSystem
{

	// these commands are dependant on the format used, so should belong in
	// the adapter class


















	// use ConfigTrait; // probably don't need this, only Cattlog will use it

	// public function __construct($config=array())
	// {
	//
	//
	// 	$this->config = $config;
	// }













	// cattlog class























	// file system for sure

	/**
	 * Get the content as a string
	 * @return string File contents
	 */
	public function fileExists($file)
	{
		return file_exists($file);
	}

	/**
	 * Get the content as a string
	 * @return string File contents
	 */
	public function getFileContents($file)
	{
		return file_get_contents($file);
	}

	/**
	 * Recursive scan to get files within a dir
	 * @param string|array $dirs Directories to scan
	 * @param string $prefix Attach a prefix to each file (optional)
	 * @return array Files
	 */
	public function getFiles($dirs)
	{
		// ensure that $dirs is an array of directories for the locale_lookup
		// if set only as string (e.g. "/path/to/src")
		if (! is_array($dirs))
			$dirs = array($dirs);

		// remove trailing slash from config dir
		$files = array();
		foreach ($dirs as $dir) {
			if (is_dir($dir)) {
				$dir = rtrim($dir, '\\/');
				$files = array_merge($files, $this->_getFilesRecursive($dir));
			} elseif (is_file($dir)) {
				array_push($files, $dir); // $dir is a file
			}
		}

		return $files;
	}

	/**
	 * Recursive scan to get files within a dir
	 * @param string $dir Directory to scan
	 * @param string $prefix Attach a prefix to each file (optional)
	 * @return array Files
	 */
	protected function _getFilesRecursive($dir)
	{
		$files = array();
		foreach (scandir($dir) as $f) {
			if ($f !== '.' and $f !== '..') {
				if (is_dir("$dir/$f")) {
					$files = array_merge($files, $this->_getFilesRecursive("$dir/$f"));
				} else {
					$files[] = $dir.'/'.$f;
				}
			}
		}

		return $files;
	}
}
