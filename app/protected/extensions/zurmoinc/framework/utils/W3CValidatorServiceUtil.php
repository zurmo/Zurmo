<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
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
            $nodes = $doc->xpath('//m:markupvalidationresponse/m:errors/m:errorcount');
            $errorcount = strval($nodes[0]);
            $nodes = $doc->xpath('//m:markupvalidationresponse/m:errors/m:errorlist/m:error');

            if (!$validity || count($nodes) > 0)
            {
                $xhtmlValidationErrors[] = 'THIS IS NOT A VALID XHTML FILE';
                $xhtmlValidationErrors[] = 'There are ' . $errorcount . ' errors';
                foreach ($nodes as $node)
                {
                    $nodes = $node->xpath('m:line');
                    $line = strval($nodes[0]);
                    $nodes = $node->xpath('m:col');
                    $col = strval($nodes[0]);
                    $nodes = $node->xpath('m:message');
                    $message = strval($nodes[0]);
                    $errorMessage = 'line: ' . $line . ', column: ' . $col . ' message: ' . $message ;
                    $xhtmlValidationErrors[] = "$errorMessage";
                }
            }
            return $xhtmlValidationErrors;
        }
    }
?>