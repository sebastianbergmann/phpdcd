<?php
/*
 * This file is part of PHP Dead Code Detector (PHPDCD).
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * Tests for the SebastianBergmann\PHPDCD\Analyser class.
 *
 * @package    phpdcd
 * @subpackage Tests
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://github.com/sebastianbergmann/phpdcd/
 * @since      Class available since Release 1.0.0
 */
class AnalyserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Analyser
     */
    protected $analyser;

    protected function setUp()
    {
        $this->analyser = new Analyser();
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionDeclarations
     */
    public function testDetectingDeclaredFunctionsAndMethodsWorks()
    {
        $file = TEST_FILES_PATH . 'declarations.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('AClass::aStaticMethod', 'AClass::aMethod', 'a_function', 'another_function', 'yet_another_function'),
            array_keys($this->analyser->getFunctionDeclarations())
        );
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionCalls
     */
    public function testDetectingCalledFunctionsAndMethodsWorks()
    {
        $file = TEST_FILES_PATH . 'declarations.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('another_function'),
            array_keys($this->analyser->getFunctionCalls())
        );
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionDeclarations SebastianBergmann\PHPDCD\Analyser::getFunctionCalls SebastianBergmann\PHPDCD\Analyser::getClassDescendants
     */
    public function testParentMethods()
    {
        $file = TEST_FILES_PATH . 'issue_18.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('Animal::hasHead', 'Rabbit::hasFur', 'Rabbit::eatsCarrots'),
            array_keys($this->analyser->getFunctionDeclarations())
        );
        $this->assertEquals(
            array('Rabbit::__construct', 'Rabbit::hasHead', 'Rabbit::hasFur'),
            array_keys($this->analyser->getFunctionCalls())
        );
        $this->assertEquals(
            array('Animal' => array('Rabbit')),
            $this->analyser->getClassDescendants()
        );
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionDeclarations SebastianBergmann\PHPDCD\Analyser::getFunctionCalls SebastianBergmann\PHPDCD\Analyser::getClassDescendants
     */
    public function testGreatParentMethods()
    {
        $file = TEST_FILES_PATH . 'issue_18_extra.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('Animal::hasHead', 'FurryAnimal::hasFur', 'Rabbit::isCute'),
            array_keys($this->analyser->getFunctionDeclarations())
        );
        $this->assertEquals(
            array('Rabbit::__construct', 'Rabbit::hasHead', 'Rabbit::hasFur', 'Rabbit::isCute'),
            array_keys($this->analyser->getFunctionCalls())
        );
        $this->assertEquals(
            array(
                'Animal' => array('FurryAnimal', 'Rabbit'),
                'FurryAnimal' => array('Rabbit')
            ),
            $this->analyser->getClassDescendants()
        );
    }


    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionDeclarations SebastianBergmann\PHPDCD\Analyser::getFunctionCalls
     */
    public function testParentDoubleColonHandling()
    {
        $file = TEST_FILES_PATH . 'parent_double_colon_handling.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('Toy::ping', 'Ball::roll'),
            array_keys($this->analyser->getFunctionDeclarations())
        );
        $calls = $this->analyser->getFunctionCalls();
        $this->assertArrayHasKey('Toy::ping', $calls);
        $this->assertArrayHasKey('Ball::__construct', $calls);
        $this->assertArrayHasKey('Ball::roll', $calls);
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getClassDescendants
     */
    public function testGetClassDescendants()
    {
        $sourceCode = '<?php
            abstract class A {}
            class B extends A {}
            class C extends A {}
            class D extends B {}
            class E extends D {}
            class Z implements I {}
        ';
        $this->analyser->analyseSourceCode($sourceCode);
        $descendants = $this->analyser->getClassDescendants();
        $expected = array(
            'A' => array('B', 'C', 'D', 'E'),
            'B' => array('D', 'E'),
            'D' => array('E'),
        );
        $this->assertSame($expected, $descendants);
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getAncestors
     * @dataProvider provideTestGetAncestors
     */
    public function testGetAncestors($child, $expectedAncestors)
    {
        $sourceCode = '<?php
            abstract class A {}
            class B extends A {}
            class C extends A {}
            class D extends B {}
            class E extends D {}
            class Z implements I {}
        ';
        $this->analyser->analyseSourceCode($sourceCode);
        $ancestors = $this->analyser->getAncestors($child);
        sort($ancestors);
        sort($expectedAncestors);
        $this->assertSame($expectedAncestors, $ancestors);
    }

    /**
     * Data provider for testGetAncestors
     */
    public function provideTestGetAncestors()
    {
        $data = array();
        $data[] = array('A', array());
        $data[] = array('B', array('A'));
        $data[] = array('C', array('A'));
        $data[] = array('D', array('A', 'B'));
        $data[] = array('E', array('A', 'B', 'D'));
        $data[] = array('Z', array());
        return $data;
    }

    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getAncestors
     */
    public function testGetAncestorsWithCycle()
    {
        // This code snippet is invalid PHP,
        // but PHPDCD should complain about this and not naively end up in an endless loop.
        $sourceCode = '<?php
            class A extends D {}
            class B extends A {}
            class C extends B {}
            class D extends C {}
        ';
        $this->analyser->analyseSourceCode($sourceCode);
        $this->setExpectedException('RunTimeException', 'Class hierarchy cycle detected');
        $this->analyser->getAncestors('A');
    }

    /**
     * @see https://github.com/sebastianbergmann/phpdcd/issues/26
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionDeclarations SebastianBergmann\PHPDCD\Analyser::getFunctionCalls
     */
    public function testMethodsFunctionsMixup()
    {
        $file = TEST_FILES_PATH . 'methods_vs_functions.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('Klass::doSomething', 'doSomething', 'main'),
            array_keys($this->analyser->getFunctionDeclarations())
        );
        $this->assertEquals(
            array('::doSomething', 'Klass::__construct', 'main'),
            array_keys($this->analyser->getFunctionCalls())
        );
    }


    /**
     * @see https://github.com/sebastianbergmann/phpdcd/issues/28
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionDeclarations
     */
    public function testComplexVariableInterpolation()
    {
        $file = TEST_FILES_PATH . 'Interpolator.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('Interpolator::methodFoo', 'Interpolator::methodBar', 'Interpolator::methodBazBaz'),
            array_keys($this->analyser->getFunctionDeclarations())
        );
    }


    /**
     * @covers SebastianBergmann\PHPDCD\Analyser::getFunctionDeclarations
     */
    public function testIgnoreAbstractMethods()
    {
        $file = TEST_FILES_PATH . 'abstract_methods.php';
        $this->analyser->analyseFile($file);
        $this->assertEquals(
            array('Painting::getShape'),
            array_keys($this->analyser->getFunctionDeclarations())
        );
    }


}
