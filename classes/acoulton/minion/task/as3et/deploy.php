<?php defined('SYSPATH') OR die('No direct script access.');

class ACoulton_Minion_Task_As3et_Deploy extends Minion_Task
{
	protected $_s3 = NULL;

	protected $_as3et = NULL;

	protected $_git_sha = NULL;

	/**
	 * Deploys all current asset files (respecting the configuration set in the
	 * As3et configuration) to the appropriate S3 path and - if successful -
	 * stores the current git revision sha for use in Asset URLs at render-time.
	 *
	 * @param array $config  Command line options
	 */
	public function execute(array $config)
	{
		$this->write('Uploading as3et files to S3 for SHA '.$this->current_git_sha());
		$this->write('Storing in bucket '.Kohana::$config->load('as3et.s3.bucket'));

		// Setup the S3 for batch operation
		$this->s3()->batch();

		// Get a list of asset files and upload them
		$files = $this->get_asset_files();
		$this->write(count($files).' as3et files to upload');
		foreach ($files as $file => $disk_path)
		{
			$this->upload_file($file, $disk_path);
		}

		// Send the S3 batch
		$this->write('Sending request to AWS S3');
		$response = $this->s3()->batch()->send();

		if ( ! $response->areOK())
			throw new As3et_Exception_DeployFailed("Deployment Failed", $response);

		$this->write('Deployment successful');

		// Store the SHA for future
		$this->as3et()->set_deploy_sha($this->current_git_sha());
	}

	/**
	 * Wrapper for [Minion_CLI::write] to allow output suppression during unit
	 * tests.
	 *
	 * @param string $text
	 * @param string $foreground
	 * @param string $background
	 */
	public function write($text, $foreground = NULL, $background = NULL)
	{
		Minion_CLI::write($text, $foreground, $background);
	}

	/**
	 * Provides an AmazonS3 instance for communication with Amazon - allows
	 * injection of an instance (eg for testing).
	 *
	 * @param AmazonS3 $s3  AmazonS3 instance to inject
	 * @return AmazonS3
	 */
	public function s3($s3 = NULL)
	{
		// If an instance is provided, set it.
		if ($s3 !== NULL)
		{
			$this->_s3 = $s3;
		}

		// If no instance exists, create one.
		if ($this->_s3 === NULL)
		{
			$this->_s3 = As3et_S3::factory()->s3();
		}

		return $this->_s3;
	}

	/**
	 * Provides an As3et instance - allows injection of an instance (eg for testing).
	 *
	 * @param As3et $as3et  As3et instance to inject
	 * @return As3et
	 */
	public function as3et($as3et = NULL)
	{
		// If an instance is provided, set it.
		if ($as3et !== NULL)
		{
			$this->_as3et = $as3et;
		}

		// If no instance exists, create one.
		if ($this->_as3et === NULL)
		{
			$this->_as3et = new As3et;
		}

		return $this->_as3et;
	}

	/**
	 * Builds a list of the asset files for uploading, based on the CFS
	 *
	 * @return array
	 */
	public function get_asset_files()
	{
		// Compile the blacklist
		$blacklist = Kohana::$config->load('as3et.blacklist');
		$patterns = array();
		foreach ($blacklist as $pattern => $enabled)
		{
			if ( ! $enabled)
				continue;

			// Escape the filter string
			$pattern = preg_quote($pattern, '#');

			// Replace ? wildcards with .
			$pattern = str_replace('\?', '.', $pattern);

			// Replace * wildcards with .*
			$pattern = str_replace('\*', '.*', $pattern);

			// Prefix with assets/ and add to the array
			$patterns[] = 'assets/'.$pattern;
		}

		// Build the full regular expression
		$blacklist = $patterns ? '#^('.implode('|', $patterns).')$#' : NULL;

		// Load the list of files
		return $this->filter_asset_files(Kohana::list_files('assets'), $blacklist);
	}

	/**
	 * Recursively filter a list of asset files against a blacklist, and return
	 * a flattened array with the 'assets/' prefix removed from the front of each
	 * array key (so that the filenames are ready to be set as the Amazon S3 paths).
	 *
	 * @param array $files	A nested array of files, same format as [Kohana::list_files]
	 * @param string $blacklist A regular expression that matches the files and paths to blacklist
	 * @return array
	 */
	protected function filter_asset_files($files, $blacklist)
	{
		$filtered = array();

		foreach ($files as $path => $value)
		{
			// Filter paths matching the blacklist
			if ($blacklist AND preg_match($blacklist, $path))
			{
				continue;
			}

			// Recurse into arrays, or add the files to the list
			if (is_array($value))
			{
				$filtered += $this->filter_asset_files($value, $blacklist);
			}
			else
			{
				// Strip the 'assets/' prefix from the path key
				$path = substr($path, 7);
				// Replace windows directory separators
				$path = str_replace('\\', '/', $path);
				$filtered[$path] = $value;
			}
		}

		return $filtered;

	}

	/**
	 * Gets the current git SHA for version tracking
	 * @return string
	 */
	public function current_git_sha()
	{
		if ($this->_git_sha === NULL)
		{
			exec('git rev-parse --short HEAD 2>&1', $output, $error);
			if ($error)
				throw new Exception("git returned status code $error".PHP_EOL."---".implode(PHP_EOL, $output));

			$this->_git_sha = trim(`git rev-parse --short HEAD`, "\r\n");
		}

		return $this->_git_sha;
	}

	/**
	 * Upload a file to AmazonS3, prefixing the path with the current git SHA
	 * for versioning and setting headers based on the as3et configuration.
	 *
	 * @param string $file		 Asset path to set on S3
	 * @param string $disk_path  Absolute path to the file on disk to upload
	 */
	public function upload_file($file, $disk_path)
	{
		$config = Kohana::$config->load('as3et');

		if ( ! $mime = File::mime_by_ext(strtolower(pathinfo($disk_path, PATHINFO_EXTENSION))))
		{
			$mime = $config->get('default_mime_type');
		}

		$result = $this->s3()
			->create_object(
				$config['s3']['bucket'],
				$this->current_git_sha().'/'.$file,
				array(
					'contentType' => $mime,
					'fileUpload' => $disk_path,
					'acl' => AmazonS3::ACL_PUBLIC,
					'headers' => $config->get('asset_headers', array()),
				));
	}

}