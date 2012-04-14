<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Wrapper class for [AmazonS3] which allows for loading config from the Kohana
 * Config system and generally initialising the class.
 *
 * @package    As3et
 * @category   S3 Wrapper
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
class ACoulton_As3et_S3
{
	/**
	 * Local storage
	 * @var AmazonS3
	 */
	protected $_s3 = NULL;

	/**
	 * Creates a new instance of As3et_S3
	 *
	 * @return As3et_S3
	 */
	public static function factory()
	{
		require_once(Kohana::find_file('vendor', 'aws-sdk-for-php/sdk.class'));
		return new As3et_S3;
	}

	/**
	 * Returns the current AmazonS3 instance (shared within a single As3et_S3
	 * class).
	 *
	 * @return AmazonS3
	 */
	public function s3()
	{
		if ( ! $this->_s3)
		{
			// Load the config and instantiate Amazon S3
			$config = Kohana::$config->load('as3et.s3');
			$this->_s3 = new AmazonS3(array(
				'key' => $config['key'],
				'secret' => $config['secret']
			));
		}

		return $this->_s3;
	}
}