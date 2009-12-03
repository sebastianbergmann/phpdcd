<?php
/**
 * phpdcd
 *
 * Copyright (c) 2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright 2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since     File available since Release 1.0.0
 */

require_once 'PHP/Token/Stream.php';

/**
 * PHPDCD code analyser.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/phpdcd/tree
 * @since     Class available since Release 1.0.0
 */
class PHPDCD_Detector
{
    /**
     * @param  array   $files
     * @param  boolean $recursive
     * @return array
     */
    public function detectDeadCode(array $files, $recursive = FALSE)
    {
        $blocks          = array();
        $called          = array();
        $currentBlock    = NULL;
        $currentClass    = '';
        $currentFunction = '';
        $declared        = array();
        $namespace       = '';
        $result          = array();
        $variables       = array();

        foreach ($files as $file) {
            $tokens = new PHP_Token_Stream($file);
            $count  = count($tokens);

            for ($i = 0; $i < $count; $i++) {
                if ($tokens[$i] instanceof PHP_Token_NAMESPACE) {
                    $namespace = $tokens[$i]->getName();
                }

                else if ($tokens[$i] instanceof PHP_Token_CLASS) {
                    $currentClass = $tokens[$i]->getName();

                    if ($namespace != '') {
                        $currentClass = $namespace . '\\' . $currentClass;
                    }

                    $currentBlock = $currentClass;
                }

                else if ($tokens[$i] instanceof PHP_Token_NEW &&
                         !$tokens[$i+2] instanceof PHP_Token_VARIABLE) {
                    if ($tokens[$i-1] instanceof PHP_Token_EQUAL) {
                        $j = -1;
                    }

                    else if ($tokens[$i-1] instanceof PHP_Token_WHITESPACE &&
                             $tokens[$i-2] instanceof PHP_Token_EQUAL) {
                        $j = -2;
                    }

                    else {
                        continue;
                    }

                    if ($tokens[$i+$j-1] instanceof PHP_Token_WHITESPACE) {
                        $j--;
                    }

                    if ($tokens[$i+$j-1] instanceof PHP_Token_VARIABLE) {
                        $name             = (string)$tokens[$i+$j-1];
                        $variables[$name] = (string)$tokens[$i+2];
                    }

                    else if ($tokens[$i+$j-1] instanceof PHP_Token_STRING &&
                             $tokens[$i+$j-2] instanceof PHP_Token_OBJECT_OPERATOR &&
                             $tokens[$i+$j-3] instanceof PHP_Token_VARIABLE) {
                        $name             = (string)$tokens[$i+$j-3] . '->' .
                                            (string)$tokens[$i+$j-1];
                        $variables[$name] = (string)$tokens[$i+2];
                    }
                }

                else if ($tokens[$i] instanceof PHP_Token_FUNCTION) {
                    $function = $tokens[$i]->getName();

                    if ($function == 'anonymous function') {
                        continue;
                    }

                    $variables = $tokens[$i]->getArguments();

                    if ($currentClass != '') {
                        $function = $currentClass . '::' . $function;
                    }

                    $currentFunction = $function;
                    $currentBlock    = $currentFunction;

                    $declared[$function] = array(
                      'file' => $file, 'line' => $tokens[$i]->getLine()
                    );
                }

                else if ($tokens[$i] instanceof PHP_Token_OPEN_CURLY) {
                    array_push($blocks, $currentBlock);
                    $currentBlock = NULL;
                }

                else if ($tokens[$i] instanceof PHP_Token_CLOSE_CURLY) {
                    $block = array_pop($blocks);

                    if ($block == $currentClass) {
                        $currentClass = '';
                    }

                    else if ($block == $currentFunction) {
                        $currentFunction = '';
                        $variables       = array();
                    }
                }

                else if ($tokens[$i] instanceof PHP_Token_OPEN_BRACKET) {
                    for ($j = 1; $j <= 4; $j++) {
                        if ($tokens[$i-$j] instanceof PHP_Token_FUNCTION) {
                            continue 2;
                        }
                    }

                    if ($tokens[$i-1] instanceof PHP_Token_STRING) {
                        $j = -1;
                    }

                    else if ($tokens[$i-1] instanceof PHP_Token_WHITESPACE &&
                             $tokens[$i-2] instanceof PHP_Token_STRING) {
                        $j = -2;
                    }

                    else {
                        continue;
                    }

                    $function         = (string)$tokens[$i+$j];
                    $lookForNamespace = TRUE;

                    if ($tokens[$i+$j-2] instanceof PHP_Token_NEW) {
                        $function .= '::__construct';
                    }

                    else if ($tokens[$i+$j-1] instanceof PHP_Token_OBJECT_OPERATOR ||
                             $tokens[$i+$j-2] instanceof PHP_Token_OBJECT_OPERATOR) {
                        $_function        = $tokens[$i+$j];
                        $lookForNamespace = FALSE;

                        if ($tokens[$i+$j-1] instanceof PHP_Token_OBJECT_OPERATOR) {
                            $j -= 2;
                        } else {
                            $j -= 3;
                        }

                        if ($tokens[$i+$j] instanceof PHP_Token_VARIABLE &&
                            isset($variables[(string)$tokens[$i+$j]])) {
                            $function = $variables[(string)$tokens[$i+$j]] .
                                        '::' . $_function;
                        }

                        else if ($tokens[$i+$j] instanceof PHP_Token_STRING &&
                                 $tokens[$i+$j-1] instanceof PHP_Token_OBJECT_OPERATOR &&
                                 $tokens[$i+$j-2] instanceof PHP_Token_VARIABLE) {
                            $variable = (string)$tokens[$i+$j-2] . '->' .
                                        (string)$tokens[$i+$j];

                            if (isset($variables[$variable])) {
                                $function = $variables[$variable] . '::' .
                                            $_function;
                            }
                        }
                    }

                    else if ($tokens[$i+$j-1] instanceof PHP_Token_DOUBLE_COLON) {
                        $class = $tokens[$i+$j-2];

                        if ($class == 'self' || $class == 'static') {
                            $class = $currentClass;
                        }

                        $function = $class . '::' . $function;
                        $j       -= 2;
                    }

                    if ($lookForNamespace) {
                        while ($tokens[$i+$j-1] instanceof PHP_Token_NS_SEPARATOR) {
                            $function = $tokens[$i+$j-2] . '\\' . $function;
                            $j       -= 2;
                        }
                    }

                    if (!isset($called[$function])) {
                        $called[$function] = array();
                    }

                    $called[$function][] = $currentFunction;
                }
            }
        }

        foreach ($declared as $name => $source) {
            if (!isset($called[$name])) {
                $result[$name] = $source;
            }
        }

        if ($recursive) {
            $done = FALSE;

            while (!$done) {
                $done = TRUE;

                foreach ($called as $callee => $callers) {
                    $_called = FALSE;

                    foreach ($callers as $caller) {
                        if (!isset($result[$caller])) {
                            $_called = TRUE;
                            break;
                        }
                    }

                    if (!$_called) {
                        $result[$callee] = $declared[$callee];
                        $done            = FALSE;

                        unset($called[$callee]);
                    }
                }
            }
        }

        ksort($result);

        return $result;
    }
}
?>
