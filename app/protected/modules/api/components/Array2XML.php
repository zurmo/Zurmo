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
     * Array2XML: A class to convert array in PHP to XML
     * It also takes into account attributes names unlike SimpleXML in PHP
     * It returns the XML in form of DOMDocument class for further manipulation.
     * It throws exception if the tag name or attribute name has illegal chars.
     *
     * Author : Lalit Patel
     * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
     * License: Apache License 2.0
     *          http://www.apache.org/licenses/LICENSE-2.0
     *
     * Usage:
     *       $xml = Array2XML::createXML('root_node_name', $php_array);
     *       echo $xml->saveXML();
     */

    class Array2XML
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
            self::$xml = new DomDocument($version, $encoding);
            self::$xml->formatOutput = $format_output;
            self::$encoding = $encoding;
        }

        /**
         * Convert an Array to XML
         * @param string $node_name - name of the root node to be converted
         * @param array $arr - aray to be converterd
         * @return DomDocument
         */
        public static function &createXML($node_name, $arr = array())
        {
            $xml = self::getXMLRoot();
            $xml->appendChild(self::convert($node_name, $arr));

            self::$xml = null;    // clear the xml node in the class for 2nd time use.
            return $xml;
        }

        /**
         * Convert an Array to XML
         * @param string $node_name - name of the root node to be converted
         * @param array $arr - aray to be converterd
         * @return DOMNode
         */
        private static function &convert($node_name, $arr = array())
        {
            $xml = self::getXMLRoot();
            $node = $xml->createElement($node_name);

            if (is_array($arr))
            {
                // get the attributes first.;
                if (isset($arr['@attributes']))
                {
                    foreach ($arr['@attributes'] as $key => $value)
                    {
                        if (!self::isValidTagName($key))
                        {
                            throw new Exception('[Array2XML] Illegal character in attribute name. attribute: ' . $key . ' in node: ' . $node_name);
                        }
                        $node->setAttribute($key, self::bool2str($value));
                    }
                    unset($arr['@attributes']); //remove the key from the array once done.
                }

                // check if it has a value stored in @value, if yes store the value and return
                // else check if its directly stored as string
                if (isset($arr['@value']))
                {
                    $node->appendChild($xml->createTextNode(self::bool2str($arr['@value'])));
                    unset($arr['@value']);    //remove the key from the array once done.
                    //return from recursion, as a note with value cannot have child nodes.
                    return $node;
                }
                elseif (isset($arr['@cdata']))
                {
                    $node->appendChild($xml->createCDATASection(self::bool2str($arr['@cdata'])));
                    unset($arr['@cdata']);    //remove the key from the array once done.
                    //return from recursion, as a note with cdata cannot have child nodes.
                    return $node;
                }
            }

            //create subnodes using recursion
            if (is_array($arr))
            {
                // recurse to get the node for that key
                foreach ($arr as $key => $value)
                {
                    if (!self::isValidTagName($key))
                    {
                        throw new Exception('[Array2XML] Illegal character in tag name. tag: ' . $key . ' in node: ' . $node_name);
                    }
                    if (is_array($value) && is_numeric(key($value)))
                    {
                        // MORE THAN ONE NODE OF ITS KIND;
                        // if the new array is numeric index, means it is array of nodes of the same kind
                        // it should follow the parent key name
                        foreach ($value as $k => $v)
                        {
                            $node->appendChild(self::convert($key, $v));
                        }
                    }
                    else
                    {
                        // ONLY ONE NODE OF ITS KIND
                        $node->appendChild(self::convert($key, $value));
                    }
                    unset($arr[$key]); //remove the key from the array once done.
                }
            }

            // after we are done with all the keys in the array (if it is one)
            // we check if it has any text value, if yes, append it.
            if (!is_array($arr))
            {
                $node->appendChild($xml->createTextNode(self::bool2str($arr)));
            }
            return $node;
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

        /*
         * Get string representation of boolean value
         */
        private static function bool2str($v)
        {
            //convert boolean to text value.
            if ($v === true)
            {
                $v = 'true';
            }

            if ($v === false)
            {
                $v = 'false';
            }
            return $v;
        }

        /*
         * Check if the tag name or attribute name contains illegal characters
         * Ref: http://www.w3.org/TR/xml/#sec-common-syn
         */
        private static function isValidTagName($tag)
        {
            $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i'; // Not Coding Standard
            return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
        }
    }
?>