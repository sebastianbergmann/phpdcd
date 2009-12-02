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

require_once 'File/Iterator/Factory.php';
require_once 'PHPDCD/Detector.php';
require_once 'PHPDCD/TextUI/Getopt.php';
require_once 'PHPDCD/TextUI/ResultPrinter.php';

/**
 * TextUI frontend for PHPDCD.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/phpdcd/tree
 * @since     Class available since Release 1.0.0
 */
class PHPDCD_TextUI_Command
{
    /**
     * Main method.
     */
    public static function main()
    {
        try {
            $options = PHPDCD_TextUI_Getopt::getopt(
              $_SERVER['argv'],
              '',
              array(
                'help',
                'exclude=',
                'recursive',
                'suffixes=',
                'version'
              )
            );
        }

        catch (RuntimeException $e) {
            self::showError($e->getMessage());
        }

        $exclude   = array();
        $suffixes  = array('php');
        $recursive = FALSE;

        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--exclude': {
                    $exclude[] = $option[1];
                }
                break;

                case '--recursive': {
                    $recursive = TRUE;
                }
                break;

                case '--suffixes': {
                    $suffixes = explode(',', $option[1]);
                    array_map('trim', $suffixes);
                }
                break;

                case '--help': {
                    self::showHelp();
                    exit(0);
                }
                break;

                case '--version': {
                    self::printVersionString();
                    exit(0);
                }
                break;
            }
        }

        if (isset($options[1][0])) {
            $files = File_Iterator_Factory::getFilesAsArray(
              $options[1], $suffixes, array(), $exclude
            );
        } else {
            self::showHelp();
            exit(1);
        }

        self::printVersionString();

        $detector = new PHPDCD_Detector;
        $result   = $detector->detectDeadCode($files, $recursive);

        $printer = new PHPDCD_TextUI_ResultPrinter;
        $printer->printResult($result, self::getCommonPath($files));
        unset($printer);
    }

    /**
     * Returns the common path of a set of files.
     *
     * @param  array $files
     * @return string
     */
    protected static function getCommonPath(array $files)
    {
        $count = count($files);

        if ($count == 1) {
            return dirname($files[0]) . DIRECTORY_SEPARATOR;
        }

        $_files = array();

        for ($i = 0; $i < $count; $i++) {
            $_files[$i] = explode(DIRECTORY_SEPARATOR, $files[$i]);

            if (empty($_files[$i][0])) {
                $_files[$i][0] = DIRECTORY_SEPARATOR;
            }
        }

        $common = '';
        $done   = FALSE;
        $j      = 0;
        $count--;

        while (!$done) {
            for ($i = 0; $i < $count; $i++) {
                if ($_files[$i][$j] != $_files[$i+1][$j]) {
                    $done = TRUE;
                    break;
                }
            }

            if (!$done) {
                $common .= $_files[0][$j];

                if ($j > 0) {
                    $common .= DIRECTORY_SEPARATOR;
                }
            }

            $j++;
        }

        return $common;
    }

    /**
     * Shows an error.
     *
     * @param string $message
     */
    protected static function showError($message)
    {
        self::printVersionString();

        print $message;

        exit(1);
    }

    /**
     * Shows the help.
     */
    protected static function showHelp()
    {
        self::printVersionString();

        print <<<EOT
Usage: phpdcd [switches] <directory|file> ...

  --recursive          Report code as dead if it is only called by dead code.

  --exclude <dir>      Exclude <dir> from code analysis.
  --suffixes <suffix>  A comma-separated list of file suffixes to check.

  --help               Prints this usage information.
  --version            Prints the version and exits.

EOT;
    }

    /**
     * Prints the version string.
     */
    protected static function printVersionString()
    {
        print "phpdcd @package_version@ by Sebastian Bergmann.\n";
    }
}
?>
