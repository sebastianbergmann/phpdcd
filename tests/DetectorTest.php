<?php
/**
 * phpdcd
 *
 * Copyright (c) 2009-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2009-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since      File available since Release 1.0.0
 */

namespace SebastianBergmann\PHPDCD;

use PHPUnit_Framework_TestCase;

if (!defined('TEST_FILES_PATH')) {
    define(
      'TEST_FILES_PATH',
      dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR
    );
}

/**
 * Tests for the SebastianBergmann\PHPDCD\Detector class.
 *
 * @package    phpdcd
 * @subpackage Tests
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2009-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://github.com/sebastianbergmann/phpdcd/
 * @since      Class available since Release 1.0.0
 */
class DetectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Detector
     */
    protected $detector;

    protected function setUp()
    {
        $this->detector = new Detector;
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Detector::detectDeadCode
     */
    public function testDetectingDeclaredFunctionsAndMethodsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
            )
          ),
          $this->detector->detectDeadCode(
            array(TEST_FILES_PATH . 'declarations.php'), FALSE
          )
        );
    }

    /**
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingDeclaredFunctionsAndMethodsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18,
              'loc' => 3,
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
            )
          ),
          $this->detector->detectDeadCode(
            array(TEST_FILES_PATH . 'declarations.php'), TRUE
          )
        );
    }

    /**
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingFunctionCallsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
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
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingFunctionCallsWorks
     */
    public function testDetectingFunctionCallsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18,
              'loc' => 3,
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
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingFunctionCallsWorks
     */
    public function testDetectingFunctionCallsWorks3()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
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
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingFunctionCallsWorks3
     */
    public function testDetectingFunctionCallsWorks4()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
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
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingStaticMethodCallsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
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
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingStaticMethodCallsWorks
     */
    public function testDetectingStaticMethodCallsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 8,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18,
              'loc' => 3,
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
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
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingDeclaredFunctionsAndMethodsWorks
     */
    public function testDetectingMethodCallsWorks()
    {
        $this->assertEquals(
          array(
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
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
     * @covers  SebastianBergmann\PHPDCD\Detector::detectDeadCode
     * @depends testDetectingMethodCallsWorks
     */
    public function testDetectingMethodCallsWorks2()
    {
        $this->assertEquals(
          array(
            'AClass::aStaticMethod' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 4,
              'loc' => 3,
            ),
            'a_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 13,
              'loc' => 4
            ),
            'another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 18,
              'loc' => 3,
            ),
            'yet_another_function' => array(
              'file' => TEST_FILES_PATH . 'declarations.php',
              'line' => 22,
              'loc' => 3,
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
     * @covers SebastianBergmann\PHPDCD\Detector::detectDeadCode
     */
    public function testThisIsHandledCorrectly()
    {
        $this->assertEmpty(
          $this->detector->detectDeadCode(
            array(TEST_FILES_PATH . 'issue_5.php'), FALSE
          )
        );
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Detector::detectDeadCode
     */
    public function testParentMethods()
    {
        $file = TEST_FILES_PATH . 'issue_18.php';
        $this->assertEquals(
            array(
                'Rabbit::eatsCarrots' => array(
                    'file' => $file,
                    'line' => 18,
                    'loc' => 4,
                ),
            ),
            $this->detector->detectDeadCode(array($file), FALSE)
        );
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Detector::detectDeadCode
     */
    public function testGreatParentMethods()
    {
        $file = TEST_FILES_PATH . 'issue_18_extra.php';
        $this->assertEquals(
            array(),
            $this->detector->detectDeadCode(array($file), FALSE)
        );
    }


    /**
     * @covers SebastianBergmann\PHPDCD\Detector::detectDeadCode
     */
    public function testParentDoubleColonHandling()
    {
        $file = TEST_FILES_PATH . 'parent_double_colon_handling.php';
        $result = $this->detector->detectDeadCode(array($file), FALSE);
        $this->assertEquals(array(), $result);
    }


    /**
     * @see https://github.com/sebastianbergmann/phpdcd/issues/26
     * @covers SebastianBergmann\PHPDCD\Detector::detectDeadCode
     */
    public function testMethodsFunctionsMixup()
    {
        $file = TEST_FILES_PATH . 'methods_vs_functions.php';
        $result = $this->detector->detectDeadCode(array($file), FALSE);
        $this->assertEquals(array('Klass::doSomething', 'doSomething'), array_keys($result));
    }


    /**
     * @covers SebastianBergmann\PHPDCD\Detector::detectDeadCode
     */
    public function testIgnoreAbstractMethods()
    {
        $file = TEST_FILES_PATH . 'abstract_methods.php';
        $result = $this->detector->detectDeadCode(array($file), FALSE);
        $this->assertEquals(array('Painting::getShape'), array_keys($result));

    }

}
