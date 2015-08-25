<?php namespace Cattlog\Adapters;

class Php implements AdapterInterface
{
	/**
	 * Get the data from a file into an array
	 * @return array Data from file, or empty array (! file_exists)
	 */
	public function getData($file) // getData
	{
		return (file_exists($file)) ? include($file) : array();
	}

	/**
	 * Write the data to file
	 * @param string $file File to write e.g. "/path/to/lang/en/messages.php"
	 * @param array $data Data to write
	 * @return void
	 */
	public function putData($file, $data) // putData
	{
		// ensure all folders exist
		// TODO move this to FileSystem
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
