<?php
/**
 * phpdcd
 *
 * Copyright (c) 2009-2013, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    phpdcd
 * @subpackage Tests
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2009-2013 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since      File available since Release 1.0.0
 */

if (!defined('TEST_FILES_PATH')) {
    define(
      'TEST_FILES_PATH',
      dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR
    );
}

/**
 * Tests for the PHPDCD_Detector class.
 *
 * @package    phpdcd
 * @subpackage Tests
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2009-2013 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://github.com/sebastianbergmann/phpdcd/
 * @since      Class available since Release 1.0.0
 */
class PHPDCD_DetectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPDCD_Detector
     */
    protected $detector;

    protected function setUp()
    {
        $this->detector = new PHPDCD_Detector;
    }

    /**
     * @covers PHPDCD_Detector::detectDeadCode
     */
    public function testDetectingDeclaredFunctionsAndMethodsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(TEST_FILES_PATH . 'declarations.php'), FALSE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingDeclaredFunctionsAndMethodsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(TEST_FILES_PATH . 'declarations.php'), TRUE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingFunctionCallsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'function_call.php',
            ),
            FALSE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingFunctionCallsWorks
     */
    public function testDetectingFunctionCallsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18
            ),
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'function_call.php',
            ),
            TRUE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingFunctionCallsWorks
     */
    public function testDetectingFunctionCallsWorks3()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'function_call2.php',
            ),
            FALSE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingFunctionCallsWorks3
     */
    public function testDetectingFunctionCallsWorks4()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'function_call2.php',
            ),
            TRUE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingStaticMethodCallsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'static_method_call.php',
            ),
            FALSE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingStaticMethodCallsWorks
     */
    public function testDetectingStaticMethodCallsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'static_method_call.php',
            ),
            TRUE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingMethodCallsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'method_call.php',
            ),
            FALSE
          )
        );
    }

    /**
     * @covers  PHPDCD_Detector::detectDeadCode
     * @depends testDetectingMethodCallsWorks
     */
    public function testDetectingMethodCallsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22
            )
          ),
          $this->detector->detectDeadCode(
            array(
              TEST_FILES_PATH . 'declarations.php',
              TEST_FILES_PATH . 'method_call.php',
            ),
            TRUE
          )
        );
    }

    /**
     * @covers PHPDCD_Detector::detectDeadCode
     */
    public function testThisIsHandledCorrectly()
    {
        $this->assertEmpty(
          $this->detector->detectDeadCode(
            array(TEST_FILES_PATH . 'issue_5.php'), FALSE
          )
        );
    }
}
