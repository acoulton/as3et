<?php defined('SYSPATH') OR die('No direct script access.');

class ACoulton_Minion_Task_As3et_Deploy extends Minion_Task
{
	protected $_s3 = NULL;
	
	public function execute(array $config)
	{
		// Get the current git sha, and write to disk
		// List all 'asset' files
		// Upload all asset files to S3
		// 
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
				$filtered[$path] = $value;
			}
		}
		
		return $filtered;

	}

}