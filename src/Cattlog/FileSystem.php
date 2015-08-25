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

	/**
	 * Get the data from a file into an array
	 * @return array Data from file, or empty array (! file_exists)
	 */
	public function getFileData($file) // getData
	{
		return ($this->fileExists($file)) ? include($file) : array();
	}

	/**
	 * Write the data to file
	 * @param string $file File to write e.g. "/path/to/lang/en/messages.php"
	 * @param array $data Data to write
	 * @return void
	 */
	public function writeDataToFile($file, $data) // putData
	{
		// ensure all folders exist
		$dirPath = explode('/', $file);
		array_pop($dirPath);
		$dirPath = implode('/', $dirPath);
		if (! is_dir($dirPath)) mkdir($dirPath, 0777, TRUE);

		$data = '<'.'?php' . PHP_EOL .
        PHP_EOL .
        'return ' . var_export($data, true) . ';';

		// will create a file if none exist
		file_put_contents($file, $data);
	}


















	use ConfigTrait; // probably don't need this, only Cattlog will use it

	/**
	 * @var array $srcFiles Cache of source files, retrieved on each execution
	 */
	protected $srcFiles;

	public function __construct($config=array())
	{
		// set defaults
		$config = array_merge(array(
			'src' => array('application/views'),
			'dest' => 'resources/lang/{lang}',
			'project_dir' => getcwd(), // useful for testing
		), $config);

		// trim project_dir trailing right slash
		$config['project_dir'] = rtrim($config['project_dir'], '/');

		// set the full path of dest
		if (isset($config['dest'])) {
			$config['dest'] = $config['project_dir'] . '/' . $config['dest'];
		}

		// set the full path of src. also, set as an array even
		// for single item
		if (isset($config['src'])) {
			if (!is_array($config['src'])) $config['src'] = array($config['src']);

			foreach ($config['src'] as $i => $src) {
				$config['src'][$i] = $config['project_dir'] . '/' . $src;
			}
		}

		$this->config = $config;
	}













	// cattlog class

	/**
	 * Get specifically source files
	 * @return array Files
	 */
	public function getSrcFiles()
	{
		// src files are cached to be less calls on the fs
		if (!$this->srcFiles) {
			$this->srcFiles = $this->getFiles($this->config['src']);
		}

		return $this->srcFiles;
	}

	/**
	 * Get the config array (may be altered from array that was given in instantiation)
	 * In Zend, we're only gonna support a single file for now
	 * @return array Config
	 */
	public function getDestFile($lang)
	{
		// set dest path by $lang e.g. /path/to/dest/{en}/
		return str_replace("{lang}", $lang, $this->config['dest']);
	}























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
