<?php namespace Cattlog;

/**
 * This is the interface for the CLI script to use. Ideally everything should go
 * through this, each method can transfer responsibility to the adapter if required
 * Still trying to figure the ideal structure for this
 * If possible, only here should deal with config (so maybe remove config trait)
 */

use Cattlog\Adapters\AdapterInterface;

class Cattlog
{
	use ConfigTrait;

	/**
	 * @var AdapterInterface $adapter Adapter (e.g. Json) selected for use
	 */
	protected $adapter;

	/**
	 * @var FileSystem $fileSystem FileSystem object to access files/dirs
	 */
	protected $fs;

	/**
	 * @var array $srcFiles Cache of source files, retrieved on each execution
	 */
	protected $srcFiles;

	/**
	 * @param FileSystem $fileSystem For any file access requests (eg. get array of source files)
	 * @param array $config Config for the class
	 */
	public function __construct(AdapterInterface $adapter, FileSystem $fileSystem, $config=array())
	{
		// // set default config for Laravel
		// $this->config = array_merge(array(
		// 	'pattern' => array(
		// 		'/xlate\\s*\\(\\s*[\\\'|\\"]([A-Za-z0-9_\\-\\.]*)[\\\'|\\"]/',
		// 	),
		// ), $config);

		// this will also set default vales
		$this->setConfig($config);

		// set the adapter we will use for the file interaction
		$this->adapter = $adapter;

		// we'll use file system for any file access related stuff
		// also by passing it in, let's us test the class more effectively
		$this->fs = $fileSystem;
	}

	/**
	 * Will add keys with blank values
	 * @param array $data Data to add keys to
	 * @param array $keysToAdd Keys to add to data
	 * @return array Data with keys added
	 */
	public function addKeys(&$data, $keys)
	{
		// ensure keys is an array
		if (! is_array($keys))
			$keys = array($keys);

		// flip keys, so we can merge them with $data
		$keys = array_fill_keys($keys, '');

		// set each value with the value of the key, easier to spot when missing
		array_walk($keys, function(&$value, $key) {
			$value = $key;
		});

		// $data will overwrite $keys so previous values not overwritten
		$data = array_merge($keys, $data);
	}

	/**
	 * Will remove an array of keys from data
	 * @param array $data Data to remove keys from
	 * @param string|array $keys Keys to remove from data
	 * @return array Data with keys removed
	 */
	public function removeKeys(&$data, $keys)
	{
		// ensure keys is an array
		if (! is_array($keys))
			$keys = array($keys);

		$data = array_diff_key($data, array_flip($keys));
	}

	/**
	 * This will compare the old and newly scanned keys, and
	 * return an array of keys which are new. Useful for
	 * showing what difference between scans
	 * @param array $keysFromDest The array of old key/pairs
	 * @param array $keysFromScan The array of new key/pairs
	 * @return array Added key/values
	 */
	public function getDiffAddedKeys($keysFromDest, $keysFromScan)
	{
		// find the keys that are in $keysFromDest, but not long in $keysFromScan
		return array_values(array_diff($keysFromScan, $keysFromDest));
	}

	/**
	 * This will compare the old and newly scanned keys, and
	 * return an array of keys which have been removed. Useful for
	 * showing what difference between scans
	 * @param array $keysFromDest The array of old key/pairs
	 * @param array $keysFromScan The array of new key/pairs
	 * @return array Removed key/values
	 */
	public function getDiffRemovedKeys($keysFromDest, $keysFromScan)
	{
		// first, find all the keys which have not been removed
		$notRemoved = array_intersect($keysFromScan, $keysFromDest);

		// find the keys that are in $keysFromDest, but not long in $keysFromScan
		// use array_values to fix indexes
		return array_values(array_diff($keysFromDest, $notRemoved));
	}

	/**
	 * Get key in $data array with dot notation
	 * @param array $data Data to add keys to
	 * @param array $key Keys to add to data
	 * @return array The data array passed in
	 */
	public function getValue($data, $key)
	{
		return (array_key_exists($key, $data)) ? $data[$key] : null;
	}

	/**
	 * Set key in $data array with dot notation
	 * @param array $data Data to add keys to
	 * @param array $key Keys to add to data
	 * @param array $options Options to e.g. set new keys
	 * @return array The data array passed in
	 */
	public function setValue(&$data, $key, $newValue, $options=array())
	{
		// default options
		$options = array_merge(array(
			'create' => true, // create new, if none exist
		), $options);

		// use Laravel's array_get to check if the element exists using dot notation
		if(array_key_exists($key, $data) or $options['create']) {

			// try it as json
			$jsonValue = json_decode($newValue);
			if (! is_null($jsonValue)) {
				$newValue = $jsonValue;
			}

			$data[$key] = $newValue;
		}

		return $data;
	}

	/**
	 * Get the data from a dest file via the adapter
	 * @param string $lang The lang to get data for
	 * @return array Data from file, or empty array (! file_exists)
	 */
	public function getData($lang) // getData
	{
		$file = $this->getDestFile($lang);
		$data = $this->adapter->getData($file);

		return $data;
	}

	/**
	 * Write the data to file
	 * @param string $lang The lang to get data for
	 * @param array $data Data to write
	 * @return void
	 */
	public function putData($lang, $data) // putData
	{
		$file = $this->getDestFile($lang);

		$this->adapter->putData($file, $data);
	}

	/**
	 * Check whether key exists in $data
	 * @param array $data Data to add keys to
	 * @param array $key Keys to add to data
	 * @return boolean True if exists
	 */
	public function hasKey($data, $key)
	{
		return array_key_exists($key, $data);
	}

	/**
	 * Get the keys from source directories
	 * @return array Keys in an indexed array
	 */
	public function getKeysFromSrcFiles()
	{
		// get files in dir
		$files = $this->getSrcFiles();

		// for each file, get the key in string format
		$keys = array();
		foreach ($files as $file) {

			// get the contents of the file
			$contents = $this->fs->getFileContents($file);

			// regex on it to get all the matches
			foreach ($this->config['pattern'] as $pattern) {
				preg_match_all($pattern, $contents, $matches);
				$keys = array_merge($matches[1], $keys);
			}
		}

		// array_flip will deal with the duplicates
		return $keys;
	}

	// /**
	//  * Get the keys from source directories.
	//  * @return array Keys in an indexed array
	//  */
	// public function getKeysWithValuesFromDestFiles($lang)
	// {
	// 	// get files in dir
	// 	$file = $this->fileSystem->getDestFile($lang);
	//
	// 	// for each file, get the key in string format
	// 	$keys = array();
	// 	foreach ($files as $file) {
	// 		$data = $this->fileSystem->getFileData($file);
	// 		$keys = array_merge($data, $keys);
	// 	}
	//
	// 	return $keys;
	// }

	/**
	 * Get the keys from source directories.
	 * @return array Keys in an indexed array
	 */
	public function getKeysFromDestFile($lang)
	{
		// get files in dir
		$file = $this->getDestFile($lang);
		$data = $this->adapter->getData($file);

		return array_keys($data);
	}

	// /**
	//  * Will remove empty keys from data recursively. Useful after
	//  * removing keys and empty arrays remain
	//  * @param array $data Data to remove keys from
	//  * @param array $keysToRemove Keys to remove from data
	//  * @return array Data with keys removed
	//  */
	// protected function removeEmptyKeys($data) {
	//
	// 	// first loop through and build array of elements to remove
	// 	$keysToRemove = array();
	// 	foreach ($data as $key => $value) {
	//
	// 		// first, if an array .. dig first
	// 		if (is_array($data[$key])) {
	// 			$data[$key] = $this->removeEmptyKeys($data[$key]);
	// 		}
	//
	// 		// by this point, some children may have been removed, check
	// 		if (is_array($data[$key]) and empty($data[$key])) {
	// 			array_push($keysToRemove, $key);
	// 		}
	// 	}
	//
	// 	// no we are out the loop, delete all those that were empty
	// 	foreach ($keysToRemove as $key) {
	// 		unset($data[$key]);
	// 	}
	//
	// 	return $data;
	// }

	/**
	 * Get specifically source files
	 * @return array Files
	 */
	public function getSrcFiles()
	{
		// src files are cached to be less calls on the fs
		if (!$this->srcFiles) {
			$this->srcFiles = $this->fs->getFiles($this->config['src']);
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
}
