<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'mode' => NULL,	
	's3' => array(
		'region' => NULL,
		'bucket' => NULL,
		'key'	 => 'YOUR_AWS_KEY',
		'secret' => 'YOUR_AWS_SECRET',
	),
	'blacklist' => array(
	),
	'asset_headers' => array(		
	),
	'revision_file' => APPPATH.'config/as3et_current_revision.php',
);