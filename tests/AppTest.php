<?php
/**
 * Tests for App class.
 */
namespace FasterPhp\CoreApp;

use PHPUnit\Framework\TestCase;

/**
 * Tests for App class.
 */
class AppTest extends TestCase
{
	/**
	 * Cache of initial App instance.
	 *
	 * @var App
	 */
	protected static App $_app;

	/**
	 * Cache of initial APPLICATION_ENV constant.
	 *
	 * @var string|null
	 */
	protected static ?string $_applicationEnvConstant;

	/**
	 * Cache of initial APPLICATION_ENV environment variable.
	 *
	 * @var string|null
	 */
	protected static ?string $_applicationEnvEnvVar;

	/**
	 * Cache of initial APPLICATION_ENV server variable.
	 *
	 * @var string|null
	 */
	protected static ?string $_applicationEnvServerVar;

	/**
	 * Setup before any tests are run.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		self::$_app = App::getInstance();
		self::$_applicationEnvConstant = defined('APPLICATION_ENV') ? APPLICATION_ENV : null;
		self::$_applicationEnvEnvVar = false !== getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : null;
		self::$_applicationEnvServerVar = isset($_SERVER['APPLICATION_ENV']) ? $_SERVER['APPLICATION_ENV'] : null;
	}

	/**
	 * Teardown after all tests have run.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void
	{
		self::_restoreVars();
		App::setInstance(self::$_app);
	}

	/**
	 * Setup before each test.
	 *
	 * @return void
	 */
	public function setUp(): void
	{
		App::setInstance();
	}

	/**
	 * Test getInstance method when App not instantiated.
	 *
	 * @return void
	 */
	public function testGetInstanceNotInstantiated(): void
	{
		$this->expectException(\FasterPhp\CoreApp\Exception::class);
		$this->expectExceptionMessage('App not instantiated');

		App::getInstance();
	}

	/**
	 * Test getInstance method.
	 *
	 * @return void
	 */
	public function testGetInstance(): void
	{
		$app = new App(dirname(__DIR__));

		$this->assertSame($app, App::getInstance());
	}

	/**
	 * Test double instantiation.
	 *
	 * @return void
	 */
	public function testInstanceExists(): void
	{
		$this->expectException(\FasterPhp\CoreApp\Exception::class);
		$this->expectExceptionMessage('App instance already created');

		new App(dirname(__DIR__));
		new App(dirname(__DIR__));
	}

	/**
	 * Test invalid root dir.
	 *
	 * @return void
	 */
	public function testInvalidRootDir(): void
	{
		$this->expectException(\FasterPhp\CoreApp\Exception::class);
		$this->expectExceptionMessage("Invalid root directory 'notExists'");

		new App('notExists');
	}

	/**
	 * Test application env not set.
	 *
	 * @return void
	 */
	public function testApplicationEnvNotSet(): void
	{
		if (!extension_loaded('runkit7')) {
			$this->markTestSkipped('Skipping test because the runkit7 extension is not available.');
		}
		self::_clearVars();

		// Note: Can't use expectException() because we don't get a chance to call _restoreVars()
		try {
			new App(dirname(__DIR__));
		} catch (\FasterPhp\CoreApp\Exception $ex) {
			$this->assertSame('APPLICATION_ENV not set', $ex->getMessage());
			self::_restoreVars();
			return;
		}

		$this->fail('Expected exception not thrown');
	}

	/**
	 * Test application env not valid.
	 *
	 * @return void
	 */
	public function testApplicationEnvInvalid(): void
	{
		if (!extension_loaded('runkit7')) {
			$this->markTestSkipped('Skipping test because the runkit7 extension is not available.');
		}
		self::_clearVars();

		define('APPLICATION_ENV', 'invalid');

		// Note: Can't use expectException() because we don't get a chance to call _restoreVars()
		try {
			new App(dirname(__DIR__));
		} catch (\FasterPhp\CoreApp\Exception $ex) {
			$this->assertSame("Unknown/unsupported application env 'invalid'", $ex->getMessage());
			self::_restoreVars();
			return;
		}

		$this->fail('Expected exception not thrown');
	}

	/**
	 * Test getApplicationEnv method.
	 *
	 * @return void
	 */
	public function testGetApplicationEnv(): void
	{
		$app = new App(dirname(__DIR__));

		$this->assertSame(App::APPLICATIONENV_TESTING, $app->getApplicationEnv());
	}

	/**
	 * Test getRootDir method.
	 *
	 * @return void
	 */
	public function testGetRootDir(): void
	{
		$app = new App(dirname(__DIR__));

		$this->assertSame(dirname(__DIR__), $app->getRootDir());
	}

	/**
	 * Test setConfig and getConfig methods.
	 *
	 * @return void
	 */
	public function testSetGetConfig(): void
	{
		$config = new Config(['foo' => 'bah']);

		$app = new App(dirname(__DIR__));

		$this->assertSame($app, $app->setConfig($config));
		$this->assertSame($config, $app->getConfig());
	}

	/**
	 * Clear all possible definitions of APPLICATION_ENV.
	 *
	 * @return void
	 */
	protected static function _clearVars(): void
	{
		if (defined('APPLICATION_ENV')) {
			runkit7_constant_remove('APPLICATION_ENV');
		}
		putenv('APPLICATION_ENV');
		unset($_SERVER['APPLICATION_ENV']);
	}

	/**
	 * Restore all possible definitions of APPLICATION_ENV.
	 *
	 * @return void
	 */
	protected static function _restoreVars(): void
	{
		if (!empty(self::$_applicationEnvConstant) && extension_loaded('runkit7')) {
			if (defined('APPLICATION_ENV')) {
				runkit7_constant_remove('APPLICATION_ENV');
			}
			define('APPLICATION_ENV', self::$_applicationEnvConstant);
		}
		if (!empty(self::$_applicationEnvEnvVar)) {
			putenv('APPLICATION_ENV=' . self::$_applicationEnvEnvVar);
		}
		if (!empty(self::$_applicationEnvServerVar)) {
			$_SERVER['APPLICATION_ENV'] = self::$_applicationEnvServerVar;
		}
	}
}
