<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Base class for all asset handlers
 * 
 * @package    As3et
 * @category   Asset Handlers
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
abstract class ACoulton_As3et_Collection
{
	/**
	 * Internal storage for the As3et instance
	 * @type As3et
	 */
	protected $_as3et = NULL;
	
	abstract public function tag($attributes = array());

	/**
	 * Constructs a new instance, with a reference to the As3et class
	 * @param As3et $as3et
	 */
	public function __construct(As3et $as3et)
	{
		$this->_as3et = $as3et;
	}

	/**
	 * Returns the As3et instance for this asset
	 * 
	 * @return As3et
	 */
	public function as3et()
	{
		return $this->_as3et;
	}
}