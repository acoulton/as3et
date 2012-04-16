<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'mode' => NULL,	
	's3' => array(
		'region' => NULL,
		'bucket' => NULL,
		'key'	 => Arr::get($_SERVER,'AWS_KEY','YOUR_AWS_KEY'),
		'secret' => Arr::get($_SERVER,'AWS_SECRET','YOUR_AWS_SECRET'),
	),
	'blacklist' => array(
	),
	'asset_headers' => array(		
	),
	'default_mime_type' => 'application/octet-stream',
	'revision_file' => APPPATH.'config/as3et_current_revision.php',
);