<?php
/**
 * Tests for Config class.
 */
namespace FasterPhp\CoreApp;

use PHPUnit\Framework\TestCase;

/**
 * Tests for Config class.
 */
class ConfigTest extends TestCase
{
	/**
	 * Test data.
	 *
	 * @var array
	 */
	private $_data = [
		'foo' => [
			'bar' => 'value',
		],
	];

	/**
	 * Test get undefined value.
	 *
	 * @return void
	 */
	public function testGetNotSet(): void
	{
		$config = new Config([]);

		$this->expectException(\FasterPhp\CoreApp\Exception::class);
		$this->expectExceptionMessage("Config not set for 'notSet'");

		$config->notSet;
	}

	/**
	 * Test get recursive Config instance.
	 *
	 * @return void
	 */
	public function testGetConfig(): void
	{
		$config = new Config($this->_data);

		$this->assertInstanceOf(Config::class, $config->foo);
	}

	/**
	 * Test get primitive value.
	 *
	 * @return void
	 */
	public function testGet(): void
	{
		$config = new Config($this->_data);

		$this->assertSame('value', $config->foo->bar);
	}

	/**
	 * Test get object.
	 *
	 * @return void
	 */
	public function testGetObject(): void
	{
		$data = ['foo' => (object) ['bar' => 'value']];
		$config = new Config($data);

		$object = $config->foo;
		$this->assertInstanceOf(\stdClass::class, $object);
		$this->assertSame($data['foo'], $object);
	}

	/**
	 * Test get array.
	 *
	 * @return void
	 */
	public function testToArray(): void
	{
		$config = new Config($this->_data);

		$array = $config->foo->toArray();
		$this->assertIsArray($array);
		$this->assertSame($this->_data['foo'], $array);
	}
}
