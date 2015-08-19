<?php namespace Cattlog\Adapters;

use Cattlog\FileSystem;

interface AdapterInterface
{

	/**
	 * @param FileSystem $fileSystem For any file access requests (eg. get array of source files)
	 * @param array $config Config for the class
	 */
	public function __construct(FileSystem $fileSystem, $config=array());

	/**
	 * Will add keys with blank values
	 * @param array $data Data to add keys to
	 * @param array $keysToAdd Keys to add to data
	 * @return array Data with keys added
	 */
	public function add(&$data, $keys);

	/**
	 * Will remove an array of keys from data
	 * @param array $data Data to remove keys from
	 * @param string|array $keys Keys to remove from data
	 * @return array Data with keys removed
	 */
	public function remove(&$data, $keys);

	/**
	 * This will compare the old and newly scanned keys, and
	 * return an array of keys which are new. Useful for
	 * showing what difference between scans
	 * @param array $keysFromDest The array of old key/pairs
	 * @param array $keysFromScan The array of new key/pairs
	 * @return array Added key/values
	 */
	public function getDiffAddedKeys($keysFromDest, $keysFromScan);

	/**
	 * This will compare the old and newly scanned keys, and
	 * return an array of keys which have been removed. Useful for
	 * showing what difference between scans
	 * @param array $keysFromDest The array of old key/pairs
	 * @param array $keysFromScan The array of new key/pairs
	 * @return array Removed key/values
	 */
	public function getDiffRemovedKeys($keysFromDest, $keysFromScan);

	/**
	 * Set key in $data array with dot notation
	 * @param array $data Data to add keys to
	 * @param array $key Keys to add to data
	 * @param array $options Options to e.g. set new keys
	 * @return array The data array passed in
	 */
	public function setValue(&$data, $key, $newValue, $options=array());

	/**
	 * Get key in $data array with dot notation
	 * @param array $data Data to add keys to
	 * @param array $key Keys to add to data
	 * @return array The data array passed in
	 */
	public function getValue($data, $key);

	/**
	 * Check whether key exists in $data
	 * @param array $data Data to add keys to
	 * @param array $key Keys to add to data
	 * @return boolean True if exists
	 */
	public function hasKey($data, $key);

	/**
	 * Get the keys from source directories
	 * @return array Keys in an indexed array
	 */
	public function getKeysFromSrcFiles();

	/**
	 * Get the keys from source directories.
	 * @return array Keys in an indexed array
	 */
	public function getKeysFromDestFiles($lang);
}
