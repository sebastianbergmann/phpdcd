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
 * PHPDCD detector for unused functions.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2014 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/phpdcd/tree
 * @since     Class available since Release 1.0.0
 */
class Detector
{
    /**
     * @param  array   $files
     * @param  boolean $recursive
     * @return array
     */
    public function detectDeadCode(array $files, $recursive = false)
    {

        // Analyse files and collect declared and called functions
        $analyser = new Analyser();
        foreach ($files as $file) {
            $analyser->analyseFile($file);
        }

        // Get info on declared and called functions.
        $declared = $analyser->getFunctionDeclarations();
        $called = $analyser->getFunctionCalls();
        $classDescendants = $analyser->getClassDescendants();

        // Search for declared, unused functions.
        $result = array();
        foreach ($declared as $name => $source) {
            if (!isset($called[$name])) {
                // Unused function/method at first sight.
                $used = false;
                // For methods: check calls from subclass instances as well
                $parts = explode('::', $name);
                if (count($parts) == 2) {
                    $class = $parts[0];
                    $subclasses = isset($classDescendants[$class]) ? $classDescendants[$class] : array();
                    foreach ($subclasses as $subclass) {
                        if (isset($called[$subclass . '::' . $parts[1]])) {
                            $used = true;
                            break;
                        }
                    }
                }

                if (!$used) {
                    $result[$name] = $source;
                }
            }
        }

        if ($recursive) {
            $done = false;

            while (!$done) {
                $done = true;

                foreach ($called as $callee => $callers) {
                    $_called = false;

                    foreach ($callers as $caller) {
                        if (!isset($result[$caller])) {
                            $_called = true;
                            break;
                        }
                    }

                    if (!$_called) {
                        if (isset($declared[$callee])) {
                            $result[$callee] = $declared[$callee];
                        }

                        $done = false;

                        unset($called[$callee]);
                    }
                }
            }
        }

        ksort($result);

        return $result;
    }
}
