<?php namespace Cattlog\Adapters;

// as this tool is for use within Laravel, we'll just use some of it's array helpers
use Illuminate\Support\Arr;
use Cattlog\ConfigTrait;
use Cattlog\FileSystem;

class CattlogLaravel5Adapter implements AdapterInterface
{
	use ConfigTrait;

	/**
	 * @var FileSystem $fileSystem FileSystem object to access files/dirs
	 */
	protected $fileSystem;

	/**
	 * @param FileSystem $fileSystem For any file access requests (eg. get array of source files)
	 * @param array $config Config for the class
	 */
	public function __construct(FileSystem $fileSystem, $config=array())
	{
		// set default config for Laravel
		$this->config = array_merge(array(
			'pattern' => array(
				'/trans\\s*\\(\\s*[\\\'|\\"]([A-Za-z0-9_\\-\\.]*)[\\\'|\\"]/',
				'/Lang::get\\s*\\(\\s*[\\\'|\\"]([A-Za-z0-9_\\-\\.]*)[\\\'|\\"]/',
			),
		), $config);

		// we'll use file system for any file access related stuff
		// also by passing it in, let's us test the class more effectively
		$this->fileSystem = $fileSystem;
	}

	/**
	 * Will remove an array of keys from data
	 * @param array $data Data to remove keys from
	 * @param string|array $keys Keys to remove from data
	 * @return array Data with keys removed
	 */
	public function remove($data, $keys)
	{
		// ensure keys is an array
		if (! is_array($keys))
			$keys = array($keys);

		// loop through each key and remove it
		foreach ($keys as $key) {
			Arr::forget($data, $key);
		}

		// Tidy up empty arrays
		$data = $this->removeEmptyKeys($data);

		return $data;
	}

	/**
	 * Will add keys with blank values
	 * @param array $data Data to add keys to
	 * @param array $keysToAdd Keys to add to data
	 * @return array Data with keys added
	 */
	public function add($data, $keys)
	{
		// ensure keys is an array
		if (! is_array($keys))
			$keys = array($keys);

		// loop through each key and add it
		// only add if it doesn't exist, just encase we accidentally overwrite
		foreach ($keys as $key) {

			// use Laravel's array_get to check if the element exists using dot notation
			if(! Arr::has($data, $key))
				$this->setValue($data, $key, '');
		}

		return $data;
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
		return Arr::get($data, $key);
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
		if(Arr::has($data, $key) or $options['create']) {
			Arr::set($data, $key, $newValue);
		}

		return $data;
	}

	/**
	 * Check whether key exists in $data
	 * @param array $data Data to add keys to
	 * @param array $key Keys to add to data
	 * @return boolean True if exists
	 */
	public function hasKey($data, $key)
	{
		return Arr::has($data, $key);
	}

	/**
	 * Get the keys from source directories
	 * @return array Keys in an indexed array
	 */
	public function getKeysFromSrcFiles()
	{
		// get files in dir
		$files = $this->fileSystem->getSrcFiles();

		// for each file, get the key in string format
		$keys = array();
		foreach ($files as $file) {

			// get the contents of the file
			$contents = $this->fileSystem->getFileContents($file);

			// regex on it to get all the matches
			foreach ($this->config['pattern'] as $pattern) {
				preg_match_all($pattern, $contents, $matches);
				$keys = array_merge($matches[1], $keys);
			}
		}

		// array_flip will deal with the duplicates
		return $keys;
	}

	/**
	 * Get the keys from source directories.
	 * @return array Keys in an indexed array
	 */
	public function getKeysWithValuesFromDestFiles($lang)
	{
		// get files in dir
		$files = $this->fileSystem->getDestFiles($lang);

		// for each file, get the key in string format
		$keys = array();
		foreach ($files as $file) {

			// get the {prefix} from /path/to/files/{prefix}.php
			preg_match("/\/([A-Za-z0-9_\-\.]*)\.php$/", $file, $parts);
			$prefix = @$parts[1] . '.';

			// flatten to get keys such as "between.numeric", then take the
			// keys only (array_keys), and merge with existing (array_merge)
			$data = $this->fileSystem->getFileData($file);
			$flattened = Arr::dot($data, $prefix);
			$keys = array_merge($flattened, $keys);
		}


		return $keys;
	}

	/**
	 * Get the keys from source directories.
	 * @return array Keys in an indexed array
	 */
	public function getKeysFromDestFiles($lang)
	{
		// get files in dir
		$data = $this->getKeysWithValuesFromDestFiles($lang);

		return array_keys($data);
	}

	/**
	 * Will remove empty keys from data recursively. Useful after
	 * removing keys and empty arrays remain
	 * @param array $data Data to remove keys from
	 * @param array $keysToRemove Keys to remove from data
	 * @return array Data with keys removed
	 */
	protected function removeEmptyKeys($data) {

		// first loop through and build array of elements to remove
		$keysToRemove = array();
		foreach ($data as $key => $value) {

			// first, if an array .. dig first
			if (is_array($data[$key])) {
				$data[$key] = $this->removeEmptyKeys($data[$key]);
			}

			// by this point, some children may have been removed, check
			if (is_array($data[$key]) and empty($data[$key])) {
				array_push($keysToRemove, $key);
			}
		}

		// no we are out the loop, delete all those that were empty
		foreach ($keysToRemove as $key) {
			unset($data[$key]);
		}

		return $data;
	}
}
