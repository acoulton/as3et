<?php defined('SYSPATH') OR die('No direct script access.');
class ACoulton_As3et
{
	/**
	 * Indicates that assets are being served locally
	 */
	const MODE_LOCAL = 'local';

	/**
	 * Indicates that assets are being served from S3
	 */
	const MODE_S3 = 's3';

	/**
	 * Local cache of the a3set config
	 * @var Config_Group
	 */
	protected $_config = NULL;

	public static function instance()
	{

	}

	/**
	 * Loads the config and caches it in the object for later use
	 */
	public function __construct()
	{
		$this->_config = Kohana::$config->load('as3et');
	}

	/**
	 * Gets the URL for an asset file, either locally or from S3 depending on the
	 * a3set.mode configuration setting. Absolute URLs containing a URL scheme prefix
	 * will not be changed.
	 *
	 * @param string $url  The relative URL to the asset file
	 * @return string
	 */
	public function url($url)
	{
		// Don't change absolute/external URLS
		if (strpos($url, '://'))
			return $url;

		// In local mode, prefix with assets and return
		if ($this->_config['mode'] === self::MODE_LOCAL)
			return 'assets/'.$url;

		// The S3 URL is the bucket and host, plus the current deployed SHA
		$protocol = Request::initial()->secure() ? 'https://' : 'http://';
		$host = $this->_config['s3']['bucket'].'.'.$this->_config['s3']['region'];
		return $protocol.$host.'/'.$this->current_sha().'/'.$url;
	}

	public function current_sha()
	{		
		return '';
	}
	
}