<?php namespace Cattlog;

//https://github.com/lijinma/php-cli-color

class FileSystem
{
	use ConfigTrait;

	/**
	 * @var array $srcFiles Cache of source files, retrieved on each execution
	 */
	protected $srcFiles;

	public function __construct($config=array())
	{
		// set defaults
		$config = array_merge(array(
			'src' => array("resources/views"),
			'dest' => 'resources/lang/{lang}',
			'project_dir' => getcwd(), // useful for testing
		), $config);

		// trim project_dir trailing right slash
		$config['project_dir'] = rtrim($config['project_dir'], '/');

		// set the full path of dest
		if (isset($config['dest'])) {
			if (!is_array($config['dest'])) $config['dest'] = array($config['dest']);

			foreach ($config['dest'] as $i => $dest) {
				$config['dest'][$i] = $config['project_dir'] . '/' . $dest;
			}
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
	 * Get the data from a file into an array
	 * @return array Data from file, or empty array (! file_exists)
	 */
	public function getFileData($file)
	{
		return (file_exists($file)) ? include($file) : array();
	}

	/**
	 * Get the config array (may be altered from array that was given in instantiation)
	 * @return array Config
	 */
	public function getDestFiles($lang)
	{
		// set dest path by $lang e.g. /path/to/dest/{en}/
		$destDirs = $this->config['dest'];

		// replace path with $lang
		foreach ($destDirs as $i => $dir) {
			$destDirs[$i] = str_replace("{lang}", $lang, $dir);
		}

		// get files in dir
		return $destDirs;
	}

	/**
	 * Will return the string paths of the destination files. Doesn't check
	 * if file exists (without passing in some option I guess)
	 * @return string Dest file path
	 */
	public function getDestFileByCollection($lang, $collection)
	{
		$destFiles = $this->getDestFiles($lang);

		// loop until a match is found
		$found = null;
		foreach ($destFiles as $file) {
			if (preg_match('/\/' . $lang . '\/' . $collection . '\.php$/', $file, $output_array)) {
				$found = $file;
				break;
			}
		}

		// get files in dir
		return $found;
	}

	/**
	 * Will return the string path of the file
	 * @param string $lang The language code (e.g. "en")
	 * @param string $key The full key ("messages.hello")
	 * @return array Array of ($file, $key) (e.g. ("/../messages.php", "hello"))
	 */
	public function getDestArrayByKey($lang, $key)
	{
		// // get the parts
		// $keyParts = explode('.', $key);
		// if (count($keyParts) < 2)
		//     throw new \Exception('Key doesn\'t have enough parts eg. "file.part1"');

		// from the original $key, extract the $collection and inner $key
		$collection = array_shift($keyParts);
		$key = implode('.' , $keyParts);

		// now, from the dest files try to find one that matches this set
		$file = $this->getDestFileByCollection($lang, $collection);

		return array($file, $key);
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

	/**
	 * Write the data to file
	 * @param string $file File to write e.g. "/path/to/lang/en/messages.php"
	 * @param array $data Data to write
	 * @return void
	 */
	public function writeDataToFile($file, $data)
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
}
