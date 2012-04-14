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
	 * Creates a new instance of As3et_S3
	 *
	 * @return As3et_S3
	 */
	public static function factory()
	{
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

	}
}