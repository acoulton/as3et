<?php

/**
 * Exception thrown when a deployment fails
 *
 * @package    As3et
 * @category   Exceptions
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
class As3et_Exception_DeployFailed extends Exception
{
	/**
	 * Formats the Amazon response for display
	 * 
	 * @param string $message  Error string
	 * @param CFArray $response The response object from AWS SDK
	 */
	public function __construct($message, CFArray $response) {
		$message.=PHP_EOL.'------'.PHP_EOL.$response->to_json();
		parent::__construct($message);
	}
	
}