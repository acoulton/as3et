<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Default As3et configuration file - override at application level as required.
 */
return array(
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
	'blacklist' => array(
	),
	'asset_headers' => array(
	),
	'default_mime_type' => 'application/octet-stream',
	'revision_file' => APPPATH.'config/as3et_current_revision.php',
);