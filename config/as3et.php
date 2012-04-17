<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Default As3et configuration file - override at application level as required.
 */
return array(
	/*
	 * One of As3et::MODE_LOCAL or As3et::MODE_S3 - determines where to serve from
	 */
	'mode' => NULL,
	's3' => array(
		/*
		 * The region endpoint for serving assets
		 */
		'region' => NULL,

		/*
		 * The bucket name for your assets
		 */
		'bucket' => NULL,

		/*
		 * Your AWS key
		 */
		'key'	 => Arr::get($_SERVER,'AWS_KEY','YOUR_AWS_KEY'),

		/*
		 * Your AWS secret key
		 */
		'secret' => Arr::get($_SERVER,'AWS_SECRET','YOUR_AWS_SECRET'),

		/*
		 * Determines which Cerificate Authority file to use.
		 *  - A value of boolean FALSE will use the Certificate Authority file
		 *    available on the system.
		 *  - A value of boolean TRUE will use the Certificate Authority provided
		 *    by the SDK.
		 *  - Passing a file system path to a Certificate Authority file
		 *    (chmodded to 0755) will use that.
		 * Leave this set to FALSE if you’re not sure.
		 */
		'certificate_authority' => FALSE,
	),

	/*
	 * Array of file paths to blacklist and never upload to S3 - supports wildcards:
	 *
	 *     'less/*' => TRUE,
	 *     'Thumbs.db' => TRUE
	 */
	'blacklist' => array(
	),

	/*
	 * Headers to have Amazon send back with the assets when they're served -
	 * you could add a far-future expires header here as the path will always
	 * change when a new version is deployed.
	 */
	'asset_headers' => array(
	),

	/*
	 * MIME type to set for an asset when the extension is unrecognised
	 */
	'default_mime_type' => 'application/octet-stream',

	/*
	 * The path on disk where the current revision_file will be saved - this is
	 * the PHP file that returns the SHA of the last deployed revision.
	 */
	'revision_file' => APPPATH.'config/as3et_current_revision.php',
);