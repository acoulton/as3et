<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests [As3et_S3]
 *
 * @group as3et
 * @group as3et.s3
 *
 * @package    As3et
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
class As3et_As3etTest extends Unittest_TestCase
{

	public function test_should_create_amazons3_with_credentials_from_config()
	{
		// Set config values
		Kohana::$config->load('as3et')->set('s3.key', 'key-foo');
		Kohana::$config->load('as3et')->set('s3.token', 'token-foo');

		// Create the S3 object
		$s3 = As3et_S3::factory()->s3();

		// Validate that it is of the correct type and properly configured
		$this->assertInstanceOf('AmazonS3', $s3);
		$this->assertEquals('key-foo', $s3->key);
		$this->assertEquals('token-foo', $s3->bar);
	}

	

}