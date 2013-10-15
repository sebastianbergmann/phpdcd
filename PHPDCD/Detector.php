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
 * @package   phpdcd
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2013 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.0
 */

/**
 * PHPDCD code analyser.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2013 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/phpdcd/tree
 * @since     Class available since Release 1.0.0
 */
class PHPDCD_Detector
{
    /**
     * @var ezcConsoleOutput
     */
    protected $output;

    /**
     * Constructor.
     *
     * @param ezcConsoleOutput $output
     */
    public function __construct(ezcConsoleOutput $output = NULL)
    {
        $this->output = $output;
    }


    /**
     * @param  array   $files
     * @param  boolean $recursive
     * @return array
     */
    public function detectDeadCode(array $files, $recursive = FALSE)
    {
        if ($this->output !== NULL) {
            $bar = new ezcConsoleProgressbar($this->output, count($files));
            print "\nProcessing files\n";
        }

        // Analyse files and collect declared and called functions
        $analyser = new PHPDCD_Analyser();
        foreach ($files as $file) {
            $analyser->analyseFile($file);

            if ($this->output !== NULL) {
                $bar->advance();
            }
        }

        // Get info on declared and called functions.
        $declared = $analyser->getFunctionDeclarations();
        $called = $analyser->getFunctionCalls();
        $classDescendants = $analyser->getClassDescendants();

        // Build result array: declared functions that were not called.
        $result = array();
        foreach ($declared as $name => $source) {
            if (!isset($called[$name])) {
                $used = FALSE;
                // For methods: check calls from subclass instances as well
                $parts = explode('::', $name);
                if (count($parts) == 2) {
                    $class = $parts[0];
                    $subclasses = isset($classDescendants[$class]) ? $classDescendants[$class] : array();
                    foreach ($subclasses as $subclass) {
                        // TODO: also check if parent implementations are completely hidden by all child's implementations?
                        if (isset($called[$subclass . '::' . $parts[1]])) {
                            $used = TRUE;
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
                        if (isset($declared[$callee])) {
                            $result[$callee] = $declared[$callee];
                        }

                        $done = FALSE;

                        unset($called[$callee]);
                    }
                }
            }
        }

        ksort($result);

        if ($this->output !== NULL) {
            print "\n";
        }

        return $result;
    }
}
