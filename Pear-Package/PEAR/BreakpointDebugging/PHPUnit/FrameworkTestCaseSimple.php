<?php

/**
 * Debugs unit test files continuously by IDE.
 *
 * We can use for unit tests of this package and "PHPUnit" package because this class is instance and this class does not use "PHPUnit" package.
 * Also, we can use instead of "*.phpt".
 * See the "BreakpointDebugging_PHPUnit.php" file-level document for usage.
 *
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2014, Hidenori Wasa
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category PHP
 * @package  BreakpointDebugging_PHPUnit
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging_PHPUnit
 */
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;
use \BreakpointDebugging_PHPUnit_StaticVariableStorage as BSS;
use BreakpointDebugging_PHPUnit_FrameworkTestCase as BTC;

/**
 * Debugs unit test files continuously by IDE.
 *
 * @category PHP
 * @package  BreakpointDebugging_PHPUnit
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging_PHPUnit
 */
class BreakpointDebugging_PHPUnit_FrameworkTestCaseSimple
{
    /**
     * @var object "\BreakpointDebugging_PHPUnit" instance.
     */
    private static $_phpUnit;

    /**
     * This class method is called first per "*TestSimple.php" file.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {

    }

    /**
     * This class method is called lastly per "*TestSimple.php" file.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {

    }

    /**
     * Sets the "\BreakpointDebugging_PHPUnit" object.
     *
     * @param object $phpUnit "\BreakpointDebugging_PHPUnit".
     */
    static function setPHPUnit($phpUnit)
    {
        B::limitAccess('BreakpointDebugging_PHPUnit.php', true);

        self::$_phpUnit = $phpUnit;
    }

    /**
     * Checks the autoload functions.
     *
     * @param string $testClassName  The test class name.
     * @param string $testMethodName The test class method name.
     *
     * @return void
     */
    static function checkAutoloadFunctions($testClassName, $testMethodName = null)
    {
        B::limitAccess(
            array ('BreakpointDebugging/PHPUnit/FrameworkTestCase.php',
            'BreakpointDebugging/PHPUnit/FrameworkTestCaseSimple.php',
            )
            , true);

        // Checks the autoload functions.
        $autoloadFunctions = spl_autoload_functions();
        if (is_array($autoloadFunctions[0]) //
            && $autoloadFunctions[0][0] === 'BreakpointDebugging_PHPUnit_StaticVariableStorage' //
            && $autoloadFunctions[0][1] === 'loadClass' //
        ) {
            return;
        }
        if (is_object($autoloadFunctions[0][0])) {
            $className = get_class($autoloadFunctions[0][0]);
        } else {
            $className = $autoloadFunctions[0][0];
        }
        $autoloadFunction = $className . '::' . $autoloadFunctions[0][1];

        $message = '<b>You must not register autoload function "' . $autoloadFunction . '" at top of stack by "spl_autoload_register()" in all code.' . PHP_EOL;
        if ($testMethodName) {
            $message .= 'Inside of "' . $testClassName . '::' . $testMethodName . '()".' . PHP_EOL;
        } else {
            $message .= 'In "bootstrap file", "file of (class ' . $testClassName . ') which is executed at autoload" or "' . $testClassName . '::setUpBeforeClass()"' . '.' . PHP_EOL;
        }
        $message .= '</b>Because it cannot store static status.';
        B::windowHtmlAddition(BU::getUnitTestWindowName(self::$_phpUnit), 'pre', 0, $message);
        exit;
    }

    /**
     * Base of "setUp()" class method.
     *
     * @param object $phpUnit "\BreakpointDebugging_PHPUnit" instance.
     */
    static function setUpBase($phpUnit)
    {
        B::limitAccess(
            array ('BreakpointDebugging/PHPUnit/FrameworkTestCase.php',
            'BreakpointDebugging/PHPUnit/FrameworkTestCaseSimple.php',
            )
            , true);

        // Unlinks synchronization files.
        $lockFilePaths = array (
            'LockByFileExistingOfInternal.txt',
            'LockByFileExisting.txt',
        );
        $workDir = B::getStatic('$_workDir');
        foreach ($lockFilePaths as $lockFilePath) {
            $lockFilePath = realpath($workDir . '/' . $lockFilePath);
            if (is_file($lockFilePath)) {
                B::unlink(array ($lockFilePath));
            }
            B::assert(!is_file($lockFilePath));
        }
        // Stores the output buffering level.
        $obLevel = &$phpUnit->refObLevel();
        $obLevel = ob_get_level();
    }

    /**
     * This method is called before a test class method is executed.
     * Sets up initializing which is needed at least in unit test.
     *
     * @return void
     */
    protected function setUp()
    {
        self::setUpBase(self::$_phpUnit);
    }

    /**
     * Base of "tearDown()" class method.
     *
     * @param object $phpUnit "\BreakpointDebugging_PHPUnit" instance.
     */
    static function tearDownBase($phpUnit)
    {
        B::limitAccess(
            array ('BreakpointDebugging/PHPUnit/FrameworkTestCase.php',
            'BreakpointDebugging/PHPUnit/FrameworkTestCaseSimple.php',
            )
            , true);

        // Restores the output buffering level.
        while (ob_get_level() > $phpUnit->refObLevel()) {
            ob_end_clean();
        }
        B::assert(ob_get_level() === $phpUnit->refObLevel());
    }

    /**
     * This method is called after a test class method is executed.
     * Cleans up environment which is needed at least in unit test.
     *
     * @return void
     */
    protected function tearDown()
    {
        self::tearDownBase(self::$_phpUnit);
    }

    /**
     * Runs class methods of this unit test instance continuously.
     *
     * @param string $testClassName The test class name.
     *
     * @return void
     */
    static function runTestMethods($testClassName)
    {
        B::limitAccess('BreakpointDebugging_PHPUnit.php', true);

        try {
            $classReflection = new \ReflectionClass($testClassName);
            $methodReflections = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);
            // Invokes "setUpBeforeClass()" class method.
            $testClassName::setUpBeforeClass();

            self::$_phpUnit->displayProgress(300);
            // Checks the autoload functions.
            self::checkAutoloadFunctions($testClassName);
            // Checks definition, deletion and change violation of global variables and global variable references in "setUp()".
            BSS::checkGlobals(BSS::refGlobalRefs(), BSS::refGlobals(), true);
            // Checks the change violation of static properties and static property child element references.
            BSS::checkProperties(BSS::refStaticProperties2(), false);
        } catch (Exception $e) {
            B::exitForError($e); // Displays error call stack information.
        }
        foreach ($methodReflections as $methodReflection) {
            try {
                if (strpos($methodReflection->name, 'test') !== 0) {
                    continue;
                }
                self::$_phpUnit->displayProgress(5);
                // Start output buffering.
                ob_start();
                // Creates unit test instance.
                $pTestInstance = new $testClassName();
                // Clean up stat cache.
                clearstatcache();
                // Restores global variables.
                BSS::restoreGlobals(BSS::refGlobalRefs(), BSS::refGlobals());
                // Restores static properties.
                BSS::restoreProperties(BSS::refStaticProperties2());

                // Invokes "setUp()" class method.
                $pTestInstance->setUp();

                // Checks the autoload functions.
                self::checkAutoloadFunctions($testClassName, 'setUp');
            } catch (Exception $e) {
                B::exitForError($e); // Displays error call stack information.
            }

            // Invokes "test*()" class method.
            $methodReflection->invoke($pTestInstance);

            try {
                // Checks the autoload functions.
                self::checkAutoloadFunctions($testClassName, $methodReflection->name);

                // Invokes "tearDown()" class method.
                $pTestInstance->tearDown();

                // Checks the autoload functions.
                self::checkAutoloadFunctions($testClassName, 'tearDown');
                // Deletes unit test instance.
                $pTestInstance = null;
                // Stop output buffering.
                ob_end_clean();
                // Displays a completed test.
                echo '.';
            } catch (Exception $e) {
                B::exitForError($e); // Displays error call stack information.
            }
        }
        try {
            // Invokes "tearDownAfterClass()" class method.
            $testClassName::tearDownAfterClass();
        } catch (Exception $e) {
            B::exitForError($e); // Displays error call stack information.
        }
    }

    /**
     * Displays error call stack information when assertion is failed.
     *
     * @param bool   $condition Conditional expression.
     * @param string $message   Error message.
     *
     * @return void
     */
    static function assertTrue($condition, $message = '')
    {
        B::assert(is_bool($condition));
        B::assert(is_string($message));

        if (!$condition) {
            B::exitForError($message); // Displays error call stack information.
        }
    }

    /**
     * Displays error call stack information when a test is failed.
     *
     * @param string $message The fail message.
     *
     * @return void
     */
    static function fail($message = '')
    {
        B::assert(is_string($message));

        B::exitForError($message); // Displays error call stack information.
    }

    /**
     * Marks the test as skipped in debug.
     *
     * @return void
     */
    static function markTestSkippedInDebug()
    {
        if (!(BU::$exeMode & B::RELEASE)) {
            return true;
        }
        return false;
    }

    /**
     * Marks the test as skipped in release.
     *
     * @return void
     */
    static function markTestSkippedInRelease()
    {
        if (BU::$exeMode & B::RELEASE) {
            return true;
        }
        return false;
    }

}