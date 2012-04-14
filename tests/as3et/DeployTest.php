<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests [Minion_Task_As3et_Deploy]
 *
 * @group as3et
 * @group as3et.deploy
 *
 * @package    As3et
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Ingenerator
 * @license    http://kohanaframework.org/license
 */
class As3et_DeployTest extends Unittest_TestCase
{
	protected static $old_modules = array();

	/**
	 * Return the location on disk of the test_data module files
	 *
	 * @return string
	 */
	protected static function test_data_path()
	{
		return realpath(dirname(__FILE__).'/../test_data/');
	}
	
	/**
	 * Add a module containing test asset files
	 */
	public static function setupBeforeClass()
	{
		parent::setUpBeforeClass();
		
		self::$old_modules = Kohana::modules();

		$new_modules = self::$old_modules+array(
			'test_as3et' => self::test_data_path()
		);
		Kohana::modules($new_modules);

	}

	/**
	 * Restores the module list
	 */
	public static function teardownAfterClass()
	{
		parent::tearDownAfterClass();
		
		Kohana::modules(self::$old_modules);
	}

	/**
	 * Verifies that we can successfully inject an S3 instance into the task for
	 * mocking during testing.
	 */
	public function test_should_support_s3_injection()
	{
		$task = new Minion_Task_As3et_Deploy;

		$this->assertInstanceOf('AmazonS3', $task->s3(), 'Creates an S3 object by default');

		$s3 = $this->getMock('AmazonS3', array(), array(), '', FALSE);
		$task->s3($s3);
		$s3_returned = $task->s3();

		$this->assertSame($s3, $s3_returned);
	}

	/**
	 * Verifies that the Deploy task successfully finds asset files from the CFS
	 * for uploading.
	 */
	public function test_should_find_assets_to_upload()
	{
		$test_path = self::test_data_path();
		
		$task = new Minion_Task_As3et_Deploy;
		$files = $task->get_asset_files();

		$this->assertEquals($test_path.'/assets/foo.js', $files['foo.js']);
		$this->assertEquals($test_path.'/assets/css/foo.css', $files['css/foo.css']);
	}

	/**
	 * Provider for test_should_respect_blacklist_of_paths_to_ignore
	 * @return array
	 */
	public function provider_should_respect_blacklist_of_paths_to_ignore()
	{
		return array(
			array(array('ignore/*' => TRUE), 'ignore/this.js', 'ignore.js'),
			array(array('ignore.js' => TRUE), 'ignore.js', 'ignore/this.js'),
			array(array('*.tmp' => TRUE), 'css.tmp', 'ignore.js'),
			array(array('ignore/*' => FALSE), NULL, 'ignore/this.js')
		);
	}

	/**
	 * Verifies that file paths can be blacklisted and are not returned in the
	 * list of files to upload.
	 *
	 * @dataProvider provider_should_respect_blacklist_of_paths_to_ignore
	 *
	 * @param array $blacklist	   Config for as3et.blacklist
	 * @param type $expect_hidden  A file that exists and we expect to be hidden
	 * @param type $expect_found   A file that exists and we expect to be found
	 */
	public function test_should_respect_blacklist_of_paths_to_ignore($blacklist, $expect_hidden, $expect_found)
	{
		$test_path = self::test_data_path();
		Kohana::$config->load('as3et')->set('blacklist', $blacklist);		

		$task = new Minion_Task_As3et_Deploy;
		$files = $task->get_asset_files();

		if ($expect_hidden)
		{
			$this->assertArrayNotHasKey($expect_hidden, $files);
		}

		if ($expect_found)
		{
			$this->assertArrayHasKey($expect_found, $files);
		}
	}

	public function test_should_upload_files_with_correct_mime_types()
	{
		
	}

	public function test_should_prefix_paths_with_current_git_revision()
	{

	}

	public function test_should_upload_files_with_configurable_headers()
	{

	}

	public function test_should_store_deployed_revision_for_as3et_prefixing()
	{

	}

	public function test_should_throw_exception_on_failed_deploy()
	{
		
	}

}
