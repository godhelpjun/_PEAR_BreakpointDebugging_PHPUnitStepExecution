BreakpointDebugging_PHPUnit
========================================

The basic concept
-----------------

"PHPUnit"-package extention.

The features list
-----------------

* Executes unit test files continuously, and debugs with IDE.
* Creates code coverage report, then displays in browser.

Please, read the file level document block of `PEAR/BreakpointDebugging_PHPUnit.php`.

The dependences
---------------

* Requires "BreakpointDebugging" package.
* OS requires Linux or Windows.
* PHP version = 5.3.2-5.4.x
* Requires "Xdebug extension".
* Requires "Mozilla Firefox" web browser.

Change log
----------

* I added features which copies "BreakpointDebugging_ErrorLogFilesManager.php" and "BreakpointDebugging_PHPUnit_DisplayCodeCoverageReport.php" to current work directory.
* I decreased disk access at logging.
* I improved "BreakpointDebugging" package sample.

Notice
------

* Should not use draft.
* I am implementing "\BreakpointDebugging\NativeFunctions" class.
* Also, I have been testing with "BreakpointDebugging_PHPUnit" package.
* And, I have been testing "BreakpointDebugging_PHPUnit" package by "\BreakpointDebugging_PHPUnit::executeUnitTestSimple()".
