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

/**
 * PHPDCD detector for unused functions.
 *
 * @since     Class available since Release 1.0.0
 */
class Detector
{
    /**
     * @param  array $files
     * @param  bool  $recursive
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
        $declared         = $analyser->getFunctionDeclarations();
        $called           = $analyser->getFunctionCalls();
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
                    $class      = $parts[0];
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
