<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests functionality of [As3et_CSS] (including tests inherited from [As3et_CollectionBaseTest]
 *
 * @group as3et
 * @group as3et.collection.css
 *
 * @package    As3et
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */

class As3et_CSSTest extends As3et_BaseCollectionTest
{

	/**
	 * Returns an instance of the class to test
	 * @return As3et_CSS
	 */
	protected function get_class($as3et)
	{
		return new As3et_CSS($as3et,'foo.bar');
	}

	/**
	 * Verifies that the factory method creates a new As3et_CSS instance with a
	 * reference to the As3et singleton and the filename.
	 */
	public function test_factory_should_create_with_as3et_singleton_and_file()
	{
		$css = As3et_CSS::factory('foo.bar');

		$this->assertInstanceOf('As3et_CSS', $css);
		$this->assertSame(As3et::instance(), $css->as3et());
		$this->assertEquals('foo.bar', $css->file());
	}
}