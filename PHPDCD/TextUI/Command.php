<?php
/**
 * phpdcd
 *
 * Copyright (c) 2009-2012, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright 2009-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.0
 */

/**
 * TextUI frontend for PHPDCD.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/phpdcd/tree
 * @since     Class available since Release 1.0.0
 */
class PHPDCD_TextUI_Command
{
    /**
     * Main method.
     */
    public function main()
    {
        $input  = new ezcConsoleInput;
        $output = new ezcConsoleOutput;

        $input->registerOption(
          new ezcConsoleOption(
            '',
            'exclude',
            ezcConsoleInput::TYPE_STRING,
            array(),
            TRUE
           )
        );

        $input->registerOption(
          new ezcConsoleOption(
            'h',
            'help',
            ezcConsoleInput::TYPE_NONE,
            NULL,
            FALSE,
            '',
            '',
            array(),
            array(),
            FALSE,
            FALSE,
            TRUE
           )
        );

        $input->registerOption(
          new ezcConsoleOption(
            '',
            'recursive',
            ezcConsoleInput::TYPE_NONE
           )
        );

        $input->registerOption(
          new ezcConsoleOption(
            '',
            'suffixes',
            ezcConsoleInput::TYPE_STRING,
            'php',
            FALSE
           )
        );

        $input->registerOption(
          new ezcConsoleOption(
            'v',
            'version',
            ezcConsoleInput::TYPE_NONE,
            NULL,
            FALSE,
            '',
            '',
            array(),
            array(),
            FALSE,
            FALSE,
            TRUE
           )
        );

        $input->registerOption(
          new ezcConsoleOption(
            '',
            'verbose',
            ezcConsoleInput::TYPE_NONE
           )
        );

        try {
            $input->process();
        }

        catch (ezcConsoleOptionException $e) {
            print $e->getMessage() . "\n";
            exit(1);
        }

        if ($input->getOption('help')->value) {
            $this->showHelp();
            exit(0);
        }

        else if ($input->getOption('version')->value) {
            $this->printVersionString();
            exit(0);
        }

        $arguments = $input->getArguments();

        if (empty($arguments)) {
            $this->showHelp();
            exit(1);
        }

        $excludes  = $input->getOption('exclude')->value;
        $recursive = $input->getOption('recursive')->value;
        $suffixes  = explode(',', $input->getOption('suffixes')->value);

        array_map('trim', $suffixes);

        if ($input->getOption('verbose')->value !== FALSE) {
            $verbose = $output;
        } else {
            $verbose = NULL;
        }

        $this->printVersionString();

        $files = $this->findFiles($arguments, $excludes, $suffixes);

        if (empty($files)) {
            $this->showError("No files found to scan.\n");
        }
        
        $detector = new PHPDCD_Detector($verbose);
        $result   = $detector->detectDeadCode($files, $recursive);

        $printer = new PHPDCD_TextUI_ResultPrinter;
        $printer->printResult($result);
        unset($printer);
    }

    /**
     * Shows an error.
     *
     * @param string $message
     */
    protected function showError($message)
    {
        $this->printVersionString();

        print $message;

        exit(1);
    }

    /**
     * Shows the help.
     */
    protected function showHelp()
    {
        $this->printVersionString();

        print <<<EOT
Usage: phpdcd [switches] <directory|file> ...

  --recursive          Report code as dead if it is only called by dead code.

  --exclude <dir>      Exclude <dir> from code analysis.
  --suffixes <suffix>  A comma-separated list of file suffixes to check.

  --help               Prints this usage information.
  --version            Prints the version and exits.

  --verbose            Print progress bar.

EOT;
    }

    /**
     * Prints the version string.
     */
    protected function printVersionString()
    {
        print "phpdcd @package_version@ by Sebastian Bergmann.\n";
    }

    /**
     * @param  array $directories
     * @param  array $excludes
     * @param  array $suffixes
     * @return array
     * @since  Method available since Release 1.4.0
     */
    protected function findFiles(array $directories, array $excludes, array $suffixes)
    {
        $files   = array();
        $finder  = new Symfony\Component\Finder\Finder;
        $iterate = FALSE;

        try {
            foreach ($directories as $directory) {
                if (!is_file($directory)) {
                    $finder->in($directory);
                    $iterate = TRUE;
                } else {
                    $files[] = realpath($directory);
                }
            }

            foreach ($excludes as $exclude) {
                $finder->exclude($exclude);
            }

            foreach ($suffixes as $suffix) {
                $finder->name('*' . $suffix);
            }
        }

        catch (Exception $e) {
            $this->showError($e->getMessage() . "\n");
            exit(1);
        }

        if ($iterate) {
            foreach ($finder as $file) {
                $files[] = $file->getRealpath();
            }
        }

        return $files;
    }
}
