<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests [As3et]
 *
 * @group as3et
 * @group as3et.core
 *
 * @package    As3et
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
class As3et_As3etTest extends Unittest_TestCase
{
	/**
	 * Data Provider for test_url_should_be_prefixed_according_to_mode
	 * @return array
	 */
	public function provider_url_should_be_prefixed_according_to_mode()
	{
		return array(
			array(
				As3et::MODE_LOCAL,
				'foo.bar',
				'assets/foo.bar'
			),
			array(
				As3et::MODE_S3,
				'foo.bar',
				'http://foo.s3-eu-west-1.amazonaws.com/sha/foo.bar'),
			array(
				As3et::MODE_LOCAL,
				'http://some.other.server/asset.foo',
				'http://some.other.server/asset.foo'
			),
			array(
				As3et::MODE_S3,
				'http://some.other.server/asset.foo',
				'http://some.other.server/asset.foo'
			),
		);
	}

	/**
	 * Verify that As3et appropriately prefixes relative asset URLs with an S3
	 * or local url as required, based on the setting of as3et.mode in config
	 *
	 * @dataProvider provider_url_should_be_prefixed_according_to_mode
	 */
	public function test_url_should_be_prefixed_according_to_mode($mode, $file, $expect_url)
	{
		$config = Kohana::$config->load('as3et');
		$config->set('mode', $mode);
		$config->set('s3', Arr::merge($config->get('s3'), array(
			'bucket' => 'foo',
			'region' => 's3-eu-west-1.amazonaws.com'
		)));
		
		$as3et = $this->getMock('As3et', array('current_sha'));
		$as3et->expects($this->any())
				->method('current_sha')
				->will($this->returnValue('sha'));

		$this->assertEquals($expect_url, $as3et->url($file));

	}

	/**
	 * Data Provider for test_url_should_use_SSL_protocol_when_required
	 * @return array
	 */
	public function provider_url_should_use_SSL_protocol_when_required()
	{
		return array(
			array(FALSE, 'http'),
			array(TRUE, 'https')
		);
	}

	/**
	 * To avoid mixed content warnings, As3et should automatically use HTTPS for
	 * the S3 URL if the initial request from the client to our application was
	 * secure.
	 *
	 * @param boolean $initial_ssl
	 * @param string $expect_scheme
	 * @dataProvider provider_url_should_use_SSL_protocol_when_required
	 */
	public function test_url_should_use_SSL_protocol_when_required($initial_ssl, $expect_scheme)
	{
		Kohana::$config->load('as3et')->set('mode', As3et::MODE_S3);
		Request::initial()->secure($initial_ssl);

		$as3et = new As3et;
		
		$this->assertEquals($expect_scheme, parse_url($as3et->url('foo.bar'), PHP_URL_SCHEME));
	}

}
