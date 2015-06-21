<?php
/*
 * This file is part of PHP Dead Code Detector (PHPDCD).
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\PHPDCD\Log;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since     Class available since Release 1.0.0
 */
class Text
{
    /**
     * Prints a result set from PHPDCD_Detector::detectDeadCode().
     *
     * @param Symfony\Component\Console\Output\OutputInterface $output
     * @param array                                            $result
     */
    public function printResult(OutputInterface $output, array $result)
    {
        foreach ($result as $name => $source) {
            $output->writeln(
                sprintf(
                    "  - %s()\n    LOC: %d, declared in %s:%d\n",
                    $name,
                    $source['loc'],
                    $source['file'],
                    $source['line']
                )
            );
        }
    }
}
