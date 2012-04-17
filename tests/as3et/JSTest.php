<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests functionality of [As3et_CSS] (including tests inherited from [As3et_CollectionBaseTest]
 *
 * @group as3et
 * @group as3et.collection.js
 *
 * @package    As3et
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
class As3et_JSTest extends As3et_BaseCollectionTest
{
	/**
	 * Returns an instance of the class to test
	 * @return As3et_JS
	 */
	protected function get_class($as3et)
	{
		return new As3et_JS($as3et,'foo.bar');
	}

	/**
	 * Verifies that the factory method creates a new As3et_JS instance with a
	 * reference to the As3et singleton and the filename.
	 */
	public function test_factory_should_create_with_as3et_singleton_and_file()
	{
		$js = As3et_JS::factory('foo.bar');

		$this->assertInstanceOf('As3et_JS', $js);
		$this->assertSame(As3et::instance(), $js->as3et());
		$this->assertEquals('foo.bar', $js->file());
	}

}