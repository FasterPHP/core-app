<?php
/**
 * App class.
 */
declare(strict_types=1);

namespace FasterPhp\CoreApp;

/**
 * App class.
 */
class App
{
	/**
	 * Supported values for APPLICATION_ENV environment variable.
	 */
	public const APPLICATIONENV_DEVELOPMENT = 'development';
	public const APPLICATIONENV_TESTING = 'testing';
	public const APPLICATIONENV_BUILDING = 'build';
	public const APPLICATIONENV_STAGING = 'staging';
	public const APPLICATIONENV_PRODUCTION = 'production';

	public const APPLICATIONS_ENVS = [
		self::APPLICATIONENV_DEVELOPMENT,
		self::APPLICATIONENV_TESTING,
		self::APPLICATIONENV_BUILDING,
		self::APPLICATIONENV_STAGING,
		self::APPLICATIONENV_PRODUCTION,
	];

	/**
	 * Application environment.
	 *
	 * @var string
	 */
	protected string $_applicationEnv;

	/**
	 * App root directory.
	 *
	 * @var string
	 */
	protected string $_rootDir;

	/**
	 * Config instance.
	 *
	 * @var Config
	 */
	protected Config $_config;

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	protected static ?App $_instance;

	/**
	 * Get App instance.
	 *
	 * @return self
	 */
	public static function getInstance(): App
	{
		if (!isset(self::$_instance)) {
			throw new Exception('App not instantiated');
		}

		return self::$_instance;
	}

	/**
	 * Set/clear instance, for unit testing.
	 *
	 * @param App|null $app App instance, or null to clear.
	 *
	 * @return void
	 */
	public static function setInstance(App $app = null): void
	{
		self::$_instance = $app;
	}

	/**
	 * Constructor.
	 *
	 * Singleton that can be instantiated normally, but only once.
	 *
	 * @param string $rootDir App root directory.
	 *
	 * @throws Exception If root directory invalid.
	 */
	public function __construct(string $rootDir)
	{
		if (isset(self::$_instance)) {
			throw new Exception('App instance already created');
		} elseif (!is_dir($rootDir)) {
			throw new Exception("Invalid root directory '$rootDir'");
		}
		self::$_instance = $this;

		$this->_rootDir = $rootDir;
		$this->_initialise();
	}

	/**
	 * Getter for application env.
	 *
	 * @return string
	 */
	public function getApplicationEnv(): string
	{
		return $this->_applicationEnv;
	}

	/**
	 * Getter for application root dir.
	 *
	 * @return string
	 */
	public function getRootDir(): string
	{
		return $this->_rootDir;
	}

	/**
	 * Setter for Config.
	 *
	 * @param Config $config Config instance.
	 *
	 * @return $this
	 */
	public function setConfig(Config $config): App
	{
		$this->_config = $config;
		return $this;
	}

	/**
	 * Getter for Config.
	 *
	 * @return Config|null
	 */
	public function getConfig(): ?Config
	{
		return $this->_config;
	}

	/**
	 * Initialise the environment.
	 *
	 * @return void
	 */
	protected function _initialise(): void
	{
		$applicationEnv = $this->_getEnvironmentConstant('APPLICATION_ENV');

		if (empty($applicationEnv)) {
			throw new Exception('APPLICATION_ENV not set');
		} elseif (!in_array($applicationEnv, self::APPLICATIONS_ENVS)) {
			throw new Exception("Unknown/unsupported application env '$applicationEnv'");
		}

		if (defined('APPLICATION_ENV') && APPLICATION_ENV != $applicationEnv) {
			throw new Exception("APPLICATION_ENV constant already set to '" . APPLICATION_ENV . "'");
		} elseif (!defined('APPLICATION_ENV')) {
			define('APPLICATION_ENV', $applicationEnv);
		}

		$this->_applicationEnv = $applicationEnv;
	}

	/**
	 * Get environment variable or constant.
	 *
	 * @param string $name Environment variable or contant name.
	 *
	 * @return string|null
	 */
	protected function _getEnvironmentConstant(string $name): ?string
	{
		global $argv;

		// Command line args take precedence
		if (isset($argv) && is_array($argv)) {
			foreach ($argv as $arg) {
				// Look for -eMYPROP=somevalue
				if (strpos($arg, '-e' . $name . '=') === 0) {
					return substr($arg, strlen('-e' . $name . '='));
				}
			}
		}

		// Application constants are next in line
		if (defined($name)) {
			return constant($name);
		}

		// Environment constants come last
		if (getenv($name)) {
			return getenv($name);
		}

		return null;
	}
}
