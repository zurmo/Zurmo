<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/
    /**
     * XML2Array: A class to convert XML to array in PHP
     * It returns the array which can be converted back to XML using the Array2XML script
     * It takes an XML string or a DOMDocument object as an input.
     *
     * See Array2XML: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
     *
     * Author : Lalit Patel
     * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
     * License: Apache License 2.0
     *          http://www.apache.org/licenses/LICENSE-2.0
     *
     * Usage:
     *       $array = XML2Array::createArray($xml);
     */
    class XML2Array
    {
        private static $xml = null;
        private static $encoding = 'UTF-8';

        /**
         * Initialize the root XML node [optional]
         * @param $version
         * @param $encoding
         * @param $format_output
         */
        public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true)
        {
            self::$xml = new DOMDocument($version, $encoding);
            self::$xml->formatOutput = $format_output;
            self::$encoding = $encoding;
        }

        /**
         * Convert an XML to Array
         * @param string $node_name - name of the root node to be converted
         * @param array $arr - aray to be converterd
         * @return DOMDocument
         */
        public static function &createArray($input_xml, $ignoreRootNodeName = 'zurmoMessage')
        {
            $xml = self::getXMLRoot();
            if (is_string($input_xml))
            {
              $parsed = $xml->loadXML($input_xml);
              if (!$parsed)
              {
                throw new Exception('[XML2Array] Error parsing the XML string.');
              }
            }
            else
            {
              if (get_class($input_xml) != 'DOMDocument')
              {
                throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
              }
              $xml = self::$xml = $input_xml;
            }
            $array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
            self::$xml = null;    // clear the xml node in the class for 2nd time use.
            if ($ignoreRootNodeName && isset($array[$ignoreRootNodeName]))
            {
                return $array[$ignoreRootNodeName];
            }
            else
            {
                return $array;
            }
        }

        /**
         * Convert an Array to XML
         * @param mixed $node - XML as a string or as an object of DOMDocument
         * @return mixed
         */
        private static function &convert($node)
        {
            $output = array();

            switch ($node->nodeType)
            {
                case XML_CDATA_SECTION_NODE:
                    $output['@cdata'] = trim($node->textContent);
                    break;

                case XML_TEXT_NODE:
                    $output = trim($node->textContent);
                    break;

                case XML_ELEMENT_NODE:
                    // for each child node, call the covert function recursively
                    for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++)
                    {
                        $child = $node->childNodes->item($i);
                        $v = self::convert($child);
                        if (isset($child->tagName))
                        {
                            $t = $child->tagName;
                            // assume more nodes of same kind are coming
                            if (!isset($output[$t]))
                            {
                                $output[$t] = array();
                            }
                            $output[$t][] = $v;
                        }
                        else
                        {
                            //check if it is not an empty text node
                            if ($v !== '')
                            {
                                $output = $v;
                            }
                        }
                    }

                    if (is_array($output))
                    {
                        // if only one node of its kind, assign it directly instead if array($value);
                        foreach ($output as $t => $v)
                        {
                            if (is_array($v) && count($v) == 1)
                            {
                                $output[$t] = $v[0];
                            }
                        }
                        if (empty($output))
                        {
                            //for empty nodes
                            $output = '';
                        }
                    }

                    // loop through the attributes and collect them
                    if ($node->attributes->length)
                    {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode)
                        {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        // if its an leaf node, store the value in @value instead of directly storing it.
                        if (!is_array($output))
                        {
                            $output = array('@value' => $output);
                        }
                        $output['@attributes'] = $a;
                    }
                    break;
            }
            return $output;
        }

        /*
         * Get the root XML node, if there isn't one, create it.
         */
        private static function getXMLRoot()
        {
            if (empty(self::$xml))
            {
                self::init();
            }
            return self::$xml;
        }
    }
?>