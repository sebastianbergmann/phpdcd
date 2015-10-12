<?php
/**
 * This file is part of PHP Dead Code Detector (PHPDCD).
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\PHPDCD\Log;

/**
 * An XML ResultPrinter for the TextUI.
 *
 * @author    Nils Gajsek <info@linslin.org>
 * @copyright Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link      https://github.com/sebastianbergmann/phpdcd
 * @since     Class available since Release 1.0.2
 */
class XML
{
    /**
     * Prints a result set to xml.
     *
     * @param string $filename
     * @param array  $result
     */
    public function printResult($filename, array $result)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        //create xml object
        $root = $document->createElement('phpdcd');
        $document->appendChild($root);

        //setup output xml data
        if ($result > 0) {
            foreach ($result as $data) {

                //create data node
                $root->appendChild(
                    $node = $document->createElement('data')
                );

                //create result data xml children's
                $node->appendChild(
                    $document->createElement('loc', $data['loc'])
                );
                $node->appendChild(
                    $document->createElement('file', $data['file'])
                );
                $node->appendChild(
                    $document->createElement('line', $data['line'])
                );
            }
        }

        file_put_contents($filename, $document->saveXML());
    }
}
