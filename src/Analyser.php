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
 * @package   phpdcd
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2014 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.0
 */

namespace SebastianBergmann\PHPDCD;

/**
 * PHPDCD code analyser to be used on a body of source code.
 *
 * Analyses given source code (files) for declared and called functions
 * and aggregates this information.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2014 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/phpdcd/tree
 * @since     Class available since Release 1.0.0
 */
class Analyser
{
    /**
     * Function declaration mapping: maps declared function name to file and line number
     * TODO: make mapping to file and line number optional for memory usage reduction?
     * @var array
     */
    private $functionDeclarations = array();

    /**
     * Function call mapping: maps "callees" to array of "callers"
     * TODO: make callers array optional for memory usage reduction?
     * @var array
     */
    private $functionCalls = array();

    /**
     * Class hierarchy data: maps classes to their direct parent.
     * @var array
     */
    private $classParents = array();


    public function getFunctionDeclarations()
    {
        return $this->functionDeclarations;
    }

    /**
     * Get function calls we detected
     * @return array maps "callees" to array of "callers"
     */
    public function getFunctionCalls()
    {
        // Resolve parent(class) calls if possible
        foreach ($this->functionCalls as $call => $callers) {
            if (strpos($call, 'parent(') === 0) {
                preg_match('/parent\\((.*?)\\)::(.*)/', $call, $matches);
                $class = $matches[1];
                $method = $matches[2];
                foreach ($this->getAncestors($class) as $ancestor) {
                    $resolvedCall = $ancestor . '::' . $method;
                    if (isset($this->functionDeclarations[$resolvedCall])) {
                        $this->functionCalls[$resolvedCall] = $callers;
                        // TODO: also remove unresolved parent(class) entries?
                        break;
                    }
                }
            }
        }

        return $this->functionCalls;
    }

    /**
     * Get array of a class's ancestors.
     * @param $child
     * @return array of ancestors
     */
    public function getAncestors($child)
    {
        $ancestors = array();
        while (isset($this->classParents[$child])) {
            $child = $this->classParents[$child];
            if (in_array($child, $ancestors)) {
                $cycle = implode(' -> ', $ancestors) . ' -> ' . $child;
                throw new \RuntimeException('Class hierarchy cycle detected: ' . $cycle);
            }
            $ancestors[] = $child;
        }
        return $ancestors;
    }

    /**
     * Build a mapping between parent classes and all their descendants
     * @return array maps each parent classes to array of its subclasses, subsubclasses, ...
     */
    public function getClassDescendants()
    {
        $descendants = array();
        foreach ($this->classParents as $child => $parent) {
            // Direct child
            $descendants[$parent][] = $child;
            // Store child for further ancestors
            $ancestor = $parent;
            while (isset($this->classParents[$ancestor])) {
                $ancestor = $this->classParents[$ancestor];
                $descendants[$ancestor][] = $child;
            }
        }
        return $descendants;
    }

    /**
     * Analyse a PHP source code file for defined and called functions.
     * @param $filename
     */
    public function analyseFile($filename)
    {
        $sourceCode = file_get_contents($filename);
        return $this->analyseSourceCode($sourceCode, $filename);
    }

    /**
     * Analyse PHP source code for defined and called functions
     *
     * @param string $sourceCode source code.
     * @param string $filename optional file name to use in declaration definition
     */
    public function analyseSourceCode($sourceCode, $filename = 'undefined')
    {

        $blocks           = array();
        $currentBlock     = null;
        $currentClass     = '';
        $currentFunction  = '';
        $currentInterface = '';
        $namespace        = '';
        $variables        = array();

        $tokens = new \PHP_Token_Stream($sourceCode);
        $count  = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i] instanceof \PHP_Token_NAMESPACE) {
                $namespace = $tokens[$i]->getName();
            } elseif ($tokens[$i] instanceof \PHP_Token_CLASS) {
                $currentClass = $tokens[$i]->getName();

                if ($namespace != '') {
                    $currentClass = $namespace . '\\' . $currentClass;
                }

                $currentBlock = $currentClass;
            } elseif ($tokens[$i] instanceof \PHP_Token_EXTENDS
                && $tokens[$i+2] instanceof \PHP_Token_STRING) {
                // Store parent-child class relationship.
                $this->classParents[$currentClass] = (string)$tokens[$i+2];
            } elseif ($tokens[$i] instanceof \PHP_Token_INTERFACE) {
                $currentInterface = $tokens[$i]->getName();

                if ($namespace != '') {
                    $currentInterface = $namespace . '\\' . $currentClass;
                }

                $currentBlock = $currentInterface;
            } elseif ($tokens[$i] instanceof \PHP_Token_NEW &&
                !$tokens[$i+2] instanceof \PHP_Token_VARIABLE) {
                if ($tokens[$i-1] instanceof \PHP_Token_EQUAL) {
                    $j = -1;
                } elseif ($tokens[$i-1] instanceof \PHP_Token_WHITESPACE &&
                    $tokens[$i-2] instanceof \PHP_Token_EQUAL) {
                    $j = -2;
                } else {
                    continue;
                }

                if ($tokens[$i+$j-1] instanceof \PHP_Token_WHITESPACE) {
                    $j--;
                }

                if ($tokens[$i+$j-1] instanceof \PHP_Token_VARIABLE) {
                    $name             = (string)$tokens[$i+$j-1];
                    $variables[$name] = (string)$tokens[$i+2];
                } elseif ($tokens[$i+$j-1] instanceof \PHP_Token_STRING &&
                    $tokens[$i+$j-2] instanceof \PHP_Token_OBJECT_OPERATOR &&
                    $tokens[$i+$j-3] instanceof \PHP_Token_VARIABLE) {
                    $name             = (string)$tokens[$i+$j-3] . '->' .
                        (string)$tokens[$i+$j-1];
                    $variables[$name] = (string)$tokens[$i+2];
                }
            } elseif ($tokens[$i] instanceof \PHP_Token_FUNCTION) {
                if ($currentInterface != '') {
                    continue;
                }

                // Ignore abstract methods.
                for ($j=1; $j<=4; $j++) {
                    if (isset($tokens[$i-$j]) &&
                        $tokens[$i-$j] instanceof \PHP_Token_ABSTRACT) {
                        continue 2;
                    }
                }

                $function = $tokens[$i]->getName();

                if ($function == 'anonymous function') {
                    continue;
                }

                $variables = $tokens[$i]->getArguments();

                if ($currentClass != '') {
                    $function = $currentClass . '::' . $function;
                    $variables['$this'] = $currentClass;
                }

                $currentFunction = $function;
                $currentBlock    = $currentFunction;

                $this->functionDeclarations[$function] = array(
                    'file' => $filename, 'line' => $tokens[$i]->getLine()
                );
            } elseif ($tokens[$i] instanceof \PHP_Token_OPEN_CURLY
                || $tokens[$i] instanceof \PHP_Token_CURLY_OPEN
                || $tokens[$i] instanceof \PHP_Token_DOLLAR_OPEN_CURLY_BRACES ) {
                array_push($blocks, $currentBlock);
                $currentBlock = null;
            } elseif ($tokens[$i] instanceof \PHP_Token_CLOSE_CURLY) {
                $block = array_pop($blocks);

                if ($block == $currentClass) {
                    $currentClass = '';
                } elseif ($block == $currentFunction) {
                    $this->functionDeclarations[$currentFunction]['loc'] =
                        $tokens[$i]->getLine() - $this->functionDeclarations[$currentFunction]['line'] + 1;
                    $currentFunction = '';
                    $variables       = array();
                }
            } elseif ($tokens[$i] instanceof \PHP_Token_OPEN_BRACKET) {
                for ($j = 1; $j <= 4; $j++) {
                    if (isset($tokens[$i-$j]) &&
                        $tokens[$i-$j] instanceof \PHP_Token_FUNCTION) {
                        continue 2;
                    }
                }

                if ($tokens[$i-1] instanceof \PHP_Token_STRING) {
                    $j = -1;
                } elseif ($tokens[$i-1] instanceof \PHP_Token_WHITESPACE &&
                    $tokens[$i-2] instanceof \PHP_Token_STRING) {
                    $j = -2;
                } else {
                    continue;
                }

                $function         = (string)$tokens[$i+$j];
                $lookForNamespace = true;

                if (isset($tokens[$i+$j-2]) &&
                    $tokens[$i+$j-2] instanceof \PHP_Token_NEW) {
                    $function .= '::__construct';
                } elseif ((isset($tokens[$i+$j-1]) &&
                        $tokens[$i+$j-1] instanceof \PHP_Token_OBJECT_OPERATOR) ||
                    (isset($tokens[$i+$j-2]) &&
                        $tokens[$i+$j-2] instanceof \PHP_Token_OBJECT_OPERATOR)) {
                    $_function        = $tokens[$i+$j];
                    $lookForNamespace = false;

                    if ($tokens[$i+$j-1] instanceof \PHP_Token_OBJECT_OPERATOR) {
                        $j -= 2;
                    } else {
                        $j -= 3;
                    }

                    if ($tokens[$i+$j] instanceof \PHP_Token_VARIABLE) {
                        if (isset($variables[(string)$tokens[$i+$j]])) {
                            $function = $variables[(string)$tokens[$i+$j]] .
                                '::' . $_function;
                        } else {
                            $function = '::' . $_function;
                        }
                    } elseif ($tokens[$i+$j] instanceof \PHP_Token_STRING &&
                        $tokens[$i+$j-1] instanceof \PHP_Token_OBJECT_OPERATOR &&
                        $tokens[$i+$j-2] instanceof \PHP_Token_VARIABLE) {
                        $variable = (string)$tokens[$i+$j-2] . '->' .
                            (string)$tokens[$i+$j];

                        if (isset($variables[$variable])) {
                            $function = $variables[$variable] . '::' .
                                $_function;
                        }
                    }
                } elseif ($tokens[$i+$j-1] instanceof \PHP_Token_DOUBLE_COLON) {
                    $class = (string)$tokens[$i+$j-2];

                    if ($class == 'self' || $class == 'static') {
                        $class = $currentClass;
                    } elseif ($class == 'parent') {
                        $class = "parent($currentClass)";
                    }

                    $function = $class . '::' . $function;
                    $j       -= 2;
                }

                if ($lookForNamespace) {
                    while ($tokens[$i+$j-1] instanceof \PHP_Token_NS_SEPARATOR) {
                        $function = $tokens[$i+$j-2] . '\\' . $function;
                        $j       -= 2;
                    }
                }

                if (!isset($this->functionCalls[$function])) {
                    $this->functionCalls[$function] = array();
                }
                $this->functionCalls[$function][] = $currentFunction;
            }
        }
    }
}
