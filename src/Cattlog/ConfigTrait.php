<?php namespace Cattlog;

/**
 * Accessor methods for config
 */

trait ConfigTrait
{
	/**
	 * @var array $config Config passed in
	 */
	protected $config = array();

	/**
	 * Get config options
	 * @return array $config Config options
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Set config options
	 * @return array $config Config options
	 */
	public function setConfig($newConfig)
	{
		$this->config = array_merge($this->config, $newConfig);
	}

}
