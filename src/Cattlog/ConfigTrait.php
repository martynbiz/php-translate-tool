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
	public function setConfig($config)
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

		$this->config = array_merge($this->config, $config);
	}

}
