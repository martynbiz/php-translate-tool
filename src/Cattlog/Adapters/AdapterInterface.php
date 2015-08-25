<?php namespace Cattlog\Adapters;

// use Cattlog\FileSystem;

interface AdapterInterface
{
	// /**
	//  * @param FileSystem $fileSystem For any file access requests (eg. get array of source files)
	//  * @param array $config Config for the class
	//  */
	// public function __construct(FileSystem $fileSystem);

	/**
	 * Get data from the file and convert it to an array
	 * @param string $file The file to get data from
	 */
	public function getData($file);

	/**
	 * Write an array to the file and encode for this adapter
	 * @param string $file The file to get data from
	 */
	public function putData($file, $data);

}
