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
     *
     * Validates XHtml
     *
     */
    class W3CValidatorServiceUtil
    {
        /**
         * Validates the page content against the XHTML schema
         * using W3C XHtml validator and writes the problems
         * directly to output in bright
         * red on yellow.
         * @param string $content
         * @return array
         */
        public static function validate($content)
        {
            $xhtmlValidationErrors = array();
            $params = array(
                'fragment' => $content,
                'output' => 'soap12',
            );

            $url = 'http://validator.w3.org/check';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // multipart encoding
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_REFERER, '');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP CURL');

            $xml = curl_exec($ch);
            if (curl_errno($ch))
            {
                throw new FailedServiceException(curl_error($ch));
            }
            else
            {
                curl_close($ch);
            }

            $doc = simplexml_load_string($xml);
            $doc->registerXPathNamespace('m', 'http://www.w3.org/2005/10/markup-validator');
            $nodes = $doc->xpath('//m:markupvalidationresponse/m:validity');
            $validity = $nodes[0];

            $errorNodes = $doc->xpath('//m:markupvalidationresponse/m:errors/m:errorcount');
            $errorCount = strval($errorNodes[0]);
            $errorNodes = $doc->xpath('//m:markupvalidationresponse/m:errors/m:errorlist/m:error');

            $warningNodes = $doc->xpath('//m:markupvalidationresponse/m:warnings/m:warningcount');

            // We don't want to count and show warning about encoding type, which happen when we
            // upload file directly to w3w or use API
            // This warning will appear on all pages, and we can just ignore it.
            $warningCount = strval($warningNodes[0]) - 1;
            $warningNodes = $doc->xpath('//m:markupvalidationresponse/m:warnings/m:warninglist/m:warning');

            if (!$validity || $errorCount > 0 || $warningCount > 0)
            {
                if ($errorCount)
                {
                    $xhtmlValidationErrors[] = 'There are ' . $errorCount . ' error(s)';
                    foreach ($errorNodes as $node)
                    {
                        $errorNodes = $node->xpath('m:line');
                        $line = strval($errorNodes[0]);
                        $errorNodes = $node->xpath('m:col');
                        $col = strval($errorNodes[0]);
                        $errorNodes = $node->xpath('m:message');
                        $message = strval($errorNodes[0]);
                        $errorMessage = 'line: ' . $line . ', column: ' . $col . ' message: ' . $message ;
                        $xhtmlValidationErrors[] = "$errorMessage";
                    }
                }

                if ($warningCount)
                {
                    $xhtmlValidationErrors[] = 'There are ' . $warningCount . ' warning(s)';
                    foreach ($warningNodes as $node)
                    {
                        $errorMessage = "";
                        $warningNodes = $node->xpath('m:line');
                        if (isset($warningNodes[0]))
                        {
                            $line = strval($warningNodes[0]);
                            $errorMessage .= 'line: ' . $line . ', ';
                        }
                        $warningNodes = $node->xpath('m:col');
                        if (isset($warningNodes[0]))
                        {
                            $col = strval($warningNodes[0]);
                            $errorMessage .= ' column: ' . $col . ', ';
                        }
                        $warningNodes = $node->xpath('m:message');
                        $message = strval($warningNodes[0]);

                        if ($message == 'Using Direct Input mode: UTF-8 character encoding assumed')
                        {
                            // This is just a message, because we didn't validate code by url
                            // So just ignore this message.
                            continue;
                        }
                        $errorMessage .=  'message: ' . $message ;
                        $xhtmlValidationErrors[] = $errorMessage;
                    }
                }

                if (!empty($xhtmlValidationErrors))
                {
                    array_unshift($xhtmlValidationErrors, 'THIS IS NOT A VALID XHTML FILE');
                }
            }
            return $xhtmlValidationErrors;
        }

        public static function resolveClean(& $output, $comparisonString, $secondComparisonString)
        {
            if (strpos($output, $comparisonString) === false ||  strpos($output, $secondComparisonString) === false)
            {
                eval("\x24\x6f\x75\x74\x70\x75\x74\x20\x2e\x3d\x20\x22\x3c\x61\x20\x68\x72\x65\x66\x3d" .
                     "\x27\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x7a\x75\x72\x6d\x6f\x2e\x63\x6f" .
                     "\x6d\x27\x20\x69\x64\x3d\x27\x63\x72\x65\x64\x69\x74\x2d\x6c\x69\x6e\x6b\x27\x20" .
                     "\x63\x6c\x61\x73\x73\x3d\x27\x63\x6c\x65\x61\x72\x66\x69\x78\x27\x3e\x3c\x73\x70" .
                     "\x61\x6e\x3e\x43\x6f\x70\x79\x72\x69\x67\x68\x74\x20\x26\x23\x31\x36\x39\x3b\x20" .
                     "\x5a\x75\x72\x6d\x6f\x20\x49\x6e\x63\x2e\x2c\x20\x32\x30\x31\x33\x2e\x20\x41\x6c" .
                     "\x6c\x20\x72\x69\x67\x68\x74\x73\x20\x72\x65\x73\x65\x72\x76\x65\x64\x2e\x20\x3c" .
                     "\x2f\x73\x70\x61\x6e\x3e\x3c\x2f\x61\x3e\x22\x3b");
            }
        }
    }
?>