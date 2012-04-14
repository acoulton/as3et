<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests base functionality of [As3et_Collection] and descendent classes
 *
 * @group as3et
 * @group as3et.collection.base
 *
 * @package    As3et
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
abstract class As3et_CollectionBaseTest extends Unittest_TestCase
{

	/**
	 * The mock as3et instance passed to the class
	 * @var As3et
	 */
	protected $as3et_instance = NULL;

	/**
	 * Returns an instance of the class to test
	 * @return As3et_Collection
	 */
	abstract protected function get_class($as3et);

	/**
	 * Verify that we can inject a mock As3et class into this collection class
	 */
	public function test_should_support_as3et_injection()
	{
		// Create a new As3et and inject into the class
		$as3et = new As3et;
		$class = $this->get_class($as3et);

		// Verify that the same As3et class is returned
		$this->assertSame($as3et, $class->as3et());
	}

	/**
	 * Verify that the tag() method calls As3et::url to get the right URL for
	 * an asset file.
	 * @depends test_should_support_as3et_injection
	 */
	public function test_tag_should_get_as3et_url()
	{
		$as3et = $this->getMock('As3et');
		$as3et->expects($this->once())
				->method('url')
				->with('foo.bar')
				->will($this->returnValue('/foo.bar'));

		$class = $this->get_class($as3et, 'foo.bar');

		$this->assertEquals('/foo.bar', $class->tag());
	}

	/**
	 * Verfiy that the tag() method includes HTML attributes specified in the
	 * second param
	 */
	public function test_tag_should_include_html_attributes_from_first_param()
	{
		$as3et = new As3et;

		$class = $this->get_class($as3et, 'foo.bar');
		$output = $class->tag(array('data-foo' => 'b', 'media'=>'print'));

		$this->assertContains('data-foo="b"', $output);
		$this->assertContains('media="print"', $output);
	}
}
