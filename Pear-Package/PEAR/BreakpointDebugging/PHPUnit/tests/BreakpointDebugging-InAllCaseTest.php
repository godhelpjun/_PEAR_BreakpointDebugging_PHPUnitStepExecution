<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_InAllCase as BA;
use \BreakpointDebugging_PHPUnit as BU;
use \BreakpointDebugging_InAllCaseTest as T;

/**
 * Test class for BreakpointDebugging_InAllCase.
 * Generated by PHPUnit on 2012-09-30 at 16:24:30.
 */
class BreakpointDebugging_InAllCaseTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCase
{
    static $testAutoload;
    private static $_isRegister = array ();

    const TEST_CONST = 'The test constant.';

    static function setUpBeforeClass()
    {
        BU::loadClass('\BreakpointDebugging_LockByFileExisting');
        parent::setUpBeforeClass();
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testExceptionHandler()
    {
        $pPrevious = new \Exception('Previous exception.', E_USER_WARNING);
        $pException = new \Exception('Exception.', E_USER_WARNING, $pPrevious);
        BU::ignoreBreakpoint();
        ob_start();
        BA::handleException($pException);
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testClearRecursiveArrayElement()
    {
        // It sets "count($_SERVER)" to "B::$_maxLogElementNumber" because it is purpose to execute without array slicing.
        $maxLogElementNumber = &B::refMaxLogElementNumber();
        $tmpMaxLogElementNumber = $maxLogElementNumber;
        $maxLogElementNumber = count($_SERVER);

        $testArray['element'] = 'String.';
        $testArray['recursive'] = &$testArray;
        $testArray['component']['recursive'] = &$testArray;
        $testArray['component']['element'] = 'String.';
        $testArray['component']['recursive2'] = &$testArray['component'];
        $testArray['component']['component']['recursive'] = &$testArray['component'];
        $testArray['component']['component']['GLOBALS'] = $GLOBALS;

        foreach ($GLOBALS as $key => $value) {
            if ($key === 'GLOBALS') {
                $expectedGlobals[$key] = B::GLOBALS_USING;
                continue;
            }
            $expectedGlobals[$key] = $value;
        }

        $expectedArray['element'] = 'String.';
        $expectedArray['recursive'] = B::RECURSIVE_ARRAY;
        $expectedArray['component']['recursive'] = B::RECURSIVE_ARRAY;
        $expectedArray['component']['element'] = 'String.';
        $expectedArray['component']['recursive2'] = B::RECURSIVE_ARRAY;
        $expectedArray['component']['component']['recursive'] = B::RECURSIVE_ARRAY;

        $resultArray = BA::clearRecursiveArrayElement($testArray);
        parent::assertTrue($expectedArray['element'] === $resultArray['element']);
        parent::assertTrue($expectedArray['recursive'] === $resultArray['recursive']);
        parent::assertTrue($expectedArray['component']['recursive'] === $resultArray['component']['recursive']);
        parent::assertTrue($expectedArray['component']['element'] === $resultArray['component']['element']);
        parent::assertTrue($expectedArray['component']['recursive2'] === $resultArray['component']['recursive2']);
        parent::assertTrue($expectedArray['component']['component']['recursive'] === $resultArray['component']['component']['recursive']);

        // "\BreakpointDebugging_InAllCase::clearRecursiveArrayElement()" copies array type element if has it, then returns array which has a copied array type element part.
        // Therefore, compares per array element because array type element reference differs.
        $compareArray = function($expectedGlobals, $resultElement) {
            foreach ($expectedGlobals as $key => $expectedGlobal) {
                if (is_array($expectedGlobal)) {
                    $tmp = array_diff($expectedGlobal, $resultElement[$key]);
                    T::assertTrue(empty($tmp));
                    $tmp = array_diff($resultElement[$key], $expectedGlobal);
                    T::assertTrue(empty($tmp));
                    continue;
                }
                T::assertTrue($expectedGlobal === $resultElement[$key]);
            }
            T::assertTrue(is_array($GLOBALS['GLOBALS']));
        };

        $resultElement = $resultArray['component']['component']['GLOBALS'];
        $compareArray($expectedGlobals, $resultElement);

        $resultArray = BA::clearRecursiveArrayElement($GLOBALS);
        $compareArray($expectedGlobals, $resultArray);

        unset($testArray);
        $testArray = array ($GLOBALS);
        $resultArray = BA::clearRecursiveArrayElement($testArray);
        $resultElement = $resultArray[0];
        $compareArray($expectedGlobals, $resultElement);

        // Restores "B::$_maxLogElementNumber".
        $maxLogElementNumber = $tmpMaxLogElementNumber;
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    function testCheckDevelopmentSecurity_A()
    {
        ob_start();

        if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1') { // If local.
            parent::assertTrue(BA::checkDevelopmentSecurity('LOCAL'));
        } else {
            parent::assertTrue(!BA::checkDevelopmentSecurity('LOCAL'));
        }

        BU::$exeMode = 0; // Change to local debug mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(!BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(!BA::checkDevelopmentSecurity('RELEASE'));

        BU::$exeMode = B::REMOTE; // Change to remote debug mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(!BA::checkDevelopmentSecurity('RELEASE'));
        $_SERVER['HTTPS'] = 'off';
        parent::assertTrue(!BA::checkDevelopmentSecurity());
        unset($_SERVER['HTTPS']);
        parent::assertTrue(!BA::checkDevelopmentSecurity());
        $_SERVER['REMOTE_ADDR'] = 'DUMMY';
        parent::assertTrue(!BA::checkDevelopmentSecurity());
        $_SERVER['REMOTE_ADDR'] = B::getDeveloperIP();
        $_SERVER['HTTPS'] = 'on';
        parent::assertTrue(BA::checkDevelopmentSecurity());

        BU::$exeMode = B::RELEASE; // Change to local release mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(!BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(BA::checkDevelopmentSecurity('RELEASE'));

        BU::$exeMode = B::REMOTE | B::RELEASE; // Change to remote release mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(BA::checkDevelopmentSecurity('RELEASE'));

        BU::$exeMode = B::UNIT_TEST; // Change to local debug unit test mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(!BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(!BA::checkDevelopmentSecurity('RELEASE'));

        BU::$exeMode = B::UNIT_TEST | B::RELEASE; // Change to local release unit test mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(!BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(BA::checkDevelopmentSecurity('RELEASE'));

        BU::$exeMode = B::REMOTE | B::UNIT_TEST; // Change to remote debug unit test mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(!BA::checkDevelopmentSecurity('RELEASE'));

        BU::$exeMode = B::REMOTE | B::RELEASE | B::UNIT_TEST; // Change to remote release unit test mode.
        parent::assertTrue(BA::checkDevelopmentSecurity());
        parent::assertTrue(BA::checkDevelopmentSecurity('REMOTE'));
        parent::assertTrue(BA::checkDevelopmentSecurity('RELEASE'));
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    function test__initialize()
    {
        BA::initialize();
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    function testRefAndGetStatic()
    {
        $developerIP = &BA::refDeveloperIP();
        parent::assertTrue($developerIP !== '111.222.333.444');
        $developerIP = '111.222.333.444';
        parent::assertTrue($developerIP === '111.222.333.444');
        parent::assertTrue(BA::getDeveloperIP() === '111.222.333.444');
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    function testSetAndGetXebugExists()
    {
        BA::setXebugExists(false);
        parent::assertTrue(BA::getXebugExists() === false);
        BA::setXebugExists(true);
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testIniCheck()
    {
        try {
            BA::iniCheck('arg1', 'arg2');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            BU::assertExceptionMessage($e, 'Missing argument 3 for BreakpointDebugging_InAllCase::iniCheck(), called in');
        }
        ob_start();
        parent::assertTrue(BA::iniCheck('arg1', 'arg2', 'arg3', 'DUMMY') === false);
        parent::assertTrue(BA::iniCheck(1, 'arg2', 'arg3') === false);
        parent::assertTrue(BA::iniCheck('arg1', array (1), 'arg3') === false);
        parent::assertTrue(BA::iniCheck('arg1', 1, 'arg3') === false);
        parent::assertTrue(BA::iniCheck('arg1', 'arg2', 1) === false);
        parent::assertTrue(BA::iniCheck('safe_mode', '', 'DUMMY') === true);
        parent::assertTrue(BA::iniCheck('safe_mode', 'DiffVal', 'Error message 1.') === false);
        parent::assertTrue(BA::iniCheck('safe_mode', array ('DiffVal1', 'DiffVal2', 'DiffVal3'), 'DUMMY') === true);
        ob_end_clean();
    }

    /**
     * @covers \BreakpointDebugging<extended>
     */
    public function testIniSet()
    {
        try {
            B::iniSet('arg1');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            BU::assertExceptionMessage($e, 'Missing argument 2 for BreakpointDebugging_InAllCase::iniSet(), called in');
        }
        ob_start();
        parent::assertTrue(B::iniSet('arg1', 'arg2', 'arg3') === false);
        parent::assertTrue(B::iniSet('error_log', 'DUMMY') === false);
        parent::assertTrue(B::iniSet(1, 'DUMMY') === false);
        parent::assertTrue(B::iniSet('DUMMY', 1) === false);
        parent::assertTrue(B::iniSet('doc_root', 'dummy') === false); // Cannot set 'doc_root'.
        parent::assertTrue(B::iniSet('not_exist', 'true') === false); // 'not_exist' does not exist.
        ob_end_clean();

        parent::assertTrue(ini_get('default_charset') === 'utf8');
        B::iniSet('default_charset', 'sjis');
        parent::assertTrue(ini_get('default_charset') === 'sjis');
        B::iniSet('default_charset', 'sjis');
        parent::assertTrue(ini_get('default_charset') === 'sjis');

        B::iniSet('default_charset', 'utf8');
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testRegisterNotFixedLocation()
    {
        BA::registerNotFixedLocation(self::$_isRegister[__METHOD__]);
        BA::registerNotFixedLocation(self::$_isRegister[__METHOD__]);
        $notFixedLocations = BA::getNotFixedLocations();
        $notFixedLocation = $notFixedLocations[count($notFixedLocations) - 1];
        parent::assertTrue($notFixedLocation['function'] === 'testRegisterNotFixedLocation');
        parent::assertTrue($notFixedLocation['class'] === 'BreakpointDebugging_InAllCaseTest');
        parent::assertTrue(!array_key_exists('file', $notFixedLocation));
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testAddValuesToTrace()
    {
        $testString = 'The test character string.';
        $values = array ('TEST_CONST' => BreakpointDebugging_InAllCaseTest::TEST_CONST, '$testString' => $testString);
        BA::addValuesToTrace($values);
        $line = __LINE__ - 1;
        $valuesToTraces = BA::getValuesToTrace();
        $valuesToTrace = $valuesToTraces[__FILE__][$line];
        parent::assertTrue($valuesToTrace['function'] === 'testAddValuesToTrace');
        parent::assertTrue($valuesToTrace['class'] === 'BreakpointDebugging_InAllCaseTest');
        parent::assertTrue(!array_key_exists('file', $valuesToTrace));
        parent::assertTrue($valuesToTrace['values'] === $values);
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testConvertMbString_A()
    {
        // SJIS
        BA::convertMbString("\x95\xB6\x8E\x9A ");
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testConvertMbString_B()
    {
        // UTF-8
        BA::convertMbString("\xE6\x96\x87\xE5\xAD\x97 ");
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_InAllCase FUNCTION=convertMbString ID=101.
     */
    public function testConvertMbString_C()
    {
        // SJIS + UTF-8
        BA::convertMbString("\x95\xB6\x8E\x9A \xE6\x96\x87\xE5\xAD\x97 ");
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testMkdir_A()
    {
        $testDirName = BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'TestMkDir';
        if (is_dir($testDirName)) {
            B::rmdir(array ($testDirName));
        }
        BA::mkdir(array ($testDirName, 0700));
        parent::assertTrue(is_dir($testDirName));
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            return;
        }
        clearstatcache();
        parent::assertTrue(substr(sprintf('%o', fileperms($testDirName)), -4) === '0700');
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testMkdir_B()
    {
        $testDirName = BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'TestMkDir';
        if (is_dir($testDirName)) {
            B::rmdir(array ($testDirName));
        }
        BA::mkdir(array ($testDirName));
        parent::assertTrue(is_dir($testDirName));

        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            return;
        }
        clearstatcache();
        parent::assertTrue(substr(sprintf('%o', fileperms($testDirName)), -4) === '0700');
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage mkdir()
     */
    public function testMkdir_C()
    {
        $testDirName = BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'TestMkDir';
        BA::mkdir(array ($testDirName), 2);
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testFopen_A()
    {
        $testFileName = BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'TestFopen.txt';
        if (is_file($testFileName)) {
            BA::unlink(array ($testFileName));
        }
        $pFile = BA::fopen(array ($testFileName, 'w+b'), 0600);
        try {
            BA::fopen(array ($testFileName, 'x+b'), 0600, 2);
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            BU::assertExceptionMessage($e, 'failed to open stream:');
        }
        fclose($pFile);
        parent::assertTrue(is_file($testFileName));
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            return;
        }
        clearstatcache();
        parent::assertTrue(substr(sprintf('%o', fileperms($testFileName)), -4) === '0600');
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testCompressThenDecompressIntArray()
    {
        $intArray = array ();
        for ($count = 0; $count <= 400; $count++) {
            $intArray[] = $count;
        }
        $pFile = B::fopen(array (BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'test.bin', 'w+b'));
        fwrite($pFile, BA::compressIntArray($intArray));
        fwrite($pFile, BA::compressIntArray($intArray));
        fflush($pFile);
        rewind($pFile);
        while ($intResultArray = BA::decompressIntArray(fgets($pFile))) {
            parent::assertTrue($intArray === $intResultArray);
        }
        fclose($pFile);
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testAutoload()
    {
        if (T::$testAutoload === 2) { // In case of accessing to static member.
            ob_start();
            \NativeClass::publicStaticFunction();
        }

        if (T::$testAutoload === 3) { // In case of creating new instance.
            new \NativeClass();
        }
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testErrorHandler()
    {
        BU::ignoreBreakpoint();
        ob_start();
        BA::handleError(E_USER_WARNING, 'Error test.');
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_InAllCaseTest FUNCTION=testInternalException ID=1.
     */
    public function testInternalException()
    {
        BU::ignoreBreakpoint();
        BA::internalException('Tests "internalException()".', 1);
    }

    /**
     * @covers \BreakpointDebugging_InAllCase<extended>
     */
    public function testShutdown()
    {
        // Emulates this page shutdown.
        BA::shutdown();
    }

}

T::$testAutoload = 1;
if (T::$testAutoload === 1) { // The case which extends base class.
    class AutoloadTest extends \AutoloadTestBase
    {

    }

}
