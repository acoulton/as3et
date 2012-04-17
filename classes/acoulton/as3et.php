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

	/**
	 * Instance storage of the current SHA
	 */
	protected $_sha = NULL;

	/**
	 * Returns a singleton As3et instance for general use.
	 * 
	 * @staticvar string $instance
	 * @return As3et
	 */
	public static function instance()
	{
		static $instance = NULL;

		if ( ! $instance)
		{
			$instance = new As3et;
		}

		return $instance;
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

		$sha = $this->current_sha();

		// In local mode or if there's no SHA, prefix with assets and return
		if (($this->_config['mode'] === self::MODE_LOCAL) OR ($sha === NULL))
			return 'assets/'.$url;

		// The S3 URL is the bucket and host, plus the current deployed SHA
		$protocol = Request::initial()->secure() ? 'https://' : 'http://';
		$host = $this->_config['s3']['bucket'].'.'.$this->_config['s3']['region'];
		return $protocol.$host.'/'.$sha.'/'.$url;
	}

	/**
	 * Sets the current SHA and stores it to disk - this SHA is used as part of
	 * the path to achieve atomic and instant deployment of assets
	 *
	 * @param string $sha
	 */
	public function set_deploy_sha($sha)
	{
		$file = $this->_config['revision_file'];

		// Create the path if required
		$path = dirname($file);
		if ( ! file_exists($path))
		{
			mkdir($path, 0664, TRUE);
		}

		// Output to a parsable PHP file (so APC will cache it for us)
		file_put_contents($file, '<?php return '.var_export($sha, TRUE).';');
		$this->_sha = $sha;
	}

	/**
	 * Returns the current deploy SHA
	 *
	 * @return string
	 */
	public function current_sha()
	{
		// Load the revision SHA from disk
		if (($this->_sha === NULL) AND (file_exists($this->_config['revision_file'])))
		{
			$this->_sha = include($this->_config['revision_file']);
		}

		return $this->_sha;
	}

}