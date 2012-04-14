<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class for working with CSS assets
 *
 * @package    As3et
 * @category   Asset Handlers
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
class ACoulton_As3et_CSS extends As3et_Collection
{
	/**
	 * The filename
	 * @var string
	 */
	protected $_file = NULL;


	/**
	 * Creates a new As3et_JS instance
	 *
	 * @param As3et $as3et  An As3et instance
	 * @param string $file  Relative path to the javascript file
	 */
	public function __construct(As3et $as3et, $file)
	{
		parent::__construct($as3et);
		$this->_file = $file;
	}

	/**
	 * Gets the HTML script tag for the given file
	 *
	 * @param array $attributes  HTML attributes to render on the tag
	 * @return string  HTML <script> tag
	 */
	public function tag($attributes = array())
	{
		$url = $this->as3et()->url($this->_file);
		return HTML::style($url, $attributes);
	}

}