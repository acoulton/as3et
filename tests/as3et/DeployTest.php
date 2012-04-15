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

	protected static $old_path = NULL;

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

		// Set Kohana modules
		self::$old_modules = Kohana::modules();

		$new_modules = self::$old_modules+array(
			'test_as3et' => self::test_data_path()
		);
		Kohana::modules($new_modules);

		// Set environment path (for mocking git)
		self::$old_path = $_SERVER['PATH'];
		putenv('PATH='.self::test_data_path().':'.$_SERVER['PATH']);
		putenv('CLI_ASSERT_FILE='.self::test_data_path().'/git_assert_file');

	}

	/**
	 * Restores the module list and path
	 */
	public static function teardownAfterClass()
	{
		parent::tearDownAfterClass();
		
		Kohana::modules(self::$old_modules);
		putenv('PATH='.self::$old_path);
	}

	/**
	 * Cleanup the temporary file used to assert git command line arguments
	 */
	protected function clear_assert_file()
	{
		$git_assert_file = self::test_data_path().'/git_assert_file';
		if (file_exists($git_assert_file))
		{
			unlink($git_assert_file);
		}
	}

	/**
	 * Clear the assert file
	 */
	public function setUp()
	{
		parent::setUp();
		$this->clear_assert_file();
	}

	/**
	 * Clear the assert file
	 */
	public function tearDown()
	{
		parent::tearDown();
		$this->clear_assert_file();
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

	/**
	 * Verify that the task can load the current HEAD sha reference
	 */
	public function test_should_get_current_git_revision()
	{
		$task = new Minion_Task_As3et_Deploy;
		$sha = $task->current_git_sha();

		$git_assert = trim(file_get_contents(self::test_data_path().'/git_assert_file'),"\r\n");		
		$this->assertEquals('rev-parse --short HEAD',$git_assert);
		$this->assertEquals('abcdefg', $sha);

	}

	/**
	 * Helper method to mock an S3 object expecting a call to create_object with
	 * given params.
	 * 
	 * @param string|PHPUnit_Framework_Constraint $bucket
	 * @param string|PHPUnit_Framework_Constraint $file
	 * @param array|PHPUnit_Framework_Constraint $options
	 * @param boolean $success
	 * @return AmazonS3
	 */
	protected function _mock_s3_create_object($bucket, $file, $options, $success)
	{
		$s3 = $this->getMock('AmazonS3', array(), array(), '', FALSE);
		$s3->expects($this->once())
			->method('create_object')
			->with($bucket, $file, $options)
			->will($this->returnValue(NULL));

		return $s3;
	}

	/**
	 * Data provider for test_should_upload_files_with_correct_mime_types
	 * @return array
	 */
	public function provider_should_upload_files_with_correct_mime_types()
	{
		$test_path = self::test_data_path();
		return array(
			array('css.tmp', $test_path.'/assets/css.tmp','application/octet-stream'),
			array('css/foo.css', $test_path.'/assets/css/foo.css','text/css')
		);
	}

	/**
	 * Verifies that an appropriate mime-type header is sent with the files
	 *
	 * @dataProvider provider_should_upload_files_with_correct_mime_types
	 * @param string $file  Relative asset filename
	 * @param string $path  Path to the file on disk
	 * @param string $mime  Expected mime-type
	 */
	public function test_should_upload_files_with_correct_mime_types($file, $path, $mime)
	{

		$task = new Minion_Task_As3et_Deploy;
		$task->s3($this->_mock_s3_create_object(
				$this->anything(),
				$this->anything(),
				new Constraint_ArrayKey_HasValue('contentType', $mime),
				TRUE));

		$task->upload_file($file, $path);
	}

	/**
	 * Verifies that the S3 path is set to the git SHA followed by the asset
	 * file path, and that the bucket is set correctly.
	 */
	public function test_should_upload_to_git_versioned_path_in_bucket()
	{
		// Set config values
		$s3_config = Kohana::$config->load('as3et');
		$s3_config->set('s3', Arr::merge($s3_config->get('s3'), array(
			'bucket' => 'my-bucket',
		)));

		// Shunt the task class so we can force the git SHA
		$task = $this->getMock('Minion_Task_As3et_Deploy', array('current_git_sha'));
		$task->expects($this->once())
				->method('current_git_sha')
				->will($this->returnValue('abcdefg'));

		// Configure an asset
		$asset = array(
			'file' => 'css/foo.css',
			'path' => self::test_data_path().'/assets/css/foo.css'
		);

		// Mock the s3 class
		$task->s3($this->_mock_s3_create_object(
				'my-bucket',
				'abcdefg/'.$asset['file'],
				new Constraint_ArrayKey_HasValue('fileUpload', $asset['path']),
				TRUE));

		$task->upload_file($asset['file'], $asset['path']);

	}

	/**
	 * Verifies that the asset headers specified in the as3et config are sent
	 * with the files.
	 */
	public function test_should_upload_files_with_configurable_headers()
	{
		// Set config values
		$headers = array('max-age'=>'foo','x-up-with'=>'as3et');
		Kohana::$config->load('as3et')->set('asset_headers', $headers);

		// Mock S3
		$task = new Minion_Task_As3et_Deploy;
		$task->s3($this->_mock_s3_create_object(
				$this->anything(),
				$this->anything(),
				new Constraint_ArrayKey_HasValue('headers', $headers),
				TRUE));

		$task->upload_file('css/foo.css', self::test_data_path().'/assets/css/foo.css');
	}

	public function test_should_store_deployed_revision_for_as3et_prefixing()
	{

	}

	public function test_should_throw_exception_on_failed_deploy()
	{
		
	}

}



/**
 * Constraint that asserts that a key in the array it is evaluated for has the
 * given value.
 *
 * The array key and value is passed in the constructor.
 *
 */
class Constraint_ArrayKey_HasValue extends PHPUnit_Framework_Constraint
{
    /**
     * @var integer|string
     */
    protected $key;

    /**
     * @var mixed
     */
	protected $value;

    /**
     * @param integer|string $key
     * @param mixed $value
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
		$this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    protected function matches($other)
    {
		return array_key_exists($this->key, $other) AND ($other[$this->key] === $this->value);
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'key ' . PHPUnit_Util_Type::export($this->key) . ' equals ' . PHPUnit_Util_Type::export($this->value);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param  mixed $other Evaluated value or object.
     * @return string
     */
    protected function failureDescription($other)
    {
        return 'an array ' . $this->toString().' (got '.PHPUnit_Util_Type::export($other).')';
    }
}