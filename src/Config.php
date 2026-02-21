<?php
/**
 * Config class.
 */
declare(strict_types=1);

namespace FasterPhp\CoreApp;

/**
 * Config class.
 */
class Config
{
	/**
	 * Config data.
	 *
	 * @var array
	 */
	private array $_data;

	/**
	 * Object constructor.
	 *
	 * @param array $data Config data.
	 */
	public function __construct(array $data)
	{
		$this->_data = $data;
	}

	/**
	 * Get config data array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->_data;
	}

	/**
	 * Get config data.
	 *
	 * @param string ...$keys Name of key(s).
	 *
	 * @return mixed
	 * @throws Exception If key not set.
	 */
	public function get(string ...$keys)
	{
		$data = $this->_data;
		foreach ($keys as $key) {
			if (!is_array($data) || !array_key_exists($key, $data)) {
				throw new Exception("Config not set for '" . implode('->', $keys) . "'");
			}
			$data = $data[$key];
		}
		return $data;
	}

	/**
	 * Magic __get method.
	 *
	 * Allows recursive access to config values, such as a $config->key1->key2.
	 *
	 * @param string $name Property name.
	 *
	 * @return Config|mixed
	 */
	public function __get(string $name)
	{
		$value = $this->get($name);
		if (is_array($value)) {
			return new Config($value);
		}
		return $value;
	}
}
