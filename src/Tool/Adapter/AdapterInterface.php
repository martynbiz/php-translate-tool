<?php
namespace MartynBiz\Translate\Tool\Adapter;

interface AdapterInterface
{
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
