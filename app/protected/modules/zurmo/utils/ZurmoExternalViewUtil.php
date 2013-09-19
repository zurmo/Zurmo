<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for constructing the default view used for rendering external widgets
     */
    class ZurmoExternalViewUtil
    {
        const EXTERNAL_SCRIPT_FILE_NAME = 'ExternalScripts.js';

        /**
         * @param View $containedView
         * @return GridView
         */
        public static function makeExternalViewForCurrentUser(View $containedView)
        {
            $horizontalGridView = new GridView(1, 1);
            $containedView->setCssClasses(array_merge($containedView->getCssClasses(), array('AppContent')));
            $horizontalGridView->setView($containedView, 0, 0);
            $verticalGridView   = new GridView(1, 1);
            $verticalGridView->setView($horizontalGridView, 0, 0);
            return $verticalGridView;
        }

        /**
         * @param $rawXHtml
         * @param bool $excludeStyles
         * @return array
         */
        public static function resolveHeadTag($rawXHtml, $excludeStyles = false)
        {
            $dom        = new DOMDocument();
            $headBody   = array('js'    => array(),
                                'css'   => array(),
                                'style' => array());
            libxml_use_internal_errors(true);
            $dom->loadHTML($rawXHtml);
            $head       = $dom->getElementsByTagName('head')->item(0);
            foreach ($head->childNodes as $child)
            {
                if ($child->nodeName == 'script' && $child->hasAttribute('src'))
                {
                    $headBody['js'][] = $child->getAttribute('src');
                }
                elseif (!$excludeStyles && $child->nodeName == 'link' && $child->hasAttribute('rel'))
                {
                    if ($child->getAttribute('rel') == 'stylesheet')
                    {
                        if (strpos($child->getAttribute('href'), 'jquery-ui-timepicker-addon.css'))
                        {
                            $resourceUrl = Yii::app()->getRequest()->getHostInfo() . $child->getAttribute('href');
                        }
                        else
                        {
                            $resourceUrl = $child->getAttribute('href');
                        }
                        $headBody['css'][] = array('rel'  => $child->getAttribute('rel'),
                                                   'type' => $child->getAttribute('type'),
                                                   'href' => $resourceUrl);
                    }
                }
                elseif (!$excludeStyles && $child->nodeName == 'style')
                {
                    $headBody['style'][] = $child->nodeValue;
                }
            }
            return $headBody;
        }

        /**
         * @param $rawXHtml
         * @return array
         */
        public static function resolveHtmlAndScriptInBody($rawXHtml)
        {
            $dom            = new DOMDocument();
            $bodyContent    = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($rawXHtml);
            $body           = $dom->getElementsByTagName('body')->item(0);
            foreach ($body->childNodes as $child)
            {
                $bodyContent->appendChild($bodyContent->importNode($child, true));
            }
            $scriptTagsInBody                   = static::resolveScriptTagsInBody($bodyContent);
            $htmlAndScriptTagsInBody            = array();
            $htmlAndScriptTagsInBody['js']      = $scriptTagsInBody;
            $htmlAndScriptTagsInBody['html']    = $bodyContent->saveHTML();
            return $htmlAndScriptTagsInBody;
        }

        /**
         * @param $bodyContent
         * @return array
         */
        public static function resolveScriptTagsInBody(&$bodyContent)
        {
            $scriptTagNodes = $bodyContent->getElementsByTagName('script');
            $scriptTags     = array();
            foreach ($scriptTagNodes as $scriptTagNode)
            {
                $bodyContent->removeChild($scriptTagNode);
                $scriptTagDetail = array();
                if ($scriptTagNode->hasAttribute('src'))
                {
                    $scriptTagDetail['type']    = 'file';
                    $scriptTagDetail['src']     = $scriptTagNode->getAttribute('src');
                    $scriptTagDetail['body']    = null;
                    $scriptTags[]               = $scriptTagDetail;
                }
                else
                {
                    $scriptTagDetail['type']    = 'codeBlock';
                    $scriptTagDetail['src']     = null;
                    $scriptTagDetail['body']    = $scriptTagNode->nodeValue;
                    $scriptTags[]               = $scriptTagDetail;
                }
            }
            return $scriptTags;
        }

        /**
         * @param $rawXHtml
         * @return string
         */
        public static function resolveAndCombineScripts($rawXHtml)
        {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($rawXHtml);
            $externalScriptFilePath = Yii::getPathOfAlias('application.runtime.uploads') .
                                      DIRECTORY_SEPARATOR . self::EXTERNAL_SCRIPT_FILE_NAME;
            $publishedUrl = Yii::app()->getAssetManager()->getPublishedUrl($externalScriptFilePath);
            if ($publishedUrl === false || file_exists($publishedUrl) === false)
            {
                $publishedUrl = self::publishExternalScripts($dom);
            }
            self::removeScriptTagSrcNodes($dom);
            $head = $dom->getElementsByTagName('head')->item(0);
            $combinedFileElement = $dom->createElement('script');

            $typeAttribute = $dom->createAttribute('type');
            $typeAttribute->value = 'text/javascript';
            $combinedFileElement->appendChild($typeAttribute);

            $srcAttribute = $dom->createAttribute('src');
            $srcAttribute->value = Yii::app()->getRequest()->getHostInfo() . $publishedUrl;
            $combinedFileElement->appendChild($srcAttribute);

            $head->appendChild($combinedFileElement);
            $rawXHtml = $dom->saveHTML();
            return $rawXHtml;
        }

        /**
         * @param $path
         * @return string
         */
        public static function getContentsFromSource($path)
        {
            $scriptFileContents = file_get_contents($path);
            if (strpos($path, 'jquery.min.js') === false && strpos($path, 'jquery.ui.min.js') === false)
            {
                $scriptFileContents = "jQQ.isolate (function(jQuery, $) { " . $scriptFileContents . " });";
            }
            return $scriptFileContents;
        }

        /**
         * @param $dom
         */
        public static function removeScriptTagSrcNodes(&$dom)
        {
            $scriptTags   = $dom->getElementsByTagName('script');
            $tagCount     = 0;
            foreach ($scriptTags as $child)
            {
                if ($child->hasAttribute('src'))
                {
                    $tagCount++;
                }
            }
            while ($tagCount > 0)
            {
                foreach ($scriptTags as $child)
                {
                    if ($child->hasAttribute('src'))
                    {
                        $child->parentNode->removeChild($child);
                        $tagCount--;
                    }
                }
            }
        }

        protected static function publishExternalScripts($dom)
        {
            $scriptTags   = $dom->getElementsByTagName('script');
            $fileContents = '';
            foreach ($scriptTags as $scriptTagNode)
            {
                if ($scriptTagNode->hasAttribute('src'))
                {
                    $scriptSrcPath              = $scriptTagNode->getAttribute('src');
                    $scriptFullPath             = self::getScriptAbsolutePath($scriptSrcPath);
                    $fileContents              .= self::getContentsFromSource($scriptFullPath);
                }
            }
            $scriptFileName = self::EXTERNAL_SCRIPT_FILE_NAME;
            if (!is_dir(Yii::getPathOfAlias('application.runtime.assets')))
            {
                mkdir(Yii::getPathOfAlias('application.runtime.assets'), 0755, true);
            }
            $scriptFilePath = Yii::getPathOfAlias('application.runtime.assets') . DIRECTORY_SEPARATOR . $scriptFileName;
            $fp             = fopen($scriptFilePath, 'w');
            fwrite($fp, $fileContents);
            fclose($fp);
            $publishedUrl   = Yii::app()->getAssetManager()->publish($scriptFilePath);
            return $publishedUrl;
        }

        protected static function getScriptAbsolutePath($scriptSrcPath)
        {
            $assetsBasePath             = Yii::app()->assetManager->basePath;
            $scriptPathRelativeToAssets = substr($scriptSrcPath, strpos($scriptSrcPath, 'assets') + 6);
            $scriptFullPath             = $assetsBasePath . $scriptPathRelativeToAssets;
            return $scriptFullPath;
        }
    }
?>